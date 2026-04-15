<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeolocationController extends Controller
{
    /**
     * Get location by IP address (free API)
     */
    public function getLocationByIp(Request $request)
    {
        $ip = $request->input('ip', '');
        
        // If no IP provided, use current IP
        if (empty($ip)) {
            $ip = $request->ip();
        }

        try {
            // Free IP geolocation API (no API key required for basic usage)
            $response = Http::get("http://ip-api.com/json/{$ip}");
            
            if ($response->successful() && $response->json()['status'] === 'success') {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'ip' => $data['query'] ?? $ip,
                        'country' => $data['country'] ?? '',
                        'countryCode' => $data['countryCode'] ?? '',
                        'region' => $data['regionName'] ?? '',
                        'regionCode' => $data['region'] ?? '',
                        'city' => $data['city'] ?? '',
                        'zip' => $data['zip'] ?? '',
                        'latitude' => $data['lat'] ?? 0,
                        'longitude' => $data['lon'] ?? 0,
                        'timezone' => $data['timezone'] ?? '',
                        'isp' => $data['isp'] ?? '',
                        'org' => $data['org'] ?? '',
                        'as' => $data['as'] ?? '',
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch location for the provided IP'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching location: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get coordinates by address using OpenStreetMap Nominatim (free, no API key)
     */
    public function getCoordinatesByAddress(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:255'
        ]);

        try {
            // OpenStreetMap Nominatim API (free, no API key required)
            $response = Http::withHeaders([
                'User-Agent' => 'LaravelGeolocationApp/1.0'
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $request->input('address'),
                'format' => 'json',
                'limit' => 1
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
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching coordinates: ' . $e->getMessage()
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
            'longitude' => 'required|numeric|between:-180,180'
        ]);

        try {
            // OpenStreetMap Nominatim Reverse Geocoding (free, no API key required)
            $response = Http::withHeaders([
                'User-Agent' => 'LaravelGeolocationApp/1.0'
            ])->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $request->input('latitude'),
                'lon' => $request->input('longitude'),
                'format' => 'json'
            ]);

            if ($response->successful() && isset($response->json()['display_name'])) {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'address' => $data['display_name'] ?? '',
                        'latitude' => $data['lat'] ?? 0,
                        'longitude' => $data['lon'] ?? 0,
                        'city' => $data['address']['city'] ?? $data['address']['town'] ?? $data['address']['village'] ?? '',
                        'state' => $data['address']['state'] ?? '',
                        'country' => $data['address']['country'] ?? '',
                        'postcode' => $data['address']['postcode'] ?? '',
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Location not found for the provided coordinates'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching address: ' . $e->getMessage()
            ], 500);
        }
    }
}
