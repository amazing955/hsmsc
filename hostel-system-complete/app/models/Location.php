<?php
class Location {
    // OpenStreetMap Nominatim API endpoint
    const NOMINATIM_API = 'https://nominatim.openstreetmap.org/search';
    const NOMINATIM_REVERSE = 'https://nominatim.openstreetmap.org/reverse';
    
    // Default Kampala center for fallback
    const KAMPALA_CENTER_LAT = 0.3365;
    const KAMPALA_CENTER_LON = 32.5775;
    
    // Cache for coordinates during request
    private static $coordinateCache = [];

    /**
     * Get coordinates for a location using OpenStreetMap Nominatim API
     * Returns [latitude, longitude]
     */
    public static function getCoordinates($locationName) {
        $location = trim($locationName);
        
        // Check cache first
        $cacheKey = strtolower($location);
        if (isset(self::$coordinateCache[$cacheKey])) {
            return self::$coordinateCache[$cacheKey];
        }
        
        // Try to get coordinates from Nominatim API
        $coords = self::getCoordinatesFromNominatim($location);
        
        if ($coords) {
            self::$coordinateCache[$cacheKey] = $coords;
            return $coords;
        }
        
        // Fallback: assume it's in Kampala area with slight variance
        $coords = self::getDefaultKampalaCoordinates();
        self::$coordinateCache[$cacheKey] = $coords;
        return $coords;
    }

    /**
     * Query OpenStreetMap Nominatim API for location coordinates
     */
    private static function getCoordinatesFromNominatim($locationName) {
        try {
            // Build search query biased towards Uganda
            $query = $locationName . ', Uganda';
            
            $url = self::NOMINATIM_API . '?' . http_build_query([
                'q' => $query,
                'format' => 'json',
                'limit' => 1,
                'email' => 'noreply@hostelapp.local'
            ]);
            
            // Suppress warnings from file_get_contents
            $response = @file_get_contents($url, false, stream_context_create([
                'http' => ['timeout' => 5]
            ]));
            
            if ($response === false) {
                return null;
            }
            
            $data = json_decode($response, true);
            
            if (empty($data) || !isset($data[0])) {
                return null;
            }
            
            $lat = floatval($data[0]['lat']);
            $lon = floatval($data[0]['lon']);
            
            // Validate coordinates are in Uganda region (approximately)
            // Uganda: lat -1.5 to 4.2, lon 29.3 to 35.4
            if ($lat >= -2 && $lat <= 4.5 && $lon >= 29 && $lon <= 36) {
                return [$lat, $lon];
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get default coordinates within Kampala area with small variance
     */
    private static function getDefaultKampalaCoordinates() {
        $latOffset = (rand(-50, 50) / 10000); // ±0.005 degree
        $lonOffset = (rand(-50, 50) / 10000); // ±0.005 degree
        
        return [
            self::KAMPALA_CENTER_LAT + $latOffset,
            self::KAMPALA_CENTER_LON + $lonOffset
        ];
    }

    /**
     * Calculate distance between two locations using Haversine formula
     * Returns distance in kilometers
     */
    public static function calculateDistance($location1, $location2) {
        $coords1 = self::getCoordinates($location1);
        $coords2 = self::getCoordinates($location2);
        
        $lat1 = $coords1[0];
        $lon1 = $coords1[1];
        $lat2 = $coords2[0];
        $lon2 = $coords2[1];
        
        // Haversine formula
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        return max(1, round($distance, 1)); // Minimum 1km
    }

    /**
     * Calculate transport fare based on SafeBoda pricing
     * Minimum: 1500 UGX
     * Rate: 2000 UGX per km
     */
    public static function calculateFare($distance) {
        $baseRate = 2000; // UGX per km
        $minimumFare = 1000; // UGX
        
        $fare = $distance * $baseRate;
        
        // Apply minimum fare
        if ($fare < $minimumFare) {
            $fare = $minimumFare;
        }
        
        // Round to nearest 500 for easier payment
        $fare = round($fare / 500) * 500;
        
        return $fare;
    }

    /**
     * Calculate both distance and fare
     * Returns associative array with distance (km) and cost (UGX)
     */
    public static function calculateTransportCost($pickupLocation, $destinationLocation) {
        $distance = self::calculateDistance($pickupLocation, $destinationLocation);
        $cost = self::calculateFare($distance);
        
        return [
            'distance' => $distance,
            'cost' => $cost,
            
            'currency' => 'UGX'
            
        ];
    }

    /**
     * Get location name from coordinates (reverse geocoding)
     * Useful for displaying rider's location
     */
    public static function getLocationName($latitude, $longitude) {
        try {
            $url = self::NOMINATIM_REVERSE . '?' . http_build_query([
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'json',
                'zoom' => 18,
                'email' => 'noreply@hostelapp.local'
            ]);
            
            $response = @file_get_contents($url, false, stream_context_create([
                'http' => ['timeout' => 5]
            ]));
            
            if ($response === false) {
                return 'Location (' . round($latitude, 4) . ', ' . round($longitude, 4) . ')';
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['address'])) {
                // Return district/village/suburb name
                $address = $data['address'];
                if (isset($address['suburb'])) return $address['suburb'];
                if (isset($address['village'])) return $address['village'];
                if (isset($address['district'])) return $address['district'];
                if (isset($address['town'])) return $address['town'];
                if (isset($address['city'])) return $address['city'];
            }
            
            return 'Location (' . round($latitude, 4) . ', ' . round($longitude, 4) . ')';
        } catch (Exception $e) {
            return 'Location (' . round($latitude, 4) . ', ' . round($longitude, 4) . ')';
        }
    }

    /**
     * Verify if a location exists using Nominatim
     */
    public static function isValidLocation($locationName) {
        $coords = self::getCoordinatesFromNominatim($locationName);
        return $coords !== null;
    }
}
?>
