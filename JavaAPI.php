
// ============= JavaAPI.php (Bridge to Java Backend) =============
<?php
class JavaAPI {
    private $apiUrl = "http://localhost:8080/impound/api"; // Your Java servlet URL
    
    // Call Java REST API
    private function callAPI($endpoint, $method = 'GET', $data = null) {
        $url = $this->apiUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($method === 'POST' && $data !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return ['success' => false, 'message' => 'API Error: ' . $httpCode];
        }
        
        return json_decode($response, true);
    }
    
    // Get all impounded vehicles
    public function getAllVehicles() {
        return $this->callAPI('/vehicles', 'GET');
    }
    
    // Search vehicles
    public function searchVehicles($query) {
        return $this->callAPI('/search?query=' . urlencode($query), 'GET');
    }
    
    // Impound a vehicle
    public function impoundVehicle($data) {
        return $this->callAPI('/impound', 'POST', $data);
    }
    
    // Release a vehicle
    public function releaseVehicle($data) {
        return $this->callAPI('/release', 'POST', $data);
    }
    
    // Get statistics
    public function getStatistics() {
        return $this->callAPI('/statistics', 'GET');
    }
}
?>