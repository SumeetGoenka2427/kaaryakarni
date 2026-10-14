<?php

// Status / option labels for App\Enums\* (Hindi).
return [
    'gender' => ['male' => 'पुरुष', 'female' => 'महिला', 'other' => 'अन्य'],

    'member_status' => [
        'pending' => 'प्रोफ़ाइल अधूरी',
        'under_verification' => 'सत्यापन प्रक्रिया में',
        'verified' => 'सत्यापित',
        'rejected' => 'अस्वीकृत',
        'suspended' => 'निलंबित',
    ],

    'membership_status' => [
        'pending' => 'लंबित',
        'payment_pending' => 'भुगतान लंबित',
        'payment_submitted' => 'भुगतान जमा',
        'under_verification' => 'सत्यापन प्रक्रिया में',
        'active' => 'आजीवन सदस्य',
        'rejected' => 'अस्वीकृत',
        'suspended' => 'निलंबित',
    ],

    'payment_status' => [
        'created' => 'आरंभ',
        'pending' => 'लंबित',
        'submitted' => 'जमा',
        'successful' => 'सफल',
        'verified' => 'सत्यापित',
        'failed' => 'असफल',
        'refunded' => 'धनवापसी',
        'cancelled' => 'रद्द',
    ],

    'payment_mode' => ['online' => 'ऑनलाइन', 'offline' => 'ऑफ़लाइन'],

    'payment_purpose' => ['membership' => 'आजीवन सदस्यता', 'donation' => 'दान', 'ticket' => 'कार्यक्रम टिकट'],

    'offline_method' => ['cash' => 'नकद', 'cheque' => 'चेक', 'upi' => 'यूपीआई', 'bank_transfer' => 'बैंक ट्रांसफ़र (NEFT/IMPS)'],

    'donation_status' => [
        'pending' => 'लंबित',
        'submitted' => 'जमा',
        'successful' => 'सफल',
        'verified' => 'सत्यापित',
        'failed' => 'असफल',
        'refunded' => 'धनवापसी',
        'cancelled' => 'रद्द',
    ],

    'business_status' => [
        'draft' => 'ड्राफ़्ट',
        'pending' => 'स्वीकृति लंबित',
        'approved' => 'स्वीकृत',
        'rejected' => 'अस्वीकृत',
        'changes_requested' => 'बदलाव आवश्यक',
        'hidden' => 'छिपा हुआ',
    ],

    'matrimonial_status' => [
        'draft' => 'ड्राफ़्ट',
        'pending' => 'स्वीकृति लंबित',
        'approved' => 'स्वीकृत',
        'rejected' => 'अस्वीकृत',
        'hidden' => 'छिपा हुआ',
        'suspended' => 'निलंबित',
    ],

    'family_relation' => [
        'spouse' => 'पति / पत्नी',
        'son' => 'पुत्र',
        'daughter' => 'पुत्री',
        'father' => 'पिता',
        'mother' => 'माता',
        'brother' => 'भाई',
        'sister' => 'बहन',
        'daughter_in_law' => 'पुत्रवधू',
        'son_in_law' => 'दामाद',
        'grandson' => 'पौत्र / नाती',
        'granddaughter' => 'पौत्री / नातिन',
        'other' => 'अन्य',
    ],

    'marital_status' => ['unmarried' => 'अविवाहित', 'married' => 'विवाहित', 'widowed' => 'विधवा / विधुर', 'divorced' => 'तलाकशुदा'],
];
