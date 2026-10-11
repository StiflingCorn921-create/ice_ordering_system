<?php

/**
 * Notifications Service: handles customer & admin text messages (SMS) and audit logs.
 * Supports Philippine SMS via Semaphore API (https://semaphore.co/).
 * Falls back cleanly to storage/logs/app.log if no SMS API key is configured.
 */
class NotificationService
{
    /**
     * Send an SMS message using Semaphore API.
     */
    public static function sendSMS(string $phone, string $message): bool
    {
        $apiKey = config('sms.api_key', '');
        if (empty($apiKey)) {
            // No API key configured: log to file instead
            app_log("[SMS SIMULATION] To: $phone | Message: $message");
            return true;
        }

        $sender = config('sms.sender_name', '');
        $data = [
            'apikey'  => $apiKey,
            'number'  => $phone,
            'message' => $message,
        ];
        if (!empty($sender)) {
            $data['sendername'] = $sender;
        }

        $ch = curl_init('https://api.semaphore.co/api/v4/messages');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            app_log("[SMS SENT] To: $phone | Response: $response");
            return true;
        }

        app_log("[SMS ERROR] Failed sending to $phone (HTTP $httpCode): $response");
        return false;
    }

    public static function orderPlaced(?array $order): void
    {
        if (!$order) {
            return;
        }

        $orderNum = $order['order_number'];
        $name     = $order['customer_name'];
        $phone    = $order['contact_number'];
        $total    = money($order['total_price']);

        // 1. Send SMS confirmation to Customer
        $customerMsg = "Hi $name! Your order ($orderNum) for $total has been received by Yulio's Ice. We'll update you as soon as it's prepared!";
        self::sendSMS($phone, $customerMsg);

        // 2. Alert the Store Owner (Aunt)
        $adminPhone = config('admin_phone');
        if ($adminPhone) {
            $adminMsg = "NEW ORDER! $orderNum from $name ($phone). Total: $total. Please check the admin dashboard.";
            self::sendSMS($adminPhone, $adminMsg);
        }

        app_log("NEW ORDER $orderNum from $name ($phone) - total $total");
    }

    public static function statusChanged(?array $order): void
    {
        if (!$order) {
            return;
        }

        $orderNum = $order['order_number'];
        $name     = $order['customer_name'];
        $phone    = $order['contact_number'];
        $status   = $order['status'];

        $statusMsg = "Hi $name! Your order ($orderNum) is now: $status. Thank you for choosing Yulio's Ice!";
        self::sendSMS($phone, $statusMsg);

        app_log("ORDER $orderNum status changed to '$status' - customer notified ($phone)");
    }
}
