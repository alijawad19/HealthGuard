<?php

function calculatePremium($age, $gender, $tenure, $sumInsured) {
    $basePremium = 1000;
    $genderDiscount = ($gender === 'female') ? 0.05 : 0;
    $sumInsuredFactor = $sumInsured / 500000;

    if ($tenure == 3) {
        $tenureDiscount = 0.15;
    } elseif ($tenure == 2) {
        $tenureDiscount = 0.1;
    } elseif ($tenure == 1) {
        $tenureDiscount = 0;
    }

    if ($age < 18) {
        $premium = round($basePremium * 0.5);
    } elseif ($age <= 30) {
        $premium = round($basePremium);
    } elseif ($age <= 50) {
        $premium = round($basePremium * 1.5);
    } elseif ($age <= 65) {
        $premium = round($basePremium * 2);
    }

    $premium = round($premium * (1 - $genderDiscount));

    $premium = round($premium * $sumInsuredFactor);

    $premium = round($premium * ($tenure - $tenureDiscount));

    return round($premium);
}


?>