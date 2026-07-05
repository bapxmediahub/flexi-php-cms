<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$port = 8017;
$base = 'http://127.0.0.1:' . $port;
$process = null;
$pipes = [];

$probe = http_request($base . '/');
if ($probe['status'] !== 200 || !str_contains($probe['body'], 'Flexi Feet')) {
    $port = available_port(8027, 8050);
    $base = 'http://127.0.0.1:' . $port;
    $descriptor = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $process = proc_open(escapeshellarg(PHP_BINARY) . ' -S 127.0.0.1:' . $port, $descriptor, $pipes, $root);
    if (!is_resource($process)) {
        fwrite(STDERR, "Unable to start local PHP server.\n");
        exit(1);
    }
    wait_for_server($base);
}

$checks = [
    '/' => 200,
    '/blog.php' => 200,
    '/sitemap.php' => 200,
    '/llms.txt' => 200,
    '/map.mmd' => 200,
    '/docs/systematic-map.mmd' => 200,
    '/admin/login.php' => 200,
];

$failures = [];
foreach ($checks as $path => $expected) {
    $response = http_request($base . $path);
    echo "{$response['status']} GET {$path}\n";
    if ($response['status'] !== $expected) {
        $failures[] = "GET {$path} expected {$expected}, got {$response['status']}";
    }
}

$homepage = http_request($base . '/');
foreach ([
    'data-support-bot' => 'support bot',
    'data-shorts-player' => 'in-site Shorts player',
    'hammer-toes-reference.png' => 'Hammer Toes image',
    'poor-circulation-reference.png' => 'Poor Circulation image',
] as $needle => $label) {
    if (!str_contains($homepage['body'], $needle)) {
        $failures[] = "Homepage missing {$label}";
    }
}

if (is_resource($process)) {
    foreach ($pipes as $pipe) {
        if (is_resource($pipe)) {
            fclose($pipe);
        }
    }
    proc_terminate($process);
    proc_close($process);
}

if ($failures) {
    fwrite(STDERR, implode("\n", $failures) . "\n");
    exit(1);
}

echo "PASS local smoke\n";

function wait_for_server(string $base): void
{
    $deadline = microtime(true) + 8;
    do {
        $response = http_request($base . '/');
        if (($response['status'] ?? 0) > 0) {
            return;
        }
        usleep(100000);
    } while (microtime(true) < $deadline);
    throw new RuntimeException('Local PHP server did not start.');
}

function available_port(int $start, int $end): int
{
    for ($port = $start; $port <= $end; $port++) {
        $socket = @stream_socket_server('tcp://127.0.0.1:' . $port, $errno, $errstr);
        if ($socket) {
            fclose($socket);
            return $port;
        }
    }
    throw new RuntimeException('No free local smoke-test port found.');
}

function http_request(string $url): array
{
    $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 8]]);
    $body = @file_get_contents($url, false, $context);
    $headers = $http_response_header ?? [];
    preg_match('/\s(\d{3})\s/', $headers[0] ?? '', $matches);
    return [
        'status' => (int) ($matches[1] ?? 0),
        'body' => is_string($body) ? $body : '',
        'headers' => $headers,
    ];
}
