<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeMark - Location Tracker</title>
    
    <!-- Leaflet CSS for interactive map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f7;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            padding: 30px 0;
        }
        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
        }
        .logo span {
            color: #007aff;
        }
        .current-time {
            font-size: 1.1rem;
            color: #666;
            margin-top: 5px;
        }
        .status-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .status-indicator {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #34c759;
            margin-right: 10px;
            animation: pulse 2s infinite;
        }
        .status-dot.inactive {
            background: #8e8e93;
            animation: none;
        }
        .status-dot.high-accuracy {
            background: #34c759;
            box-shadow: 0 0 0 0 rgba(52, 199, 89, 0.7);
            animation: pulse-accuracy 2s infinite;
        }
        .status-dot.medium-accuracy {
            background: #ffcc00;
        }
        .status-dot.low-accuracy {
            background: #ff3b30;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        @keyframes pulse-accuracy {
            0% { box-shadow: 0 0 0 0 rgba(52, 199, 89, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(52, 199, 89, 0); }
            100% { box-shadow: 0 0 0 0 rgba(52, 199, 89, 0); }
        }
        .status-text {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a1a1a;
        }
        .accuracy-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 10px;
        }
        .accuracy-badge.high {
            background: #d4edda;
            color: #155724;
        }
        .accuracy-badge.medium {
            background: #fff3cd;
            color: #856404;
        }
        .accuracy-badge.low {
            background: #f8d7da;
            color: #721c24;
        }
        .location-info {
            display: none;
        }
        .location-info.show {
            display: block;
        }
        .info-item {
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            font-size: 0.85rem;
            color: #8e8e93;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 1rem;
            color: #1a1a1a;
            font-weight: 500;
        }
        .info-value.street {
            font-size: 1.1rem;
            color: #007aff;
        }
        .map-container {
            margin: 20px 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        #map {
            height: 300px;
            width: 100%;
        }
        .map-links {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .map-link-btn {
            flex: 1;
            min-width: 120px;
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .map-link-btn.google {
            background: #4285f4;
            color: white;
        }
        .map-link-btn.osm {
            background: #7ebc6f;
            color: white;
        }
        .map-link-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        .btn-primary {
            background: #007aff;
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-primary:active {
            transform: scale(0.98);
        }
        .btn-primary:disabled {
            background: #8e8e93;
            cursor: not-allowed;
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #1a1a1a;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: all 0.2s;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .loading {
            text-align: center;
            padding: 40px;
            display: none;
        }
        .loading.show {
            display: block;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f0f0f0;
            border-top-color: #007aff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .error {
            background: #fff2f2;
            color: #ff3b30;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            display: none;
        }
        .error.show {
            display: block;
        }
        .history-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .history-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 15px;
        }
        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .history-item:last-child {
            border-bottom: none;
        }
        .history-time {
            font-size: 0.85rem;
            color: #8e8e93;
        }
        .history-location {
            font-size: 0.9rem;
            color: #1a1a1a;
            font-weight: 500;
        }
        .no-history {
            text-align: center;
            color: #8e8e93;
            padding: 20px;
            font-size: 0.9rem;
        }
        .source-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 8px;
        }
        .source-badge.gps {
            background: #d1ecf1;
            color: #0c5460;
        }
        .source-badge.ip {
            background: #f8d7da;
            color: #721c24;
        }
        @media (max-width: 600px) {
            .map-links {
                flex-direction: column;
            }
            .map-link-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Time<span>Mark</span></div>
            <div class="current-time" id="currentTime"></div>
        </div>

        <div class="status-card">
            <div class="status-indicator">
                <div class="status-dot inactive" id="statusDot"></div>
                <div class="status-text" id="statusText">Location Not Set</div>
                <span id="accuracyBadge" class="accuracy-badge" style="display: none;"></span>
            </div>

            <button class="btn-primary" id="getLocationBtn" onclick="getLocation()">
                📍 Get My Location
            </button>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <div id="loadingText">Getting your location...</div>
            </div>

            <div class="error" id="error"></div>

            <div class="location-info" id="locationInfo">
                <div class="info-item">
                    <div class="info-label">Street Address</div>
                    <div class="info-value street" id="streetAddress">-</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Neighborhood</div>
                    <div class="info-value" id="neighbourhood">-</div>
                </div>
                <div class="info-item">
                    <div class="info-label">City</div>
                    <div class="info-value" id="city">-</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Region / State</div>
                    <div class="info-value" id="region">-</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Country</div>
                    <div class="info-value" id="country">-</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Postal Code</div>
                    <div class="info-value" id="postcode">-</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Coordinates <span id="sourceBadge" class="source-badge"></span></div>
                    <div class="info-value" id="coordinates">-</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Accuracy</div>
                    <div class="info-value" id="accuracy">-</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Marked At</div>
                    <div class="info-value" id="markedAt">-</div>
                </div>

                <!-- Interactive Map -->
                <div class="map-container">
                    <div id="map"></div>
                </div>

                <!-- Map Links -->
                <div class="map-links">
                    <a id="googleMapsLink" href="#" target="_blank" class="map-link-btn google">
                        🗺️ Google Maps
                    </a>
                    <a id="osmLink" href="#" target="_blank" class="map-link-btn osm">
                        🌍 OpenStreetMap
                    </a>
                </div>

                <button class="btn-secondary" onclick="getLocation()">
                    🔄 Update Location
                </button>
            </div>
        </div>

        <div class="history-card">
            <div class="history-title">Location History</div>
            <div id="historyList">
                <div class="no-history">No location history yet</div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        let locationHistory = [];
        let map = null;
        let marker = null;
        let accuracyCircle = null;
        let currentLat = null;
        let currentLon = null;

        function updateCurrentTime() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleString();
        }
        setInterval(updateCurrentTime, 1000);
        updateCurrentTime();

        function initMap(lat, lon, accuracy) {
            currentLat = lat;
            currentLon = lon;

            // Initialize map if not exists
            if (!map) {
                map = L.map('map').setView([lat, lon], 17);
                
                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(map);
            } else {
                map.setView([lat, lon], 17);
            }

            // Remove existing marker and accuracy circle
            if (marker) {
                map.removeLayer(marker);
            }
            if (accuracyCircle) {
                map.removeLayer(accuracyCircle);
            }

            // Add custom marker with popup
            marker = L.marker([lat, lon]).addTo(map);
            marker.bindPopup(`
                <b>Your Location</b><br>
                Lat: ${lat.toFixed(6)}<br>
                Lon: ${lon.toFixed(6)}<br>
                Accuracy: ±${Math.round(accuracy)}m
            `).openPopup();

            // Add accuracy circle
            accuracyCircle = L.circle([lat, lon], {
                radius: accuracy,
                color: '#007aff',
                fillColor: '#007aff',
                fillOpacity: 0.1,
                weight: 2
            }).addTo(map);

            // Update map links
            updateMapLinks(lat, lon);
        }

        function updateMapLinks(lat, lon) {
            // Google Maps link
            document.getElementById('googleMapsLink').href = 
                `https://www.google.com/maps?q=${lat},${lon}&z=17`;
            
            // OpenStreetMap link
            document.getElementById('osmLink').href = 
                `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lon}#map=17/${lat}/${lon}`;
        }

        function getAccuracyLevel(accuracy) {
            if (accuracy <= 20) return { level: 'high', label: 'High Accuracy', class: 'high' };
            if (accuracy <= 100) return { level: 'medium', label: 'Medium Accuracy', class: 'medium' };
            return { level: 'low', label: 'Low Accuracy', class: 'low' };
        }

        function updateAccuracyDisplay(accuracy) {
            const accuracyInfo = document.getElementById('accuracy');
            const accuracyBadge = document.getElementById('accuracyBadge');
            const statusDot = document.getElementById('statusDot');
            
            accuracyInfo.textContent = `±${Math.round(accuracy)} meters`;
            
            const accLevel = getAccuracyLevel(accuracy);
            accuracyBadge.textContent = accLevel.label;
            accuracyBadge.className = `accuracy-badge ${accLevel.class}`;
            accuracyBadge.style.display = 'inline-block';
            
            // Update status dot color based on accuracy
            statusDot.classList.remove('high-accuracy', 'medium-accuracy', 'low-accuracy');
            statusDot.classList.add(`${accLevel.level}-accuracy`);
        }

        function getLocation() {
            const btn = document.getElementById('getLocationBtn');
            const loading = document.getElementById('loading');
            const loadingText = document.getElementById('loadingText');
            const error = document.getElementById('error');
            const locationInfo = document.getElementById('locationInfo');

            error.classList.remove('show');
            locationInfo.classList.remove('show');

            // Try browser geolocation first with maximum accuracy settings
            if (navigator.geolocation) {
                btn.style.display = 'none';
                loading.classList.add('show');
                loadingText.textContent = 'Getting precise location...';

                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        const accuracy = position.coords.accuracy;

                        loadingText.textContent = 'Fetching street address...';

                        try {
                            const response = await fetch(
                                `/api/location/by-coordinates?latitude=${lat}&longitude=${lon}`
                            );
                            const data = await response.json();

                            loading.classList.remove('show');
                            btn.style.display = 'block';

                            if (data.success) {
                                displayLocation(data.data, lat, lon, accuracy, 'GPS');
                                addToHistory(data.data.street_address || data.data.city || 'Unknown', lat, lon, 'GPS');
                            } else {
                                getIpLocation();
                            }
                        } catch (err) {
                            console.error('Reverse geocoding failed:', err);
                            loading.classList.remove('show');
                            btn.style.display = 'block';
                            getIpLocation();
                        }
                    },
                    (err) => {
                        console.log('Geolocation failed, using IP fallback:', err.message);
                        loading.classList.remove('show');
                        loadingText.textContent = 'GPS unavailable, using IP location...';
                        getIpLocation();
                    },
                    {
                        enableHighAccuracy: true,  // Use GPS for maximum accuracy
                        timeout: 15000,            // Wait up to 15 seconds
                        maximumAge: 0              // Don't use cached position
                    }
                );
            } else {
                // No geolocation support, use IP directly
                loadingText.textContent = 'GPS not supported, using IP location...';
                getIpLocation();
            }
        }

        async function getIpLocation() {
            const loading = document.getElementById('loading');
            const loadingText = document.getElementById('loadingText');
            const error = document.getElementById('error');
            const btn = document.getElementById('getLocationBtn');
            const locationInfo = document.getElementById('locationInfo');

            loading.classList.add('show');
            loadingText.textContent = 'Getting location from IP...';

            try {
                // Use Laravel backend route instead of direct external call (fixes HTTPS issues)
                const response = await fetch('/api/location/by-ip');
                const data = await response.json();

                console.log('IP API Response:', data);

                loading.classList.remove('show');

                if (data.success) {
                    const ipData = data.data;
                    const locationData = {
                        address: `${ipData.city}, ${ipData.region}, ${ipData.country}`,
                        street_address: '',
                        street: '',
                        house_number: '',
                        neighbourhood: '',
                        city: ipData.city,
                        state: ipData.region,
                        country: ipData.country,
                        country_code: ipData.countryCode,
                        postcode: ipData.zip
                    };

                    // IP-based location has lower accuracy (typically 1-50 km)
                    const ipAccuracy = 5000; // 5km radius

                    displayLocation(locationData, ipData.latitude, ipData.longitude, ipAccuracy, 'IP');
                    addToHistory(ipData.city || 'Unknown', ipData.latitude, ipData.longitude, 'IP');

                    btn.style.display = 'block';
                    btn.textContent = '🔄 Update Location';
                } else {
                    error.textContent = '❌ Unable to get location from IP';
                    error.classList.add('show');
                    btn.style.display = 'block';
                }
            } catch (err) {
                console.error('Error:', err);
                loading.classList.remove('show');
                error.textContent = '❌ Error: ' + err.message;
                error.classList.add('show');
                btn.style.display = 'block';
            }
        }

        function displayLocation(data, lat, lon, accuracy, source) {
            document.getElementById('statusDot').classList.remove('inactive');
            document.getElementById('statusText').textContent = 'Location Active';
            document.getElementById('locationInfo').classList.add('show');

            // Street address
            const streetAddress = data.street_address || data.street || 'Not available';
            document.getElementById('streetAddress').textContent = streetAddress;
            
            // House number display
            if (data.house_number && data.street) {
                document.getElementById('streetAddress').textContent = 
                    `${data.house_number} ${data.street}`;
            }

            document.getElementById('neighbourhood').textContent = 
                data.neighbourhood || data.quarter || '-';
            document.getElementById('city').textContent = data.city || '-';
            document.getElementById('region').textContent = data.state || data.region || '-';
            document.getElementById('country').textContent = data.country || '-';
            document.getElementById('postcode').textContent = data.postcode || '-';
            document.getElementById('coordinates').textContent = 
                `${lat.toFixed(6)}, ${lon.toFixed(6)}`;
            document.getElementById('markedAt').textContent = new Date().toLocaleString();

            // Source badge
            const sourceBadge = document.getElementById('sourceBadge');
            sourceBadge.textContent = source;
            sourceBadge.className = `source-badge ${source.toLowerCase()}`;

            // Update accuracy display
            updateAccuracyDisplay(accuracy);

            // Initialize map
            setTimeout(() => {
                initMap(lat, lon, accuracy);
            }, 100);
        }

        function addToHistory(location, lat, lon, source) {
            const time = new Date().toLocaleTimeString();
            locationHistory.unshift({ location, lat, lon, time, source });
            if (locationHistory.length > 5) locationHistory.pop();
            renderHistory();
        }

        function renderHistory() {
            const list = document.getElementById('historyList');
            if (locationHistory.length === 0) {
                list.innerHTML = '<div class="no-history">No location history yet</div>';
                return;
            }

            list.innerHTML = locationHistory.map(item => `
                <div class="history-item">
                    <div>
                        <div class="history-location">
                            ${item.location || 'Unknown'}
                            <span class="source-badge ${item.source?.toLowerCase() || 'ip'}">${item.source || 'IP'}</span>
                        </div>
                        <div class="history-time">${item.lat.toFixed(4)}, ${item.lon.toFixed(4)}</div>
                    </div>
                    <div class="history-time">${item.time}</div>
                </div>
            `).join('');
        }

        window.addEventListener('load', function() {
            setTimeout(getLocation, 500);
        });
    </script>
</body>
</html>
