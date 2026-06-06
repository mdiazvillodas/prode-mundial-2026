<?php

return [
    'registration_daily_limit' => (int) env('REGISTRATION_DAILY_LIMIT', 50),
    'registration_ip_hourly_limit' => (int) env('REGISTRATION_IP_HOURLY_LIMIT', 5),
    'verification_email_daily_limit' => (int) env('VERIFICATION_EMAIL_DAILY_LIMIT', 80),
    'verification_resend_user_hourly_limit' => (int) env('VERIFICATION_RESEND_USER_HOURLY_LIMIT', 5),
    'verification_resend_user_daily_limit' => (int) env('VERIFICATION_RESEND_USER_DAILY_LIMIT', 10),
    'verification_resend_cooldown_seconds' => (int) env('VERIFICATION_RESEND_COOLDOWN_SECONDS', 60),
    'alert_email' => env('ABUSE_ALERT_EMAIL'),
    'alert_cooldown_minutes' => (int) env('ABUSE_ALERT_COOLDOWN_MINUTES', 360),
];
