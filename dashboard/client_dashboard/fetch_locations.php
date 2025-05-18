<?php
header('Content-Type: application/json');

// Path to the locations.json file
$locations_file = __DIR__ . '/../data/locations.json';

try {
    // Read and decode the JSON file
    $json_data = file_get_contents($locations_file);
    $data = json_decode($json_data, true);
    
    // Filter only active locations
    $active_locations = array_filter($data['locations'], function($location) {
        return $location['status'] === 'Active';
    });
    
    // Return the active locations
    echo json_encode([
        'success' => true,
        'locations' => array_values($active_locations)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error fetching locations: ' . $e->getMessage()
    ]);
}
?> 