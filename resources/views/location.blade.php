<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeMark - Location Tracker</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }
        .app-container { max-width: 100%; margin: 0 auto; }
        
        .map-section {
            position: relative;
            height: 40vh;
            min-height: 300px;
            background: #e5e5e5;
        }
        #map { height: 100%; width: 100%; }
        
        .map-header {
            position: absolute;
            top: 16px;
            left: 16px;
            right: 16px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 16px 20px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .logo { font-size: 1.4rem; font-weight: 700; color: #1a1a1a; }
        .logo span { color: #667eea; }
        .current-time { font-size: 0.8rem; color: #666; }
        
        .status-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            background: #f0f2f5;
            border-radius: 20px;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #8e8e93;
            flex-shrink: 0;
        }
        .status-dot.active { background: #34c759; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .status-text { font-size: 0.85rem; font-weight: 600; color: #1a1a1a; }
        
        .main-content { padding: 24px 16px; display: flex; flex-direction: column; gap: 20px; }
        
        .info-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .card-title { font-size: 1rem; font-weight: 700; color: #1a1a1a; }
        
        .source-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .source-badge.gps { background: #d4edda; color: #155724; }
        .source-badge.cell { background: #cce5ff; color: #004085; }
        .source-badge.ip { background: #fff3cd; color: #856404; }
        
        .address-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 16px;
        }
        .address-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.85;
            margin-bottom: 8px;
        }
        .address-value { font-size: 1.1rem; font-weight: 600; line-height: 1.5; }
        
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .info-item { padding: 14px; background: #f8f9fa; border-radius: 12px; }
        .info-label { font-size: 0.7rem; color: #8e8e93; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .info-value { font-size: 0.9rem; color: #1a1a1a; font-weight: 600; }
        
        .accuracy-display {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 12px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        .accuracy-bar { flex: 1; height: 6px; background: #e5e5e5; border-radius: 3px; overflow: hidden; }
        .accuracy-fill { height: 100%; border-radius: 3px; transition: width 0.3s; }
        .accuracy-fill.high { background: #34c759; }
        .accuracy-fill.medium { background: #ffcc00; }
        .accuracy-fill.low { background: #ff3b30; }
        .accuracy-text { font-size: 0.8rem; color: #666; font-weight: 500; min-width: 60px; }
        
        .accuracy-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 1px solid #ffc107;
            border-radius: 12px;
            padding: 14px;
            margin-top: 12px;
            font-size: 0.8rem;
            color: #856404;
            display: none;
        }
        .accuracy-warning.show { display: block; }
        
        .map-links { display: flex; gap: 10px; margin-top: 16px; }
        .map-link-btn {
            flex: 1;
            padding: 12px 16px;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
        }
        .map-link-btn.google { background: #4285f4; color: white; }
        .map-link-btn.osm { background: #7ebc6f; color: white; }
        .map-link-btn:hover { transform: translateY(-2px); }
        
        .action-section { padding: 0 16px; }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 16px 24px;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4); }
        .btn-primary:disabled { background: #8e8e93; cursor: not-allowed; }
        
        .loading { text-align: center; padding: 30px; display: none; }
        .loading.show { display: block; }
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e5e5e5;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 12px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { font-size: 0.9rem; color: #666; font-weight: 500; }
        
        .error { background: #fff2f2; color: #ff3b30; padding: 14px; border-radius: 12px; margin-top: 12px; font-size: 0.85rem; display: none; }
        .error.show { display: block; }
        
        .history-list { display: flex; flex-direction: column; gap: 10px; }
        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        .history-location { font-size: 0.9rem; color: #1a1a1a; font-weight: 600; }
        .history-coords { font-size: 0.75rem; color: #8e8e93; }
        .history-time { font-size: 0.75rem; color: #8e8e93; }
        .no-history { text-align: center; color: #8e8e93; padding: 20px; font-size: 0.85rem; }
        
        .location-info { display: none; }
        .location-info.show { display: block; }
        
        @media (max-width: 600px) {
            .map-section { height: 35vh; min-height: 250px; }
            .map-header { flex-wrap: wrap; }
            .info-grid { grid-template-columns: 1fr; }
            .map-links { flex-direction: column; }
            .main-content { padding: 16px; gap: 16px; }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="map-section">
            <div id="map"></div>
            <div class="map-header">
                <div class="logo">Time<span>Mark</span></div>
                <div class="status-pill">
                    <div class="status-dot" id="statusDot"></div>
                    <span class="status-text" id="statusText">Not Set</span>
                </div>
                <div class="current-time" id="currentTime"></div>
            </div>
        </div>

        <div class="main-content">
            <div class="action-section">
                <button class="btn-primary" id="getLocationBtn" onclick="getLocation()">
                    📍 Get My Location
                </button>
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <div class="loading-text" id="loadingText">Getting your location...</div>
                </div>
                <div class="error" id="error"></div>
            </div>

            <div class="location-info" id="locationInfo">
                <div class="info-card">
                    <div class="address-display">
                        <div class="address-label">Current Location</div>
                        <div class="address-value" id="fullAddress">-</div>
                    </div>
                    <div class="map-links">
                        <a id="googleMapsLink" href="#" target="_blank" class="map-link-btn google">Google Maps</a>
                        <a id="osmLink" href="#" target="_blank" class="map-link-btn osm">OpenStreetMap</a>
                    </div>
                </div>

                <div class="info-card">
                    <div class="card-header">
                        <div class="card-title">Location Details</div>
                        <span class="source-badge" id="sourceBadge">-</span>
                    </div>
                    <div class="info-grid">
                        <div class="info-item" style="grid-column: span 2;">
                            <div class="info-label">Street Address</div>
                            <div class="info-value" id="streetAddress" style="font-size: 0.85rem;">-</div>
                        </div>
                        <div class="info-item" style="grid-column: span 2;">
                            <div class="info-label">Neighborhood / Area</div>
                            <div class="info-value" id="neighbourhood" style="font-size: 0.85rem;">-</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">City</div>
                            <div class="info-value" id="city">-</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Region</div>
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
                            <div class="info-label">Coordinates</div>
                            <div class="info-value" id="coordinates">-</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Marked At</div>
                            <div class="info-value" id="markedAt">-</div>
                        </div>
                    </div>
                    <div class="accuracy-display">
                        <span style="font-size: 0.8rem; color: #666;">Accuracy:</span>
                        <div class="accuracy-bar">
                            <div class="accuracy-fill" id="accuracyFill" style="width: 0%"></div>
                        </div>
                        <span class="accuracy-text" id="accuracyText">-</span>
                    </div>
                    <div class="accuracy-warning" id="accuracyWarning">
                        ⚠️ <strong>Low Accuracy:</strong> IP-based location may be inaccurate (5-50km). Enable location services for better results.
                    </div>
                </div>
            </div>

            <div class="info-card">
                <div class="card-title" style="margin-bottom: 16px;">Location History</div>
                <div class="history-list" id="historyList">
                    <div class="no-history">No location history yet</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let locationHistory = [];
        let map = null;
        let marker = null;
        let accuracyCircle = null;
        let currentConnectionType = 'unknown';

        function updateCurrentTime() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleString();
        }
        setInterval(updateCurrentTime, 1000);
        updateCurrentTime();

        function detectConnectionType() {
            if (navigator.connection) {
                const conn = navigator.connection;
                const effectiveType = conn.effectiveType || 'unknown';
                const isMobile = ['4g', '3g', '2g', 'slow-2g'].includes(effectiveType);
                currentConnectionType = isMobile ? 'mobile' : 'wifi';
                return { effectiveType, isMobile };
            }
            const isMobile = /Mobile|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
            currentConnectionType = isMobile ? 'mobile' : 'unknown';
            return { effectiveType: 'unknown', isMobile };
        }

function getLocation() {
            var btn = document.getElementById('getLocationBtn');
            var loading = document.getElementById('loading');
            var loadingText = document.getElementById('loadingText');
            var error = document.getElementById('error');
            var locationInfo = document.getElementById('locationInfo');
            var accuracyWarning = document.getElementById('accuracyWarning');

            error.classList.remove('show');
            locationInfo.classList.remove('show');
            accuracyWarning.classList.remove('show');

            var connection = detectConnectionType();
            var isMobileData = connection.isMobile;

            if (navigator.geolocation) {
                btn.style.display = 'none';
                loading.classList.add('show');
                loadingText.textContent = isMobileData ? 'Getting GPS location...' : 'Getting precise location...';

                var timeout = isMobileData ? 25000 : 15000;

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        var lat = position.coords.latitude;
                        var lon = position.coords.longitude;
                        var accuracy = position.coords.accuracy;

                        loadingText.textContent = 'Fetching address...';

                        fetch('/api/location/by-coordinates?latitude=' + lat + '&longitude=' + lon)
                            .then(function(response) { return response.json(); })
                            .then(function(data) {
                                loading.classList.remove('show');
                                btn.style.display = 'block';

                                if (data.success) {
                                    displayLocation(data.data, lat, lon, accuracy, 'GPS');
                                    addToHistory(data.data.street_address || data.data.city || 'Unknown', lat, lon, 'GPS');
                                } else {
                                    displayLocation({
                                        street: '', city: 'Unknown', state: '', country: '', postcode: ''
                                    }, lat, lon, accuracy, 'GPS');
                                    addToHistory('Coordinates Only', lat, lon, 'GPS');
                                }
                            })
                            .catch(function(err) {
                                loading.classList.remove('show');
                                btn.style.display = 'block';
                                displayLocation({
                                    street: '', city: 'Unknown', state: '', country: '', postcode: ''
                                }, lat, lon, accuracy, 'GPS');
                                addToHistory('Coordinates Only', lat, lon, 'GPS');
                            });
                    },
                    function(err) {
                        console.log('Geolocation error:', err.message);
                        loading.classList.remove('show');
                        
                        if (err.code === err.PERMISSION_DENIED) {
                            error.innerHTML = '❌ Location permission denied. Please enable location access in your browser settings.';
                            error.classList.add('show');
                            btn.style.display = 'block';
                        } else if (err.code === err.POSITION_UNAVAILABLE) {
                            loadingText.textContent = 'Location unavailable, trying IP...';
                            setTimeout(function() { getIpLocation(); }, 1000);
                        } else if (err.code === err.TIMEOUT) {
                            loadingText.textContent = 'GPS timeout, retrying...';
                            setTimeout(function() { retryGeolocation(); }, 1500);
                        } else if (err.message && err.message.indexOf('secure') !== -1) {
                            loadingText.textContent = 'Secure connection required, using IP location...';
                            setTimeout(function() { getIpLocation(); }, 1000);
                        } else {
                            getIpLocation();
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: timeout,
                        maximumAge: 60000
                    }
                );
            } else {
                getIpLocation();
            }
        }

        function retryGeolocation() {
            var loading = document.getElementById('loading');
            var loadingText = document.getElementById('loadingText');
            var btn = document.getElementById('getLocationBtn');

            loading.classList.add('show');
            loadingText.textContent = 'Retrying GPS...';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var lat = position.coords.latitude;
                    var lon = position.coords.longitude;
                    var accuracy = position.coords.accuracy;

                    loading.classList.remove('show');
                    btn.style.display = 'block';

                    fetch('/api/location/by-coordinates?latitude=' + lat + '&longitude=' + lon)
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.success) {
                                displayLocation(data.data, lat, lon, accuracy, 'GPS');
                                addToHistory(data.data.street_address || data.data.city || 'Unknown', lat, lon, 'GPS');
                            } else {
                                displayLocation({ street: '', city: 'Unknown', state: '', country: '', postcode: '' }, lat, lon, accuracy, 'GPS');
                                addToHistory('Coordinates Only', lat, lon, 'GPS');
                            }
                        })
                        .catch(function() {
                            displayLocation({ street: '', city: 'Unknown', state: '', country: '', postcode: '' }, lat, lon, accuracy, 'GPS');
                            addToHistory('Coordinates Only', lat, lon, 'GPS');
                        });
                },
                function() {
                    loading.classList.remove('show');
                    getIpLocation();
                },
                { enableHighAccuracy: false, timeout: 15000, maximumAge: 300000 }
            );
        }

        async function getIpLocation() {
            var loading = document.getElementById('loading');
            var loadingText = document.getElementById('loadingText');
            var error = document.getElementById('error');
            var btn = document.getElementById('getLocationBtn');
            var accuracyWarning = document.getElementById('accuracyWarning');

            loading.classList.add('show');
            loadingText.textContent = 'Getting location from IP...';

            try {
                var response = await fetch('/api/location/by-ip');
                var data = await response.json();

                loading.classList.remove('show');

                if (data.success) {
                    var ipData = data.data;
                    var locationData = {
                        street: '',
                        city: ipData.city,
                        state: ipData.region,
                        country: ipData.country,
                        postcode: ipData.zip
                    };

                    var isMobile = currentConnectionType === 'mobile';
                    var ipAccuracy = isMobile ? 3000 : 1500;

                    if (isMobile) {
                        accuracyWarning.classList.add('show');
                    }

                    displayLocation(locationData, ipData.latitude, ipData.longitude, ipAccuracy, 'IP');
                    addToHistory(ipData.city || 'Unknown', ipData.latitude, ipData.longitude, 'IP');

                    btn.style.display = 'block';
                    btn.textContent = '🔄 Update Location';
                } else {
                    error.textContent = '❌ ' + (data.message || 'Unable to get location. Please enable location services for better accuracy.');
                    error.classList.add('show');
                    btn.style.display = 'block';
                }
            } catch (err) {
                loading.classList.remove('show');
                error.textContent = '❌ Error: ' + err.message;
                error.classList.add('show');
                btn.style.display = 'block';
            }
        }

        function displayLocation(data, lat, lon, accuracy, source) {
            document.getElementById('statusDot').classList.add('active');
            document.getElementById('statusText').textContent = 'Location Active';
            document.getElementById('locationInfo').classList.add('show');

            // Build full address with street
            var addressParts = [];
            if (data.street_address) addressParts.push(data.street_address);
            else if (data.street) addressParts.push(data.street);
            if (data.neighbourhood) addressParts.push(data.neighbourhood);
            if (data.city) addressParts.push(data.city);
            if (data.region) addressParts.push(data.region);
            if (data.country) addressParts.push(data.country);
            
            var fullAddress = addressParts.join(', ');
            document.getElementById('fullAddress').textContent = fullAddress || '-';

            // Street address field
            var streetAddr = data.street_address || data.street || data.house_number || '-';
            document.getElementById('streetAddress').textContent = streetAddr;
            
            // Neighbourhood
            var neighbourhood = data.neighbourhood || data.quarter || data.suburb || '-';
            document.getElementById('neighbourhood').textContent = neighbourhood;

            // Show individual details
            document.getElementById('city').textContent = data.city || '-';
            document.getElementById('region').textContent = data.state || data.region || '-';
            document.getElementById('country').textContent = data.country || '-';
            document.getElementById('postcode').textContent = data.postcode || '-';
            document.getElementById('coordinates').textContent = lat.toFixed(6) + ', ' + lon.toFixed(6);
            document.getElementById('markedAt').textContent = new Date().toLocaleString();

            document.getElementById('sourceBadge').textContent = source;
            document.getElementById('sourceBadge').className = 'source-badge ' + source.toLowerCase();

            updateAccuracyDisplay(accuracy);

            document.getElementById('googleMapsLink').href = 'https://www.google.com/maps?q=' + lat + ',' + lon;
            document.getElementById('osmLink').href = 'https://www.openstreetmap.org/?mlat=' + lat + '&mlon=' + lon + '#map=15/' + lat + '/' + lon;

            setTimeout(function() { initMap(lat, lon, accuracy); }, 100);
        }

        function updateAccuracyDisplay(accuracy) {
            var fill = document.getElementById('accuracyFill');
            var text = document.getElementById('accuracyText');
            var warning = document.getElementById('accuracyWarning');
            var percent = 100;
            var level = 'High';
            var color = '#34c759';

            if (accuracy <= 100) {
                percent = 100;
                level = 'High (' + Math.round(accuracy) + 'm)';
                color = '#34c759';
            } else if (accuracy <= 1000) {
                percent = 60;
                level = 'Medium (' + Math.round(accuracy) + 'm)';
                color = '#ffcc00';
            } else {
                percent = 30;
                level = 'Low (' + Math.round(accuracy) + 'm)';
                color = '#ff3b30';
            }

            fill.className = 'accuracy-fill ' + (percent === 100 ? 'high' : percent === 60 ? 'medium' : 'low');
            fill.style.width = percent + '%';
            fill.style.background = color;
            text.textContent = level;

            if (accuracy > 1000) {
                warning.classList.add('show');
            } else {
                warning.classList.remove('show');
            }
        }

        function initMap(lat, lon, accuracy) {
            if (map) {
                map.remove();
            }
            
            var zoom = accuracy <= 100 ? 16 : accuracy <= 1000 ? 14 : 11;
            map = L.map('map').setView([lat, lon], zoom);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);
            
            marker = L.marker([lat, lon]).addTo(map);
            
            if (accuracy > 0 && accuracy < 5000) {
                var color = accuracy <= 100 ? '#34c759' : accuracy <= 1000 ? '#ffcc00' : '#ff3b30';
                accuracyCircle = L.circle([lat, lon], {
                    color: color,
                    fillColor: color,
                    fillOpacity: 0.2,
                    radius: accuracy
                }).addTo(map);
            }
        }

        function addToHistory(location, lat, lon, source) {
            var time = new Date().toLocaleTimeString();
            locationHistory.unshift({ location: location, lat: lat, lon: lon, time: time, source: source });
            if (locationHistory.length > 5) locationHistory.pop();
            renderHistory();
        }

        function renderHistory() {
            var list = document.getElementById('historyList');
            if (locationHistory.length === 0) {
                list.innerHTML = '<div class="no-history">No location history yet</div>';
                return;
            }

            var html = '';
            for (var i = 0; i < locationHistory.length; i++) {
                var item = locationHistory[i];
                html += '<div class="history-item"><div><div class="history-location">' + (item.location || 'Unknown') + '</div><div class="history-coords">' + item.lat.toFixed(4) + ', ' + item.lon.toFixed(4) + '</div></div><div class="history-time">' + item.time + '</div></div>';
            }
            list.innerHTML = html;
        }

        window.addEventListener('load', function() {
            setTimeout(getLocation, 500);
        });
    </script>
</body>
</html>