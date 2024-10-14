<?php
// Function to get latitude and longitude from city name using Open-Meteo Geocoding API
function getCoordinates($city) {
    // Replace spaces in city name with %20 for URL encoding
    $city = urlencode($city);
    
    // Geocoding API URL (adjusted for the Open-Meteo service)
    $geo_api_url = "https://geocoding-api.open-meteo.com/v1/search?name={$city}";
    
    // Initialize a cURL session for geocoding
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $geo_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $geo_response = curl_exec($ch);
    curl_close($ch);
    
    // Decode the JSON response
    $geo_data = json_decode($geo_response, true);
    
    // Check if the response contains valid data
    if (!empty($geo_data['results'][0])) {
        $latitude = $geo_data['results'][0]['latitude'];
        $longitude = $geo_data['results'][0]['longitude'];
        return ['latitude' => $latitude, 'longitude' => $longitude];
    } else {
        return null;  // City not found
    }
}

// Function to get weather data based on latitude and longitude
function getWeather($latitude, $longitude) {
    // Weather API URL
    $weather_api_url = "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&current_weather=true&hourly=temperature_2m,relative_humidity_2m,wind_speed_10m";
    
    // Initialize a cURL session for weather data
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $weather_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $weather_response = curl_exec($ch);
    curl_close($ch);
    
    // Decode the JSON response
    return json_decode($weather_response, true);
}

// Check if form is submitted
if (isset($_POST['city'])) {
    $city = $_POST['city'];
    
    // Get coordinates for the city
    $coordinates = getCoordinates($city);
    
    if ($coordinates) {
        // Fetch weather data for the given coordinates
        $weather_data = getWeather($coordinates['latitude'], $coordinates['longitude']);
        
        if ($weather_data) {
            // Display the weather data
            $current_time = $weather_data['current_weather']['time'];
            $current_temp = $weather_data['current_weather']['temperature'];
            $current_wind_speed = $weather_data['current_weather']['windspeed'];
            
            echo "<h3>Current Weather in " . htmlspecialchars($city) . "</h3>";
            echo "Time: $current_time<br>";
            echo "Temperature: $current_temp °C<br>";
            echo "Wind Speed: $current_wind_speed m/s<br><br>";
        } else {
            echo "Unable to retrieve weather data.";
        }
    } else {
        echo "City not found. Please try again.";
    }
}
?>

<!-- HTML Form to enter city name -->
<form method="POST">
    <label for="city">Enter City Name:</label>
    <input type="text" id="city" name="city" required>
    <button type="submit">Get Weather Report</button>
</form>
