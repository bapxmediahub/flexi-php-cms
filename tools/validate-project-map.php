<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$map = $root . '/docs/systematic-map.mmd';
$rootMap = $root . '/map.mmd';
$generator = $root . '/tools/generate-project-map.php';

if (!is_file($map)) {
    fwrite(STDERR, "Missing docs/systematic-map.mmd\n");
    exit(1);
}
if (!is_file($generator)) {
    fwrite(STDERR, "Missing tools/generate-project-map.php\n");
    exit(1);
}

ob_start();
require $generator;
ob_end_clean();
$command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($generator);
exec($command . ' 2>&1', $firstOutput, $firstCode);
if ($firstCode !== 0) {
    fwrite(STDERR, "Project map generator failed.\n" . implode("\n", $firstOutput) . "\n");
    exit(1);
}
$first = (string) file_get_contents($map);
$rootFirst = is_file($rootMap) ? (string) file_get_contents($rootMap) : '';
exec($command . ' 2>&1', $secondOutput, $secondCode);
if ($secondCode !== 0) {
    fwrite(STDERR, "Project map generator failed on determinism pass.\n" . implode("\n", $secondOutput) . "\n");
    exit(1);
}
$after = (string) file_get_contents($map);
$rootAfter = is_file($rootMap) ? (string) file_get_contents($rootMap) : '';

$errors = [];
if ($first !== $after) {
    $errors[] = 'docs/systematic-map.mmd generation is not deterministic.';
}
if ($after !== $rootAfter) {
    $errors[] = 'map.mmd must mirror docs/systematic-map.mmd.';
}
if (!str_starts_with($after, 'flowchart LR')) {
    $errors[] = 'Map must start with flowchart LR.';
}
if (!str_contains($after, '%% Do not edit by hand. Regenerate with php tools/generate-project-map.php.')) {
    $errors[] = 'Map must contain generated-file warning.';
}
if (!preg_match('/%% Summary: \{.*"routes":\d+.*"gap_count":\d+.*\}/', $after)) {
    $errors[] = 'Map must contain deterministic JSON summary metadata.';
}

if ($errors) {
    fwrite(STDERR, implode("\n", $errors) . "\n");
    file_put_contents($map, $first);
    file_put_contents($rootMap, $rootFirst);
    exit(1);
}

echo "PASS project map validation\n";
