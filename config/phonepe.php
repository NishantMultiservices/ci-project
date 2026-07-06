<?php
// PhonePe Payment Gateway - Production Credentials
define('PHONEPE_MERCHANT_ID', 'M22BZN06WPAW2');
define('PHONEPE_SALT_KEY', '62cdd50b-3dd7-4c60-b308-0e152a40a2a4');
define('PHONEPE_SALT_INDEX', 1);
define('PHONEPE_ENV', 'PRODUCTION');

if (PHONEPE_ENV === 'PRODUCTION') {
    define('PHONEPE_BASE_URL', 'https://api.phonepe.com/apis/hermes');
} else {
    define('PHONEPE_BASE_URL', 'https://api-preprod.phonepe.com/apis/pg-sandbox');
}
