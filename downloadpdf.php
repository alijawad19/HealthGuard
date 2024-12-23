<?php
header('Content-Type: application/json');
require('config/db_connection.php');
require('fpdf186/fpdf.php');

$application_id = filter_var($_REQUEST['application_id'], FILTER_VALIDATE_INT);

if ($application_id) {
    $stmt = $conn->prepare("SELECT * FROM proposals WHERE application_id = :application_id");
    $stmt->bindParam(':application_id', $application_id, PDO::PARAM_INT);
    $stmt->execute();
    $policyDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($policyDetails) {
        // Generate PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Add logo
        $pdf->Image('logo.png', 10, 8, 33); // Adjust the path and size as necessary
        $pdf->Cell(0, 10, 'HealthGuard Insurance Co.', 0, 1, 'C');
        $pdf->Ln(10);

        // Welcome message
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell(0, 10, 'Welcome, ' . $policyDetails['name'], 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $policyDetails['address'], 0, 1);
        $pdf->Cell(0, 10, 'PIN: ' . $policyDetails['pincode'], 0, 1);
        $pdf->Cell(0, 10, 'Contact: ' . $policyDetails['contact'], 0, 1);
        $pdf->Ln(10);

        $pdf->Cell(0, 10, 'Your HealthGuard Insurance is now live!', 0, 1);
        $pdf->Ln(10);

        $pdf->Cell(0, 10, 'Policy Number: ' . $policyDetails['application_id'], 0, 1);
        $pdf->Cell(0, 10, 'Tenure: ' . $policyDetails['tenure'] . ' year(s)', 0, 1);
        $pdf->Cell(0, 10, 'Policy Start Date: ' . $policyDetails['start_date'], 0, 1);
        $pdf->Cell(0, 10, 'Policy End Date: ' . $policyDetails['end_date'], 0, 1);
        $pdf->Ln(10);

        // Insured Members Table
        $pdf->Cell(0, 10, 'Insured Members', 0, 1, 'C');
        $pdf->Ln(5);

        // Table header
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(45, 10, 'Member Name', 1);
        $pdf->Cell(45, 10, 'Relationship', 1);
        $pdf->Cell(45, 10, 'Date of Birth', 1);
        $pdf->Cell(45, 10, 'Coverage Amount', 1);
        $pdf->Ln(10);

        // Table content
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(45, 10, $policyDetails['name'], 1);
        $pdf->Cell(45, 10, 'Self', 1);
        $pdf->Cell(45, 10, $policyDetails['dob'], 1);
        $pdf->Cell(45, 10, $policyDetails['sum_insured'], 1);
        $pdf->Ln(10);

        // Nominee Details
        $pdf->Ln(10);
        $pdf->Cell(0, 10, 'Nominee Details', 0, 1, 'C');
        $pdf->Ln(5);

        // Nominee Table header
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(45, 10, 'Nominee Name', 1);
        $pdf->Cell(45, 10, 'Relationship', 1);
        $pdf->Cell(45, 10, 'Date of Birth', 1);
        $pdf->Cell(45, 10, 'Contact', 1);
        $pdf->Ln(10);

        // Table content for nominees
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(45, 10, $policyDetails['nominee_name'], 1);
        $pdf->Cell(45, 10, $policyDetails['nominee_relation'], 1);
        $pdf->Cell(45, 10, $policyDetails['nominee_dob'], 1);
        $pdf->Cell(45, 10, $policyDetails['nominee_contact'], 1);
        $pdf->Ln(10);

        // Policy Details
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Policy Details', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Type of Insurance: Health Insurance', 0, 1);
        $pdf->Cell(0, 10, 'Coverage Amount: INR ' . $policyDetails['sum_insured'], 0, 1);
        $pdf->Cell(0, 10, 'Total Premium excluding Taxes: INR ' . $policyDetails['net_premium'], 0, 1);
        $pdf->Cell(0, 10, 'Total Premium including Taxes: INR ' . $policyDetails['total_premium'], 0, 1);
        $pdf->Ln(10);

        // Policy Benefits
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Policy Benefits', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $benefits = [
            'Hospitalization Coverage: Covers up to INR 500,000 for hospitalization expenses.',
            'Pre and Post-Hospitalization: Covers medical expenses incurred 30 days prior and 60 days post hospitalization.',
            'Daycare Procedures: Covers certain daycare procedures.',
            'Ambulance Charges: Reimburses ambulance charges up to INR 5000.'
        ];
        foreach ($benefits as $benefit) {
            $pdf->Cell(0, 10, '* ' . $benefit, 0, 1);
        }
        $pdf->Ln(10);

        // Exclusions
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Exclusions', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $exclusions = [
            'Pre-existing conditions are not covered for the first 2 years.',
            'Cosmetic surgery unless medically necessary.',
            'Injuries due to participation in hazardous activities.'
        ];
        foreach ($exclusions as $exclusion) {
            $pdf->Cell(0, 10, '* ' . $exclusion, 0, 1);
        }
        $pdf->Ln(10);

        // Claim Procedure
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Claim Procedure', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $claimProcedures = [
            'Notify the insurer within 24 hours of hospitalization.',
            'Submit the claim form along with all necessary documents.',
            'Claims will be processed within 30 days.'
        ];
        foreach ($claimProcedures as $procedure) {
            $pdf->Cell(0, 10, '* ' . $procedure, 0, 1);
        }
        $pdf->Ln(10);

        // Contact Information
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Contact Information', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Insurer Name: HealthGuard Insurance Co.', 0, 1);
        $pdf->Cell(0, 10, 'Customer Service Phone: 1-800-555-0199', 0, 1);
        $pdf->Cell(0, 10, 'Email: support@healthguard.com', 0, 1);
        $pdf->Cell(0, 10, 'Website: www.healthguard.com', 0, 1);
        $pdf->Ln(10);

        // Terms and Conditions
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Terms and Conditions', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 10, 'This policy is subject to terms and conditions. Please read them carefully before making a claim.');

        // Footer
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Page ' . $pdf->PageNo(), 0, 0, 'C');

        // Output PDF
        $pdf->Output('D', 'HealthGuard_Insurance_Policy_' . $policyDetails['application_id'] . '.pdf');
    } else {
        echo json_encode(['error' => 'Policy not found.']);
    }
} else {
    echo json_encode(['error' => 'Invalid application ID.']);
}
