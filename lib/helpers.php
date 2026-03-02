<?php

declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function genToken(int $length = 32): string
{
    return bin2hex(random_bytes((int)($length / 2)));
}

function genActivityId(): string
{
    return 'RG' . date('Y') . random_int(1000, 9999);
}

function qrImageUrl(string $text): string
{
    return 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . urlencode($text);
}

function parseState(string $state): array
{
    $parts = explode('|', $state);
    return [
        'type' => $parts[0] ?? '',
        'activity_id' => $parts[1] ?? '',
        'sign_index' => isset($parts[2]) ? (int)$parts[2] : null,
    ];
}
