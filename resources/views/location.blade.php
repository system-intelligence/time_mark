<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeMark - Location Tracker</title>
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
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .status-text {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a1a1a;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Time<span> Mark</span></div>
            <div class="current-time" id="currentTime"></div>
        </div>

        <div class="status-card">
            <div class="status-indicator">
                <div class="status-dot inactive" id="statusDot"></div>
                <div class="status-text" id="statusText">Location Not Set</div>
            </div>

            <button class="btn-primary" id="getLocationBtn" onclick="getLocation()">
                Get My Location
            </button>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <div>Getting your location...</div>
            </div>

            <div class="error" id="error"></div>

            <div class="location-info" id="locationInfo">
                <div class="info-item">
                    <div class="info-label">Address</div>
                    <div class="info-value" id="address">-</div>
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
                    <div class="info-label">Coordinates</div>
                    <div class="info-value" id="coordinates">-</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Marked At</div>
                    <div class="info-value" id="markedAt">-</div>
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

    <script>
        let locationHistory = [];

        function updateCurrentTime() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleString();
        }
        setInterval(updateCurrentTime, 1000);
        updateCurrentTime();

        function getLocation() {
            const btn = document.getElementById('getLocationBtn');
            const loading = document.getElementById('loading');
            const error = document.getElementById('error');
            const locationInfo = document.getElementById('locationInfo');

            error.classList.remove('show');
            locationInfo.classList.remove('show');
            
            // Try browser geolocation first
            if (navigator.geolocation) {
                btn.style.display = 'none';
                loading.classList.add('show');

                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;

                        try {
                            const response = await fetch(
                                `/api/location/by-coordinates?latitude=${lat}&longitude=${lon}`
                            );
                            const data = await response.json();

                            loading.classList.remove('show');
                            btn.style.display = 'block';

                            if (data.success) {
                                displayLocation(data.data, lat, lon);
                                addToHistory(data.data.city || data.data.address, lat, lon);
                            } else {
                                getIpLocation();
                            }
                        } catch (err) {
                            loading.classList.remove('show');
                            btn.style.display = 'block';
                            getIpLocation();
                        }
                    },
                    (err) => {
                        console.log('Geolocation failed, using IP fallback');
                        loading.classList.remove('show');
                        getIpLocation();
                    },
                    { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                );
            } else {
                // No geolocation support, use IP directly
                getIpLocation();
            }
        }

        async function getIpLocation() {
            const loading = document.getElementById('loading');
            const error = document.getElementById('error');
            const btn = document.getElementById('getLocationBtn');
            const locationInfo = document.getElementById('locationInfo');

            btn.style.display = 'none';
            loading.classList.add('show');

            try {
                // Direct external API call instead of Laravel route
                const response = await fetch('http://ip-api.com/json/?fields=status,message,country,countryCode,regionName,city,zip,lat,lon,timezone,isp');
                const data = await response.json();

                console.log('IP API Response:', data);

                loading.classList.remove('show');

                if (data.status === 'success') {
                    const locationData = {
                        address: `${data.city}, ${data.regionName}, ${data.country}`,
                        city: data.city,
                        state: data.regionName,
                        country: data.country,
                        postcode: data.zip
                    };

                    displayLocation(locationData, data.lat, data.lon);
                    addToHistory(data.city, data.lat, data.lon);
                    
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

        function displayLocation(data, lat, lon) {
            document.getElementById('statusDot').classList.remove('inactive');
            document.getElementById('statusText').textContent = 'Location Active';
            document.getElementById('locationInfo').classList.add('show');

            if (data.address) document.getElementById('address').textContent = data.address;
            document.getElementById('city').textContent = data.city || '-';
            document.getElementById('region').textContent = data.state || data.region || '-';
            document.getElementById('country').textContent = data.country || '-';
            document.getElementById('coordinates').textContent = `${lat.toFixed(6)}, ${lon.toFixed(6)}`;
            document.getElementById('markedAt').textContent = new Date().toLocaleString();
        }

        function addToHistory(location, lat, lon) {
            const time = new Date().toLocaleTimeString();
            locationHistory.unshift({ location, lat, lon, time });
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
                        <div class="history-location">${item.location || 'Unknown'}</div>
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
