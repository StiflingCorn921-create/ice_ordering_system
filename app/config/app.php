<?php
/**
 * General application settings.
 */
return [
    'name'         => "Yulio's Purified Cube Ice",
    // URL path where the project lives under htdocs (no trailing slash)
    'base_url'     => '/ice_ordering_system',
    'timezone'     => 'Asia/Manila',
    'currency'     => '₱',
    'debug'        => true, // set to false when the site goes live

    // Flat delivery fee (Pickup is free)
    'delivery_fee' => 30.00,

    // Store contact numbers (from official price list)
    'admin_phone'  => '09179048460',
    'support_phone'=> '09457799748',
    'service_area' => 'South Cebu',
    'reseller_min_kg' => 50,

    // SMS Gateway (Semaphore - Philippine SMS API)
    // Register free at semaphore.co to get an API key
    'sms' => [
        'api_key'     => '', // Add Semaphore API key here for live SMS
        'sender_name' => '', // Approved Semaphore sender name (or blank for default)
    ],

    // Local Ollama settings
    'ollama' => [
        'url'     => 'http://localhost:11434/api/chat',
        'model'   => 'qwen3:4b',
        'timeout' => 120,
    ],
];
