<?php

/**
 * Asymmetric Authentication Service (RS256 JWT).
 * 
 * Uses an RSA 2048-bit Private Key to SIGN tokens and a Public Key to VERIFY tokens.
 * This guarantees that only the backend server can create valid tokens,
 * while other services (such as the Python AI service or mobile clients)
 * can verify tokens using ONLY the public key, without having the private secret.
 */
class TokenService
{
    private static function privateKeyPath(): string
    {
        return dirname(APP_ROOT) . '/storage/keys/private.pem';
    }

    private static function publicKeyPath(): string
    {
        return dirname(APP_ROOT) . '/storage/keys/public.pem';
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Create an RS256 JWT signed with the RSA Private Key.
     *
     * @param array $payload Custom claims (e.g., user id, username, role)
     * @param int $ttl Time to live in seconds (default: 24 hours)
     * @return string Signed RS256 JWT token
     */
    public static function createToken(array $payload, int $ttl = 86400): string
    {
        $privPath = self::privateKeyPath();
        if (!file_exists($privPath)) {
            throw new RuntimeException('Private key file not found at ' . $privPath);
        }

        $privateKey = openssl_pkey_get_private(file_get_contents($privPath));
        if (!$privateKey) {
            throw new RuntimeException('Invalid or unreadable RSA private key.');
        }

        $now = time();
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $tokenPayload = array_merge($payload, [
            'iss' => config('name', "Yulio's Ice Ordering"),
            'iat' => $now,
            'exp' => $now + $ttl,
        ]);

        $encodedHeader  = self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $encodedPayload = self::base64UrlEncode(json_encode($tokenPayload, JSON_UNESCAPED_SLASHES));
        $dataToSign     = $encodedHeader . '.' . $encodedPayload;

        $signature = '';
        $success = openssl_sign($dataToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (!$success) {
            throw new RuntimeException('Failed to sign asymmetric token: ' . openssl_error_string());
        }

        return $dataToSign . '.' . self::base64UrlEncode($signature);
    }

    /**
     * Verify an RS256 JWT using ONLY the RSA Public Key.
     *
     * @param string $jwt The JWT token string
     * @return array|null Returns decoded payload on success, or null if signature is invalid / expired.
     */
    public static function verifyToken(string $jwt): ?array
    {
        $parts = explode('.', trim($jwt));
        if (count($parts) !== 3) {
            return null;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        $header = json_decode(self::base64UrlDecode($encodedHeader), true);
        if (!$header || ($header['alg'] ?? '') !== 'RS256') {
            return null; // Algorithm must be RS256
        }

        $payload = json_decode(self::base64UrlDecode($encodedPayload), true);
        if (!$payload || !is_array($payload)) {
            return null;
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null; // Token expired
        }

        $pubPath = self::publicKeyPath();
        if (!file_exists($pubPath)) {
            return null;
        }

        $publicKey = openssl_pkey_get_public(file_get_contents($pubPath));
        if (!$publicKey) {
            return null;
        }

        $dataToVerify = $encodedHeader . '.' . $encodedPayload;
        $signature    = self::base64UrlDecode($encodedSignature);

        $result = openssl_verify($dataToVerify, $signature, $publicKey, OPENSSL_ALGO_SHA256);

        return ($result === 1) ? $payload : null;
    }

    /**
     * Get the public key string (for clients or Python AI service to verify signatures).
     */
    public static function getPublicKey(): ?string
    {
        $pubPath = self::publicKeyPath();
        return file_exists($pubPath) ? file_get_contents($pubPath) : null;
    }
}

