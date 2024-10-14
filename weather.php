<?php
// Function to get latitude and longitude from city name using Open-Meteo Geocoding API
function getCoordinates($city) {
    $city = urlencode($city);  // URL-encode the city name
    
    // Geocoding API URL
    $geo_api_url = "https://geocoding-api.open-meteo.com/v1/search?name={$city}";
    
    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $geo_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $geo_response = curl_exec($ch);
    curl_close($ch);
    
    // Decode JSON response
    $geo_data = json_decode($geo_response, true);
    
    // Check if the response contains valid data
    if (!empty($geo_data['results'][0])) {
        $latitude = $geo_data['results'][0]['latitude'];
        $longitude = $geo_data['results'][0]['longitude'];
        $timezone = $geo_data['results'][0]['timezone'];
        return ['latitude' => $latitude, 'longitude' => $longitude, 'timezone' => $timezone];
    } else {
        return null;  // City not found
    }
}

// Function to get weather data based on latitude and longitude
function getWeather($latitude, $longitude, $timezone) {
    // Weather API URL
    $weather_api_url = "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&timezone={$timezone}&current_weather=true";
    
    // Initialize cURL session for weather data
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $weather_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $weather_response = curl_exec($ch);
    curl_close($ch);
    
    // Decode JSON response
    return json_decode($weather_response, true);
}

// Check if form is submitted
if (isset($_POST['city'])) {
    $city = $_POST['city'];
    $html = '';
    
    // Get coordinates for the city
    $coordinates = getCoordinates($city);
    
    if ($coordinates) {
        // Fetch weather data for the given coordinates
        $weather_data = getWeather($coordinates['latitude'], $coordinates['longitude'], $coordinates['timezone']);
        
        if ($weather_data) {
            // Extract necessary information
            $current_time = $weather_data['current_weather']['time'];
            $current_temp = $weather_data['current_weather']['temperature'];
            $current_wind_speed = $weather_data['current_weather']['windspeed'];
            $timezone = $coordinates['timezone'];
            
            // Convert the current time to the city's local time
            $time = new DateTime($current_time, new DateTimeZone('UTC'));  // Time from API is in UTC
            $time->setTimezone(new DateTimeZone($timezone));  // Convert to the city's timezone
            $hour = $time->format('H');  // Extract the hour in 24-hour format
            $a = '';
            // Determine day or night and select the correct icon
            if ($hour >= 6 && $hour < 18) {
                // Daytime
                if ($current_temp < 10) {
                    $weatherIcon = 'cold.png';
                } elseif ($current_temp >= 10 && $current_temp < 20) {
                    $weatherIcon = 'breezy.png';
                } elseif ($current_temp >= 20 && $current_temp < 30) {
                    $weatherIcon = 'warm.png';
                } else {
                    $weatherIcon = 'hot.png';
                }
                $a .= 'Day';
            } else {
                // Nighttime
                if ($current_temp < 10) {
                    $weatherIcon = 'nightcold.png';
                } elseif ($current_temp >= 10 && $current_temp < 20) {
                    $weatherIcon = 'nightbreezy.png';
                } elseif ($current_temp >= 20 && $current_temp < 30) {
                    $weatherIcon = 'nightwarm.png';
                } else {
                    $weatherIcon = 'nighthot.png';
                }
                $a .= 'Night';
            }

            // Prepare the HTML output
            $html .= '
            <div class="of7du8">
                <div class="yi7gke">
                    <h1>' . $current_temp . ' °C</h1>
                </div>
                <div class="urxcy7">
                    <div class="gi0rkp">
                        <img src="assets/images/' . $weatherIcon . '" alt="" class="o96gjku">
                    </div>
                </div>
                <div class="ir7dgj">
                    <span><small><strong>Time:</strong> ' . $time->format('Y-m-d H:i:s') . '</small></span>
                    <span><small><strong>Wind Speed:</strong> ' . $current_wind_speed . ' m/s</small></span>
                </div>';
                $html .= $a;
            $html .= '</div>';

            // Return the HTML content as JSON
            echo json_encode(['report' => $html]);
        } else {
            echo "Unable to retrieve weather data.";
        }
    } else {
        echo "City not found. Please try again.";
    }
}
?>
