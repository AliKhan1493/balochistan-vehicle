<?php
/**
 * Public Search API Endpoint
 * Handles vehicle search requests from end users without requiring them to log in.
 * Never shares backend credentials or cookies.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

require_once __DIR__ . '/EtanbService.php';

// Method check
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$params = ($method === 'POST') ? $_POST : $_GET;

$vehicleNo = trim($params['vehicle_no'] ?? '');
$location = trim($params['location'] ?? '1');

if (empty($vehicleNo)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a vehicle registration number (e.g. QTA-1234).'
    ]);
    exit;
}

// Clean and validate format
$vehicleNo = strtoupper($vehicleNo);
if (!preg_match('/^[A-Z0-9\-\s]{2,15}$/', $vehicleNo)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid vehicle number format. Please check the number and try again.'
    ]);
    exit;
}

// Ensure location is valid
$locations = [
    '1' => 'QUETTA',
    '2' => 'HUB'
];
$locationName = $locations[$location] ?? 'QUETTA';

try {
    $service = new EtanbService();
    $result = $service->searchVehicle($vehicleNo, $location);

    if (!$result['success']) {
        echo json_encode([
            'success' => false,
            'message' => $result['error'] ?? 'Search could not be completed at this time.'
        ]);
        exit;
    }

    $payload = $result['data'] ?? [];

    // If upstream returned JSON
    if (isset($payload['type']) && $payload['type'] === 'json') {
        $data = $payload['data'] ?? [];
        $status = $data['status'] ?? false;

        if ($status === true || isset($data['vehicleInfo'])) {
            echo json_encode([
                'success'       => true,
                'found'         => true,
                'vehicle_no'    => $vehicleNo,
                'district'      => $locationName,
                'vehicle_info'  => $data['vehicleInfo'] ?? [],
                'transfers'     => $data['transfers'] ?? [],
                'owners'        => $data['owners'] ?? []
            ]);
            exit;
        } else {
            echo json_encode([
                'success'    => true,
                'found'      => false,
                'vehicle_no' => $vehicleNo,
                'district'   => $locationName,
                'message'    => $data['message'] ?? 'No vehicle record found for registration number ' . $vehicleNo . ' in ' . $locationName . '.'
            ]);
            exit;
        }
    }

    // Fallback if HTML table was parsed
    if (isset($payload['fields']) && !empty($payload['fields'])) {
        echo json_encode([
            'success'      => true,
            'found'        => true,
            'vehicle_no'   => $vehicleNo,
            'district'     => $locationName,
            'fields'       => $payload['fields']
        ]);
        exit;
    }

    echo json_encode([
        'success'    => true,
        'found'      => false,
        'vehicle_no' => $vehicleNo,
        'district'   => $locationName,
        'message'    => 'Vehicle not found or no public records available.'
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while querying the vehicle records. Please try again.'
    ]);
}
