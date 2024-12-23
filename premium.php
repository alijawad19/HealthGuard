<?php
header('Content-Type: application/json');

require('calculate_premium.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    // echo "<pre>"; print_r($data); echo "</pre>"; exit;

    $age = isset($data['age']) ? intval($data['age']) : null;
    $gender = isset($data['gender']) ? htmlspecialchars(trim($data['gender'])) : null;
    $tenure = isset($data['tenure']) ? intval($data['tenure']) : null;
    $sum_insured = isset($data['sum_insured']) ? intval($data['sum_insured']) : null;

    if (is_null($age)) {
        $errors[] = 'Age is required';
        echo json_encode(['status' => 'failed', 'error' => 'Age is required']);
        exit();
    }

    if (is_null($gender)) {
        $errors[] = 'Gender is required';
        echo json_encode(['status' => 'failed', 'error' => 'Gender is required']);
        exit();
    }

    if (is_null($tenure)) {
        $errors[] = 'Tenure is required';
        echo json_encode(['status' => 'failed', 'error' => 'Tenure is required']);
        exit();
    }

    if (is_null($sum_insured)) {
        $errors[] = 'Sum Insured is required';
        echo json_encode(['status' => 'failed', 'error' => 'Sum Insured is required']);
        exit();
    }

    // Validate age
    if ($age > 65) {
        echo json_encode(['status' => 'failed', 'error' => 'Age exceeds the limit of 65']);
        exit();
    }

    // Validate tenure
    if ($tenure < 1 || $tenure > 3) {
        echo json_encode(['status' => 'failed', 'error' => 'Tenure must be between 1 and 3 years']);
        exit();
    }

    // Validate gender
    if (!in_array($gender, ['male', 'female'])) {
        echo json_encode(['status' => 'failed', 'error' => 'Gender must be either "male" or "female"']);
        exit();
    }

    // Validate sum insured
    if (!in_array($sum_insured, ['500000', '1000000', '2500000'])) {
        echo json_encode(['status' => 'failed', 'error' => 'Given sum insured is not available']);
        exit();
    }

    // Calculate premium
    if ($age > 0) {
        $premium = calculatePremium($age, $gender, $tenure, $sum_insured);
        
        if ($premium === null) {
            echo json_encode(['status' => 'failed', 'error' => 'Failed to get premium amount.']);
            exit();
        }

        $gst = $premium * 0.18;
        $total_premium = round($premium + $gst);

        echo json_encode([
            'status' => 'success',
            'error' => null,
            'data' => [
                'net_premium' => $premium,
                'gst' => $gst,
                'total_premium' => $total_premium,
                'tenure' => $tenure,
                'age' => $age,
                'gender' => $gender,
                'sum_insured' => $sum_insured
            ]
        ]);
    } else {
        echo json_encode(['status' => 'failed', 'error' => 'Invalid input']);
    }
} else {
    echo json_encode(['status' => 'failed', 'error' => 'Invalid request method']);
}
?>
