<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeolocationController extends Controller
{
    /**
     * Get location by IP address using multiple providers for better accuracy
     */
    public function getLocationByIp(Request $request)
    {
        $ip = $request->input('ip', '');

        if (empty($ip)) {
            // Get real IP from headers for proxied requests, otherwise use Laravel's IP
            $ip = $request->header('X-Forwarded-For')
                ?? $request->header('X-Real-IP')
                ?? $request->ip();
        }

        // Check if IP is private/localhost - if so, try to get public IP from external service
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost']) ||
            str_starts_with($ip, '127.') ||
            str_starts_with($ip, '10.') ||
            str_starts_with($ip, '192.168.') ||
            str_starts_with($ip, '172.16.') ||
            str_starts_with($ip, '172.17.') ||
            str_starts_with($ip, '172.18.') ||
            str_starts_with($ip, '172.19.') ||
            str_starts_with($ip, '172.2') ||
            str_starts_with($ip, '172.30.') ||
            str_starts_with($ip, '172.31.')) {

            // Try to get public IP from external service
            try {
                $externalIpResponse = Http::get('https://api.ipify.org?format=json');
                if ($externalIpResponse->successful()) {
                    $ipData = $externalIpResponse->json();
                    $ip = $ipData['ip'] ?? '';
                }
            } catch (\Exception $e) {
                // If ipify fails, try ipinfo.io tokenless endpoint
                try {
                    $ipInfoResponse = Http::get('https://ipinfo.io/json');
                    if ($ipInfoResponse->successful()) {
                        $ipData = $ipInfoResponse->json();
                        $ip = $ipData['ip'] ?? '';
                    }
                } catch (\Exception $e2) {
                    Log::debug('Could not detect public IP: '.$e2->getMessage());
                }
            }

            // If still no valid IP, return error
            if (empty($ip) || in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to detect IP address. Please use a public network or enable location services.',
                ], 404);
            }
        }

        // Try multiple providers for better accuracy
        $providers = [
            'ipapi' => fn () => $this->getIpApiLocation($ip),
            'ip-api' => fn () => $this->getIpApiComLocation($ip),
            'ipinfo' => fn () => $this->getIpInfoLocation($ip),
        ];

        foreach ($providers as $name => $provider) {
            try {
                $result = $provider();
                if ($result && isset($result['latitude']) && $result['latitude'] != 0) {
                    return response()->json([
                        'success' => true,
                        'data' => $result,
                        'provider' => $name,
                    ]);
                }
            } catch (\Exception $e) {
                Log::debug("IP location provider {$name} failed: ".$e->getMessage());

                continue;
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to fetch location for the provided IP',
        ], 404);
    }

    /**
     * Get location using ipinfo.io (better mobile carrier IP mapping)
     */
    private function getIpInfoLocation($ip)
    {
        $response = Http::get("https://ipinfo.io/{$ip}/json");

        if ($response->successful()) {
            $data = $response->json();

            $latitude = 0;
            $longitude = 0;
            if (isset($data['loc'])) {
                $coords = explode(',', $data['loc']);
                if (count($coords) === 2) {
                    $latitude = (float) $coords[0];
                    $longitude = (float) $coords[1];
                }
            }

            return [
                'ip' => $data['ip'] ?? $ip,
                'country' => $data['country'] ?? '',
                'countryCode' => $data['country'] ?? '',
                'region' => $data['region'] ?? '',
                'regionCode' => '',
                'city' => $data['city'] ?? '',
                'zip' => $data['postal'] ?? '',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'timezone' => $data['timezone'] ?? '',
                'isp' => $data['org'] ?? '',
                'org' => $data['org'] ?? '',
                'as' => $data['asn'] ?? '',
            ];
        }

        return null;
    }

    /**
     * Get location using ipapi.co (good alternative provider)
     */
    private function getIpApiLocation($ip)
    {
        $response = Http::get("https://ipapi.co/{$ip}/json/");

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['latitude']) && isset($data['longitude'])) {
                return [
                    'ip' => $data['ip'] ?? $ip,
                    'country' => $data['country_name'] ?? $data['country'] ?? '',
                    'countryCode' => $data['country'] ?? '',
                    'region' => $data['region'] ?? '',
                    'regionCode' => '',
                    'city' => $data['city'] ?? '',
                    'zip' => $data['postal'] ?? '',
                    'latitude' => (float) $data['latitude'],
                    'longitude' => (float) $data['longitude'],
                    'timezone' => $data['timezone'] ?? '',
                    'isp' => $data['org'] ?? '',
                    'org' => $data['org'] ?? '',
                    'as' => $data['asn'] ?? '',
                ];
            }
        }

        return null;
    }

    /**
     * Get location using ip-api.com (original provider, fallback)
     */
    private function getIpApiComLocation($ip)
    {
        $response = Http::get("https://ip-api.com/json/{$ip}");

        if ($response->successful() && $response->json()['status'] === 'success') {
            $data = $response->json();

            return [
                'ip' => $data['query'] ?? $ip,
                'country' => $data['country'] ?? '',
                'countryCode' => $data['countryCode'] ?? '',
                'region' => $data['regionName'] ?? '',
                'regionCode' => $data['region'] ?? '',
                'city' => $data['city'] ?? '',
                'zip' => $data['zip'] ?? '',
                'latitude' => (float) $data['lat'],
                'longitude' => (float) $data['lon'],
                'timezone' => $data['timezone'] ?? '',
                'isp' => $data['isp'] ?? '',
                'org' => $data['org'] ?? '',
                'as' => $data['as'] ?? '',
            ];
        }

        return null;
    }

    /**
     * Detect connection type from client information
     */
    public function detectConnectionType(Request $request)
    {
        $connectionInfo = $request->input('connection');

        $response = [
            'success' => true,
            'data' => [
                'type' => $connectionInfo['effectiveType'] ?? 'unknown',
                'downlink' => $connectionInfo['downlink'] ?? null,
                'rtt' => $connectionInfo['rtt'] ?? null,
                'saveData' => $connectionInfo['saveData'] ?? false,
                'isMobile' => in_array($connectionInfo['effectiveType'] ?? '', ['4g', '3g', '2g', 'slow-2g']),
            ],
        ];

        return response()->json($response);
    }

    /**
     * Get coordinates by address using OpenStreetMap Nominatim (free, no API key)
     */
    public function getCoordinatesByAddress(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:255',
        ]);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'LaravelGeolocationApp/1.0',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $request->input('address'),
                'format' => 'json',
                'limit' => 1,
            ]);

            if ($response->successful() && count($response->json()) > 0) {
                $data = $response->json()[0];

                return response()->json([
                    'success' => true,
                    'data' => [
                        'address' => $data['display_name'] ?? '',
                        'latitude' => $data['lat'] ?? 0,
                        'longitude' => $data['lon'] ?? 0,
                        'type' => $data['type'] ?? '',
                        'importance' => $data['importance'] ?? 0,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching coordinates: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get address by coordinates using OpenStreetMap Nominatim (reverse geocoding)
     */
    public function getAddressByCoordinates(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'TimeMark Location App/1.0 (https://github.com/timemark-app)',
            ])->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $request->input('latitude'),
                'lon' => $request->input('longitude'),
                'format' => 'json',
                'addressdetails' => 1,
                'extratags' => 1,
                'namedetails' => 1,
            ]);

            if ($response->successful() && isset($response->json()['display_name'])) {
                $data = $response->json();
                $address = $data['address'] ?? [];

                $street = $address['road'] ?? '';
                $houseNumber = $address['house_number'] ?? '';
                $neighbourhood = $address['neighbourhood'] ?? $address['suburb'] ?? '';
                $quarter = $address['quarter'] ?? '';

                $streetAddress = trim("{$houseNumber} {$street}");
                if (empty($streetAddress)) {
                    $streetAddress = $neighbourhood ?: $quarter ?: '';
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'address' => $data['display_name'] ?? '',
                        'street' => $street,
                        'house_number' => $houseNumber,
                        'street_address' => $streetAddress,
                        'neighbourhood' => $neighbourhood,
                        'quarter' => $quarter,
                        'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['hamlet'] ?? '',
                        'county' => $address['county'] ?? '',
                        'state' => $address['state'] ?? '',
                        'country' => $address['country'] ?? '',
                        'country_code' => $address['country_code'] ?? '',
                        'postcode' => $address['postcode'] ?? '',
                        'latitude' => $data['lat'] ?? 0,
                        'longitude' => $data['lon'] ?? 0,
                        'place_id' => $data['place_id'] ?? '',
                        'osm_type' => $data['osm_type'] ?? '',
                        'osm_id' => $data['osm_id'] ?? '',
                        'place_rank' => $data['place_rank'] ?? 0,
                        'type' => $data['type'] ?? '',
                        'importance' => $data['importance'] ?? 0,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Location not found for the provided coordinates',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching address: '.$e->getMessage(),
            ], 500);
        }
    }
}
