<?php

// Status / option labels for App\Enums\* (English).
return [
    'gender' => ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'],

    'member_status' => [
        'pending' => 'Profile Incomplete',
        'under_verification' => 'Under Verification',
        'verified' => 'Verified',
        'rejected' => 'Rejected',
        'suspended' => 'Suspended',
    ],

    'membership_status' => [
        'pending' => 'Pending',
        'payment_pending' => 'Payment Pending',
        'payment_submitted' => 'Payment Submitted',
        'under_verification' => 'Under Verification',
        'active' => 'Lifetime Member',
        'rejected' => 'Rejected',
        'suspended' => 'Suspended',
    ],

    'payment_status' => [
        'created' => 'Initiated',
        'pending' => 'Pending',
        'submitted' => 'Submitted',
        'successful' => 'Successful',
        'verified' => 'Verified',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
        'cancelled' => 'Cancelled',
    ],

    'payment_mode' => ['online' => 'Online', 'offline' => 'Offline'],

    'payment_purpose' => ['membership' => 'Lifetime Membership', 'donation' => 'Donation', 'ticket' => 'Event Ticket'],

    'offline_method' => ['cash' => 'Cash', 'cheque' => 'Cheque', 'upi' => 'UPI', 'bank_transfer' => 'Bank Transfer (NEFT/IMPS)'],

    'donation_status' => [
        'pending' => 'Pending',
        'submitted' => 'Submitted',
        'successful' => 'Successful',
        'verified' => 'Verified',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
        'cancelled' => 'Cancelled',
    ],

    'business_status' => [
        'draft' => 'Draft',
        'pending' => 'Pending Approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'changes_requested' => 'Changes Requested',
        'hidden' => 'Hidden',
    ],

    'matrimonial_status' => [
        'draft' => 'Draft',
        'pending' => 'Pending Approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'hidden' => 'Hidden',
        'suspended' => 'Suspended',
    ],

    'family_relation' => [
        'spouse' => 'Spouse (Wife / Husband)',
        'son' => 'Son',
        'daughter' => 'Daughter',
        'father' => 'Father',
        'mother' => 'Mother',
        'brother' => 'Brother',
        'sister' => 'Sister',
        'daughter_in_law' => 'Daughter-in-law',
        'son_in_law' => 'Son-in-law',
        'grandson' => 'Grandson',
        'granddaughter' => 'Granddaughter',
        'other' => 'Other',
    ],
];
