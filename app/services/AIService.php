<?php

/**
 * Talks to the local Ollama server (http://localhost:11434).
 * Make sure Ollama is running and the model is pulled:  ollama pull llama3
 */
class AIService
{
    private const CUSTOMER_PROMPT = <<<TXT
You are the friendly assistant of Yulio's Purified Cube Ice, a small ice business in the Philippines.
Help customers choose how much ice to order. Rule of thumb: about 1 kg of ice per 2-3 people for a party,
more on hot days or for coolers. Only recommend products from the list below. Keep answers short (max 4 sentences).
If asked about anything unrelated to ice orders, politely steer back to ordering.
Products:
{PRODUCTS}
TXT;

    private const ADMIN_PROMPT = <<<TXT
You are a business assistant for the owner of Yulio's Purified Cube Ice. Answer using ONLY the data below.
Be concise and practical. Use Philippine Peso (₱).
Data:
{DATA}
TXT;

    /** Customer-facing chat (ice calculator / product questions). */
    public static function customerChat(string $message, string $sessionId): array
    {
        $productLines = array_map(
            fn($p) => "- {$p['name']} ({$p['weight_kg']} kg): ₱{$p['retail_price']}",
            Product::allActive()
        );
        $system = str_replace('{PRODUCTS}', implode("\n", $productLines), self::CUSTOMER_PROMPT);

        return self::chat($system, $message, $sessionId, 'customer');
    }

    /** Admin assistant: answers questions about today's business numbers. */
    public static function adminChat(string $message, string $sessionId): array
    {
        $data = [
            'stats'        => Order::stats(),
            'low_stock'    => array_map(fn($p) => ['name' => $p['name'], 'stock' => $p['stock_quantity']], InventoryService::lowStock()),
            'top_products' => OrderItem::topProducts(7),
            'daily_sales'  => Order::dailySales(7),
        ];
        $system = str_replace('{DATA}', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), self::ADMIN_PROMPT);

        return self::chat($system, $message, $sessionId, 'admin');
    }

    /** @return array ['ok' => bool, 'reply' => string] */
    private static function chat(string $system, string $message, string $sessionId, string $role): array
    {
        $model = config('ollama.model', 'llama3');

        $payload = json_encode([
            'model'    => $model,
            'stream'   => false,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $message],
            ],
        ]);

        $ch = curl_init(config('ollama.url'));
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => (int) config('ollama.timeout', 60),
        ]);
        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $code !== 200) {
            app_log("Ollama error (HTTP $code): $err $raw");
            return ['ok' => false, 'reply' => 'The assistant is offline right now. Please try again later.'];
        }

        $reply = trim(json_decode($raw, true)['message']['content'] ?? '');
        if ($reply === '') {
            return ['ok' => false, 'reply' => 'The assistant could not answer that. Please try again.'];
        }

        $stmt = db()->prepare('INSERT INTO ai_logs (session_id, user_role, prompt, response, model_name) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$sessionId, $role, $message, $reply, $model]);

        return ['ok' => true, 'reply' => $reply];
    }
}

