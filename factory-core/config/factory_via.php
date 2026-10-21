<?php

// The HML bootstrap stores the token in a private persistent file.
// Read the token only outside production and only when explicitly bootstrapped.
// A missing or malformed credential always fails closed in the controller.
$readToken = env('FACTORY_VIA_READ_TOKEN');
if ((! is_string($readToken) || strlen($readToken) < 32)
    && env('APP_ENV', 'production') !== 'production') {
    $file = storage_path('app/factory-via-read-token');
    if (is_file($file) && is_readable($file) && (fileperms($file) & 0077) === 0) {
        $readToken = trim((string) file_get_contents($file));
    }
}

return ['read_token' => $readToken];
