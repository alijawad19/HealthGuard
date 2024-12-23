<?php
header('Content-Type: application/json');
require('config/db_connection.php');

function generateCode($length = 14) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if both application_id and response_url are provided
    if (isset($_REQUEST['application_id']) && isset($_REQUEST['response_url'])) {
        $application_id = filter_var($_REQUEST['application_id'], FILTER_VALIDATE_INT);
        $url = filter_var($_REQUEST['response_url'], FILTER_SANITIZE_URL);
        $total_premium = filter_var($_REQUEST['total_premium'], FILTER_SANITIZE_URL);

        // Proceed only if valid application_id
        if ($application_id && filter_var($url, FILTER_VALIDATE_URL)) {
            try {
                // Prepare the SQL query
                $stmt = $conn->prepare("SELECT total_premium FROM proposals WHERE application_id = :application_id");
                $stmt->bindParam(':application_id', $application_id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                // Check if $url has query parameters
                $parsedUrl = parse_url($url);
                if (isset($parsedUrl['query']) && !empty($parsedUrl['query'])) {
                    $symbol = '&';
                } else {
                    $symbol = '?';
                }
                // print_r($result['total_premium']); die;
                if ($result['total_premium'] == $total_premium) {

                    $shortened_code = generateCode();
                    // echo $shortened_code; die;

                    $stmt = $conn->prepare("INSERT INTO payment_link (application_id, total_premium, shortened_code, response_url) 
                                            VALUES (:application_id, :total_premium, :shortened_code, :response_url)
                                            ON DUPLICATE KEY UPDATE 
                                            application_id = :application_id,
                                            total_premium = :total_premium,
                                            shortened_code = :shortened_code,
                                            response_url = :response_url
                                          ");
                    $stmt->execute(['application_id' => $application_id, 'total_premium' => $total_premium, 'shortened_code' => $shortened_code, 'response_url' => $url]);
                    
                    $payment_link = "http://healthguard?code=" . $shortened_code;
                    echo json_encode([
                        'status' => 'success',
                        'error' => null,
                        'data' => [
                            'message' => 'Payment link created successfully',
                            'application_id' => $application_id,
                            'total_premium' => $result['total_premium'],
                            'payment_url' => $payment_link
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'failed',
                        'error' => null,
                        'data' => [
                            'message' => 'Resource not found',
                            'application_id' => $application_id,
                            'total_premium' => $result['total_premium'],
                            'payment_url' => ''
                        ]
                    ]);
                }

                // $redirectUrl = $url . $symbol . 'status=' . $status . '&application_id=' . $application_id;
                // header("Location: " . $redirectUrl);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'error' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'failed', 'error' => 'Invalid application ID or response URL']);
        }
    } else {
        echo json_encode(['status' => 'failed', 'error' => 'Missing application ID or response URL']);
    }
} else {
    http_response_code(405); // Method not allowed
    echo json_encode(['status' => 'failed', 'error' => 'Invalid request method']);
}
