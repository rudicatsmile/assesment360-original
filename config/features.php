<?php

return [
    'questionnaire_single_question_mode' => env('FEATURE_QUESTIONNAIRE_SINGLE_MODE', false),
    'login_mode' => env('FEATURE_LOGIN_MODE', 'both'), // password | whatsapp | both | bypass
    'otp_bypass' => env('FEATURE_OTP_BYPASS', false), // true = bypass OTP, false = kirim OTP via WhatsApp
];
