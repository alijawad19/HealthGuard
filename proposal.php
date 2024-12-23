<?php
header('Content-Type: application/json');
require('config/db_connection.php');
require('calculate_premium.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    // echo "<pre>"; print_r($data); echo "</pre>"; exit;

    function calculateAge($dob) {
        $dobDateTime = DateTime::createFromFormat('Y-m-d', $dob);
        if (!$dobDateTime) {
            return null;
        }
        $currentDate = new DateTime();
        $age = $currentDate->diff($dobDateTime)->y;
        return $age;
    }    

    $name = isset($data['name']) && trim($data['name']) !== '' ? htmlspecialchars(trim($data['name'])) : null;
    $email = isset($data['email']) && trim($data['email']) !== '' ? htmlspecialchars(trim($data['email'])) : null;
    $address = isset($data['address']) && trim($data['address']) !== '' ? htmlspecialchars(trim($data['address'])) : null;
    // $age = isset($data['age']) && filter_var($data['age'], FILTER_VALIDATE_INT) && $data['age'] >= 18 && $data['age'] <= 65 ? intval($data['age']) : null;
    $gender = isset($data['gender']) && trim($data['gender']) !== '' ? htmlspecialchars(trim($data['gender'])) : null;
    $premium = isset($data['premium']) ? intval($data['premium']) : null;
    $tenure = isset($data['tenure']) ? intval($data['tenure']) : null;
    $sum_insured = isset($data['sum_insured']) && filter_var($data['sum_insured'], FILTER_VALIDATE_INT) ? intval($data['sum_insured']) : null;

    $pincode = isset($data['pincode']) && !is_null($data['pincode']) && preg_match('/^\d{6}$/', $data['pincode']) ? $data['pincode'] : null;

    $contact = isset($data['contact']) && !is_null($data['contact']) && preg_match('/^\d{10}$/', $data['contact']) ? $data['contact'] : null;

    $dob = isset($data['dob']) && trim($data['dob']) !== '' ? $data['dob'] : null;

    $nomineeName = isset($data['nomineeDetails']['name']) && trim($data['nomineeDetails']['name']) !== '' ? htmlspecialchars(trim($data['nomineeDetails']['name'])) : null;
    $nomineeRelation = isset($data['nomineeDetails']['relation']) && trim($data['nomineeDetails']['relation']) !== '' ? htmlspecialchars(trim($data['nomineeDetails']['relation'])) : null;
    $nomineeDob = isset($data['nomineeDetails']['dob']) && trim($data['nomineeDetails']['dob']) !== '' ? $data['nomineeDetails']['dob'] : null;
    $nomineeContact = isset($data['nomineeDetails']['contact']) && !is_null($data['nomineeDetails']['contact']) && preg_match('/^\d{10}$/', $data['nomineeDetails']['contact']) ? $data['nomineeDetails']['contact'] : null;

    $errors = [];

    if (is_null($name)) {
        $errors[] = 'Name is required';
    }

    if (is_null($email)) {
        $errors[] = 'Email is required';
    }

    if (is_null($address)) {
        $errors[] = 'Address is required';
    }

    if (is_null($gender)) {
        $errors[] = 'Gender is required';
    }

    if (is_null($tenure)) {
        $errors[] = 'Tenure is required';
    }

    // Validate tenure
    if ($tenure < 1 || $tenure > 3) {
        $errors[] = 'Tenure must be between 1 and 3 years';
    }

    // Validate gender
    if (!in_array($gender, ['male', 'female'])) {
        $errors[] = 'Gender must be either "male" or "female"';
    }

    // Validate sum insured
    $allowed_sum_insured_values = [500000, 1000000, 2500000];
    if (is_null($sum_insured) || !in_array($sum_insured, $allowed_sum_insured_values)) {
        $errors[] = 'Given sum insured is not available';
    }

    // Pincode validation
    if (is_null($pincode)) {
        $errors[] = 'Pincode must be a 6-digit number';
    }

    // Contact validation
    if (is_null($contact)) {
        $errors[] = 'Contact must be a 10-digit number';
    }

    // DOB validation
    if (!is_null($dob)) {
        $age = calculateAge($dob);
        if ($age === null || $age < 18 || $age > 65) {
            $errors[] = 'The applicant must be between 18 and 65 years old';
        }
    } else {
        $errors[] = 'Date of birth for the applicant is required';
    }    

    // Nominee validations
    if (is_null($nomineeName)) {
        $errors[] = 'Nominee name is required';
    }

    $allowed_relations = [
        'Father', 'Mother', 'Spouse', 'Son', 'Daughter', 'Brother', 'Sister', 'Uncle', 
        'Aunt', 'Nephew', 'Niece', 'Grandfather', 'Grandmother', 'Grandson', 'Granddaughter', 
        'Cousin', 'Friend', 'Legal Guardian'
    ];
    if (is_null($nomineeRelation) || !in_array($nomineeRelation, $allowed_relations)) {
        $errors[] = 'Invalid relation provided';
    }

    if (!is_null($nomineeDob)) {
        $nomineeAge = calculateAge($nomineeDob);
        if ($nomineeAge === null || $nomineeAge < 18) {
            $errors[] = 'The nominee must be at least 18 years old';
        }
    } else {
        $errors[] = 'Date of birth for the nominee is required';
    }
    
    if (is_null($nomineeContact)) {
        $errors[] = 'Nominee contact must be a 10-digit number';
    }

    // If errors exist, return the error messages
    if (!empty($errors)) {
        echo json_encode(['status' => 'failed', 'errors' => $errors]);
        exit();
    }

    $applicationId = rand(10000, 99999);
    $premium = calculatePremium($age, $gender, $tenure, $sum_insured);
    // echo "premium: " . $premium; exit;
    
    if ($premium === null) {
        echo json_encode(['status' => 'failed', 'error' => 'Failed to get premium amount.']);
        exit();
    }

    $gst = $premium * 0.18;
    $total_premium = round($premium + $gst);

    $stmt = $conn->prepare("INSERT INTO proposals (name, email, address, age, gender, net_premium, total_premium, tenure, sum_insured, application_id, pincode, contact, dob, nominee_name, nominee_relation, nominee_dob, nominee_contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $address, $age, $gender, $premium, $total_premium, $tenure, $sum_insured, $applicationId, $pincode, $contact, $dob, $nomineeName, $nomineeRelation, $nomineeDob, $nomineeContact])) {

        $payment_link = 'http://healthguard/payment?application_id=' . $applicationId . '&total_premium=' . $total_premium;

        echo json_encode([
            'status' => 'success',
            'error' => null,
            'data' => [
                'message' => 'Proposal created successfully',
                'application_id' => $applicationId,
                'net_premium' => $premium,
                'gst' => $gst,
                'total_premium' => $total_premium,
                'tenure' => $tenure,
                'age' => $age,
                'gender' => $gender,
                'sum_insured' => $sum_insured,
                "name" => $name,
                "email" => $email,
                "address" => $address,
                "pincode" => $pincode,
                "contact" => $contact,
                "dob" => $dob,
                "nomineeDetails" => [
                    "name" => $nomineeName,
                    "relation" => $nomineeRelation,
                    "dob" => $nomineeDob,
                    "contact" => $nomineeContact
                ],
                'url' => $payment_link
            ]
        ]);
    } else {
        echo json_encode(['status' => 'failed', 'error' => 'Failed to create proposal']);
    }
} else {
    echo json_encode(['status' => 'failed', 'error' => 'Invalid request method']);
}
