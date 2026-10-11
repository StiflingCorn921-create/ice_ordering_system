<?php
/**
 * Small input validation helpers.
 * Each validator returns an error message string, or null when the value is valid.
 */

function v_required($value, string $label): ?string
{
    return (trim((string) ($value ?? '')) === '') ? "$label is required." : null;
}

function v_max_length($value, int $max, string $label): ?string
{
    return (mb_strlen((string) $value) > $max) ? "$label must be at most $max characters." : null;
}

/** Philippine mobile numbers: 09XXXXXXXXX or +639XXXXXXXXX */
function v_phone($value, string $label = 'Contact number'): ?string
{
    $digits = preg_replace('/[\s\-]/', '', (string) $value);
    return preg_match('/^(09|\+639)\d{9}$/', $digits) ? null : "$label must be a valid mobile number (e.g. 09171234567).";
}

function v_in($value, array $allowed, string $label): ?string
{
    return in_array($value, $allowed, true) ? null : "$label is invalid.";
}

/** Normalize phone to 09XXXXXXXXX format so the same customer is always matched. */
function normalize_phone(string $phone): string
{
    $digits = preg_replace('/[\s\-]/', '', $phone);
    if (strpos($digits, '+63') === 0) {
        $digits = '0' . substr($digits, 3);
    }
    return $digits;
}

/** Drop null results and return the list of error messages. */
function collect_errors(array $results): array
{
    return array_values(array_filter($results, fn($r) => $r !== null));
}

