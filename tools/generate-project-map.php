<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$docsDir = $root . '/docs';
if (!is_dir($docsDir)) {
    mkdir($docsDir, 0755, true);
}

function read_file_safe(string $path): string
{
    $data = @file_get_contents($path);
    return is_string($data) ? $data : '';
}

function rel(string $root, string $path): string
{
    return str_replace('\\', '/', substr($path, strlen($root) + 1));
}

function node_id(string $prefix, string $name): string
{
    return $prefix . '_' . substr(hash('sha1', $name), 0, 12);
}

function esc(string $value): string
{
    return str_replace(["\\", '"', "\r", "\n"], ['/', '\\"', '', '\\n'], $value);
}

function php_files(string $root): array
{
    $files = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }
        $path = $file->getPathname();
        if (str_contains($path, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR)) {
            continue;
        }
        $files[] = rel($root, $path);
    }
    sort($files, SORT_NATURAL | SORT_FLAG_CASE);
    return $files;
}

function list_files(string $root, array $extensions, array $skipDirs = ['.git']): array
{
    $files = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            function (SplFileInfo $file) use ($skipDirs): bool {
                return !$file->isDir() || !in_array($file->getFilename(), $skipDirs, true);
            }
        )
    );
    foreach ($it as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }
        $ext = strtolower($file->getExtension());
        if (in_array($ext, $extensions, true)) {
            $files[] = rel($root, $file->getPathname());
        }
    }
    sort($files, SORT_NATURAL | SORT_FLAG_CASE);
    return $files;
}

function route_for_php(string $file): ?array
{
    if (str_starts_with($file, 'includes/') || str_starts_with($file, 'tests/') || str_starts_with($file, 'tools/') || str_starts_with($file, 'scripts/') || str_contains($file, '/partials/')) {
        return null;
    }
    $method = str_starts_with($file, 'api/') || $file === 'mcp.php' ? 'POST/GET' : 'GET';
    if ($file === 'index.php') {
        return ['GET /', $file, 'public'];
    }
    if (str_starts_with($file, 'admin/')) {
        $route = '/admin/' . basename($file, '.php');
        if (basename($file) === 'index.php') {
            $route = '/admin';
        }
        return [$method . ' ' . $route, $file, 'admin'];
    }
    if (str_starts_with($file, 'api/')) {
        return [$method . ' /' . $file, $file, 'api'];
    }
    return [$method . ' /' . $file, $file, 'public'];
}

function extract_functions(string $root, string $file): array
{
    $text = read_file_safe($root . '/' . $file);
    preg_match_all('/function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $text, $matches);
    $names = $matches[1] ?? [];
    sort($names, SORT_NATURAL | SORT_FLAG_CASE);
    return $names;
}

function extract_requires(string $root, string $file): array
{
    $text = read_file_safe($root . '/' . $file);
    preg_match_all('/require(?:_once)?\s+__DIR__\s*\.\s*[\'"]([^\'"]+)[\'"]/', $text, $matches);
    $out = [];
    foreach ($matches[1] ?? [] as $target) {
        $base = dirname($file);
        $candidate = $base . '/' . $target;
        $parts = [];
        foreach (explode('/', str_replace('\\', '/', $candidate)) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($parts);
                continue;
            }
            $parts[] = $part;
        }
        $out[] = implode('/', $parts);
    }
    return array_values(array_unique($out));
}

function extract_fetches(string $root, string $file): array
{
    $text = read_file_safe($root . '/' . $file);
    preg_match_all('/fetch\([`\'"]([^`\'"]+)[`\'"]/', $text, $matches);
    return array_values(array_unique($matches[1] ?? []));
}

function extract_actions(string $root, string $file): array
{
    $text = read_file_safe($root . '/' . $file);
    $actions = [];
    if (preg_match_all('/\$action\s*={1,3}\s*[\'"]([A-Za-z0-9_-]+)[\'"]/', $text, $matches)) {
        foreach ($matches[1] as $action) {
            $actions[] = $action;
        }
    }
    if (preg_match_all('/name=["\']action["\'][^>]*value=["\']([^"\']+)["\']/', $text, $matches)) {
        foreach ($matches[1] as $action) {
            $actions[] = $action;
        }
    }
    sort($actions, SORT_NATURAL | SORT_FLAG_CASE);
    return array_values(array_unique($actions));
}

function extract_mcp_tools(string $root): array
{
    $text = read_file_safe($root . '/mcp.php');
    $tools = [];
    if (preg_match_all("/'name'\s*=>\s*'([^']+)'[\s\S]{0,1200}?'inputSchema'\s*=>\s*\[([\s\S]*?)\]\s*,?\s*\]/", $text, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            preg_match_all("/'([A-Za-z0-9_]+)'\s*=>\s*\['type'\s*=>\s*'([^']+)'/", $match[2], $props, PREG_SET_ORDER);
            $properties = [];
            foreach ($props as $prop) {
                if (!in_array($prop[1], ['type', 'required', 'properties'], true)) {
                    $properties[] = $prop[1] . ':' . $prop[2];
                }
            }
            $tools[] = [
                'name' => $match[1],
                'properties' => array_values(array_unique($properties)),
            ];
        }
    }
    return $tools;
}

function duplicate_groups(array $files, callable $keyFn): array
{
    $groups = [];
    foreach ($files as $file) {
        $key = $keyFn($file);
        if ($key === '') {
            continue;
        }
        $groups[$key][] = $file;
    }
    return array_filter($groups, fn($items) => count($items) > 1);
}

function empty_directories(string $root): array
{
    $directories = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            function (SplFileInfo $file): bool {
                return !$file->isDir() || $file->getFilename() !== '.git';
            }
        ),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $file) {
        if (!$file instanceof SplFileInfo || !$file->isDir()) {
            continue;
        }
        $items = @scandir($file->getPathname());
        if (is_array($items) && count(array_diff($items, ['.', '..'])) === 0) {
            $directories[] = rel($root, $file->getPathname());
        }
    }
    sort($directories, SORT_NATURAL | SORT_FLAG_CASE);
    return $directories;
}

function storage_constants(string $root): array
{
    $text = read_file_safe($root . '/includes/config.php');
    preg_match_all("/define\\('([A-Z0-9_]+_FILE)',\\s*([^;]+)\\);/", $text, $matches, PREG_SET_ORDER);
    $items = [];
    foreach ($matches as $match) {
        $items[$match[1]] = trim($match[2]);
    }
    return $items;
}

$phpFiles = php_files($root);
$routes = [];
$handlers = [];
foreach ($phpFiles as $file) {
    $route = route_for_php($file);
    if ($route !== null) {
        $routes[] = $route;
        $handlers[$file] = true;
    }
}

$coreFunctions = extract_functions($root, 'includes/functions.php');
$allFunctionsByFile = [];
$functionOwners = [];
foreach ($phpFiles as $file) {
    $functions = extract_functions($root, $file);
    $allFunctionsByFile[$file] = $functions;
    foreach ($functions as $fn) {
        $functionOwners[$fn][] = $file;
    }
}
$adminFunctions = [];
$apiFunctions = [];
foreach ($phpFiles as $file) {
    if (str_starts_with($file, 'admin/') || str_starts_with($file, 'api/') || $file === 'mcp.php') {
        foreach (extract_functions($root, $file) as $fn) {
            if (str_starts_with($file, 'admin/')) {
                $adminFunctions[] = $file . '::' . $fn;
            } else {
                $apiFunctions[] = $file . '::' . $fn;
            }
        }
    }
}

$storageFiles = list_files($root, ['json', 'jsonl', 'csv']);
$assetFiles = list_files($root, ['css', 'js', 'png', 'jpg', 'jpeg', 'webp', 'svg', 'gif']);
$docFiles = list_files($root, ['md', 'txt', 'mmd']);
$toolFiles = array_values(array_filter($phpFiles, fn($f) => str_starts_with($f, 'tools/') || str_starts_with($f, 'scripts/') || str_starts_with($f, 'tests/')));
$actionSchemas = [];
foreach ($phpFiles as $file) {
    $actions = extract_actions($root, $file);
    if ($actions) {
        $actionSchemas[$file] = $actions;
    }
}
$mcpTools = extract_mcp_tools($root);
$duplicateBasenames = duplicate_groups($phpFiles, fn($file) => basename($file));
$duplicateFunctions = array_filter($functionOwners, fn($owners) => count($owners) > 1);
$emptyDirectories = empty_directories($root);

$gaps = [];
if (is_file($root . '/storage/instagram-reels.json')) {
    $gaps[] = ['low', 'legacy_storage_name', 'storage/instagram-reels.json stores YouTube Shorts data under a legacy filename'];
}
if (str_contains(read_file_safe($root . '/README.md'), 'Instagram Reels')) {
    $gaps[] = ['medium', 'stale_docs_copy', 'Docs still contain older Instagram/support-model wording'];
}
if (!is_file($root . '/tools/validate-project-map.php')) {
    $gaps[] = ['high', 'missing_validator', 'tools/validate-project-map.php missing'];
}
foreach ($emptyDirectories as $directory) {
    if (!str_starts_with($directory, '.git/')) {
        $gaps[] = ['low', 'empty_directory', $directory . ' is empty and can be removed when not locked by the filesystem'];
    }
}
foreach ($duplicateBasenames as $basename => $owners) {
    if (in_array($basename, ['index.php'], true)) {
        $gaps[] = ['info', 'duplicate_basename', $basename . ' appears in ' . implode(', ', $owners)];
    }
}
foreach ($duplicateFunctions as $fn => $owners) {
    $gaps[] = ['info', 'duplicate_function_name', $fn . '() appears in ' . implode(', ', $owners)];
}

$summary = [
    'routes' => count($routes),
    'handlers' => count($handlers),
    'core_functions' => count($coreFunctions),
    'all_functions' => array_sum(array_map('count', $allFunctionsByFile)),
    'admin_functions' => count($adminFunctions),
    'api_functions' => count($apiFunctions),
    'action_schemas' => count($actionSchemas),
    'mcp_tools' => count($mcpTools),
    'storage_files' => count($storageFiles),
    'asset_files' => count($assetFiles),
    'doc_files' => count($docFiles),
    'tool_files' => count($toolFiles),
    'duplicate_basenames' => count($duplicateBasenames),
    'duplicate_functions' => count($duplicateFunctions),
    'empty_directories' => count($emptyDirectories),
    'gap_count' => count($gaps),
];

$lines = [];
$lines[] = 'flowchart LR';
$lines[] = 'classDef route fill:#e3f2fd,stroke:#1976d2,color:#0d47a1';
$lines[] = 'classDef handler fill:#fff3e0,stroke:#f57c00,color:#e65100';
$lines[] = 'classDef service fill:#e8f5e9,stroke:#388e3c,color:#1b5e20';
$lines[] = 'classDef storage fill:#fff8e1,stroke:#ff8f00,color:#e65100';
$lines[] = 'classDef schema fill:#e0f7fa,stroke:#00838f,color:#006064';
$lines[] = 'classDef asset fill:#f3e5f5,stroke:#7b1fa2,color:#4a148c';
$lines[] = 'classDef doc fill:#ede7f6,stroke:#5e35b1,color:#311b92';
$lines[] = 'classDef tool fill:#e0f7fa,stroke:#00838f,color:#006064';
$lines[] = 'classDef gap fill:#ffebee,stroke:#c62828,color:#b71c1c';
$lines[] = '%% Do not edit by hand. Regenerate with php tools/generate-project-map.php.';
$lines[] = '%% Summary: ' . json_encode($summary, JSON_UNESCAPED_SLASHES);

$routeNodes = [];
$handlerNodes = [];

$routeGroups = ['public' => 'PUBLIC routes', 'admin' => 'ADMIN routes', 'api' => 'API / MCP routes'];
foreach ($routeGroups as $kind => $label) {
    $lines[] = 'subgraph ROUTE_' . strtoupper($kind) . '["' . esc($label) . '"]';
    foreach ($routes as [$route, $file, $routeKind]) {
        if ($routeKind !== $kind) {
            continue;
        }
        $id = node_id('r', $route);
        $routeNodes[$route] = $id;
        $lines[] = $id . '["' . esc($route) . '"]:::route';
    }
    $lines[] = 'end';
}

$lines[] = 'subgraph HANDLERS["PHP handlers"]';
foreach (array_keys($handlers) as $file) {
    $id = node_id('h', $file);
    $handlerNodes[$file] = $id;
    $lines[] = $id . '["' . esc($file) . '"]:::handler';
}
$lines[] = 'end';

$lines[] = 'subgraph CORE["Core services / functions"]';
$mappedFunctions = $coreFunctions;
foreach ($mappedFunctions as $fn) {
    $lines[] = node_id('s', $fn) . '["' . esc($fn) . '()"]:::service';
}
$lines[] = 'end';

$lines[] = 'subgraph SCHEMA["Actions, MCP tools, and schemas"]';
foreach ($actionSchemas as $file => $actions) {
    $lines[] = node_id('schema', 'actions:' . $file) . '["' . esc($file . ' actions: ' . implode(', ', $actions)) . '"]:::schema';
}
foreach ($mcpTools as $tool) {
    $props = $tool['properties'] ? ' / ' . implode(', ', $tool['properties']) : '';
    $lines[] = node_id('schema', 'mcp:' . $tool['name']) . '["MCP tool: ' . esc($tool['name'] . $props) . '"]:::schema';
}
foreach ($allFunctionsByFile as $file => $functions) {
    if (!$functions || $file === 'includes/functions.php') {
        continue;
    }
    $lines[] = node_id('schema', 'functions:' . $file) . '["' . esc($file . ' functions: ' . implode(', ', $functions)) . '"]:::schema';
}
$lines[] = 'end';

$lines[] = 'subgraph STORAGE["Storage"]';
foreach ($storageFiles as $file) {
    $lines[] = node_id('d', $file) . '["' . esc($file) . '"]:::storage';
}
$lines[] = 'end';

$lines[] = 'subgraph ASSETS["Assets"]';
foreach ($assetFiles as $file) {
    $label = strlen($file) > 70 ? substr($file, 0, 67) . '...' : $file;
    $lines[] = node_id('a', $file) . '["' . esc($label) . '"]:::asset';
}
$lines[] = 'end';

$lines[] = 'subgraph DOCS["Docs and agent grounding"]';
foreach ($docFiles as $file) {
    $lines[] = node_id('doc', $file) . '["' . esc($file) . '"]:::doc';
}
$lines[] = 'end';

$lines[] = 'subgraph TOOLS["Tools and tests"]';
foreach ($toolFiles as $file) {
    $lines[] = node_id('t', $file) . '["' . esc($file) . '"]:::tool';
}
$lines[] = 'end';

if ($gaps) {
    $lines[] = 'subgraph GAPS["Gaps and follow-ups"]';
    foreach ($gaps as $index => [$severity, $type, $text]) {
        $lines[] = 'g' . $index . '["[' . esc($severity) . '] ' . esc($type . ': ' . $text) . '"]:::gap';
    }
    $lines[] = 'end';
}

foreach ($routes as [$route, $file]) {
    $lines[] = $routeNodes[$route] . ' --> ' . $handlerNodes[$file];
}

foreach (array_keys($handlers) as $file) {
    foreach (extract_requires($root, $file) as $required) {
        if ($required === 'includes/functions.php') {
            $lines[] = $handlerNodes[$file] . ' --> ' . node_id('s', 'core_functions');
        }
    }
    foreach (extract_fetches($root, $file) as $fetch) {
        $target = trim($fetch, './');
        $target = str_replace('../', '', $target);
        foreach ($routes as [$route, $routeFile]) {
            if ($target === $routeFile) {
                $lines[] = $handlerNodes[$file] . ' -.fetches.-> ' . $handlerNodes[$routeFile];
            }
        }
    }
}

$lines[] = node_id('s', 'core_functions') . '["includes/functions.php core"]:::service';
foreach ($mappedFunctions as $fn) {
    $lines[] = node_id('s', 'core_functions') . ' --> ' . node_id('s', $fn);
}
foreach ($actionSchemas as $file => $actions) {
    if (isset($handlerNodes[$file])) {
        $lines[] = $handlerNodes[$file] . ' -.actions.-> ' . node_id('schema', 'actions:' . $file);
    }
}
foreach ($mcpTools as $tool) {
    $lines[] = $handlerNodes['mcp.php'] . ' -.tool-schema.-> ' . node_id('schema', 'mcp:' . $tool['name']);
}
foreach ($allFunctionsByFile as $file => $functions) {
    if (!$functions || $file === 'includes/functions.php' || !isset($handlerNodes[$file])) {
        continue;
    }
    $lines[] = $handlerNodes[$file] . ' -.defines.-> ' . node_id('schema', 'functions:' . $file);
}
foreach ($storageFiles as $file) {
    if (str_starts_with($file, 'storage/')) {
        $lines[] = node_id('s', 'core_functions') . ' --> ' . node_id('d', $file);
    }
}
foreach (['assets/app.js', 'assets/styles.css', 'admin/flexi-admin.css'] as $asset) {
    if (in_array($asset, $assetFiles, true)) {
        $lines[] = node_id('h', 'index.php') . ' -.uses.-> ' . node_id('a', $asset);
    }
}
foreach (['AGENTS.md', 'index.md', 'llms.txt', 'docs/systematic-map.mmd'] as $doc) {
    if (in_array($doc, $docFiles, true)) {
        $lines[] = node_id('doc', 'AGENTS.md') . ' -.points-to.-> ' . node_id('doc', $doc);
    }
}
foreach ($gaps as $index => $gap) {
    $lines[] = 'g' . $index . ' -.tracked-by.-> ' . node_id('doc', 'docs/systematic-map.mmd');
}
if (in_array('tools/generate-project-map.php', $toolFiles, true)) {
    $lines[] = node_id('t', 'tools/generate-project-map.php') . ' -.regenerates.-> ' . node_id('doc', 'docs/systematic-map.mmd');
    $lines[] = node_id('t', 'tools/generate-project-map.php') . ' -.mirrors.-> ' . node_id('doc', 'map.mmd');
}
if (in_array('tools/validate-project-map.php', $toolFiles, true)) {
    $lines[] = node_id('t', 'tools/validate-project-map.php') . ' -.validates.-> ' . node_id('doc', 'docs/systematic-map.mmd');
}

$mmd = implode("\n", $lines) . "\n";
file_put_contents($root . '/docs/systematic-map.mmd', $mmd);
file_put_contents($root . '/map.mmd', $mmd);

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
echo "Wrote docs/systematic-map.mmd and map.mmd (" . strlen($mmd) . " bytes)\n";
