<?php
// Version and digest endpoint for deployment verification
$meta = include __DIR__ . '/version_meta.php';

$base = __DIR__;
$digests = [];
foreach (($meta['files'] ?? []) as $rel) {
    $path = $base . '/' . $rel;
    if (is_file($path)) {
        $digests[$rel] = hash_file('sha256', $path);
    } else {
        $digests[$rel] = null;
    }
}

// Combined digest over existing files only
$combined = substr(hash('sha256', implode('', array_filter($digests))), 0, 32);

$result = [
    'version' => $meta['version'] ?? 'unknown',
    'commit' => $meta['commit'] ?? 'unknown',
    'built_at' => $meta['built_at'] ?? null,
    'php_version' => PHP_VERSION,
    'digests' => $digests,
    'combined_digest' => $combined,
];

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
