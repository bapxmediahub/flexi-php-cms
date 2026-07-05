<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Accept, MCP-Protocol-Version');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'jsonrpc' => '2.0',
        'error' => ['code' => -32000, 'message' => 'Use POST JSON-RPC for this MCP endpoint.'],
    ]);
    exit;
}

$request = json_decode((string) file_get_contents('php://input'), true);
$id = $request['id'] ?? null;
$method = $request['method'] ?? '';
$params = $request['params'] ?? [];

function mcp_result($id, array $result): void
{
    echo json_encode(['jsonrpc' => '2.0', 'id' => $id, 'result' => $result], JSON_UNESCAPED_SLASHES);
    exit;
}

function mcp_error($id, int $code, string $message): void
{
    echo json_encode(['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]], JSON_UNESCAPED_SLASHES);
    exit;
}

if ($method === 'initialize') {
    mcp_result($id, [
        'protocolVersion' => '2025-06-18',
        'serverInfo' => ['name' => 'flexifeet-booking-mcp', 'version' => '1.0.0'],
        'capabilities' => ['tools' => new stdClass()],
    ]);
}

if ($method === 'tools/list') {
    mcp_result($id, [
        'tools' => [[
            'name' => 'get_available_slots',
            'description' => 'Return available Flexi Feet appointment times for one future date within the next 3 months. The available_slots array contains time strings only.',
            'inputSchema' => [
                'type' => 'object',
                'required' => ['preferred_date'],
                'properties' => [
                    'preferred_date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                ],
            ],
        ], [
            'name' => 'get_site_context',
            'description' => 'Return Flexi Feet services, product categories, common foot problems, public blog posts, and booking rules for grounded support agents.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'section' => [
                        'type' => 'string',
                        'description' => 'Optional: all, services, products, problems, blogs, booking.',
                    ],
                ],
            ],
        ], [
            'name' => 'book_appointment',
            'description' => 'Create a Flexi Feet appointment request for custom footwear, custom insoles, foot assessment, or follow-up after confirming an available slot.',
            'inputSchema' => [
                'type' => 'object',
                'required' => ['name', 'phone', 'email', 'preferred_date', 'preferred_time', 'visit_type'],
                'properties' => [
                    'name' => ['type' => 'string'],
                    'phone' => ['type' => 'string'],
                    'email' => ['type' => 'string'],
                    'preferred_date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                    'preferred_time' => ['type' => 'string', 'description' => 'HH:MM'],
                    'visit_type' => ['type' => 'string'],
                    'notes' => ['type' => 'string'],
                ],
            ],
        ]],
    ]);
}

if ($method === 'tools/call') {
    $name = $params['name'] ?? '';
    $arguments = $params['arguments'] ?? [];
    if (!is_array($arguments)) {
        mcp_error($id, -32602, 'Invalid arguments.');
    }
    if ($name === 'get_available_slots') {
        $date = normalize_text($arguments['preferred_date'] ?? '', 20);
        if ($date === '') {
            mcp_error($id, -32602, 'preferred_date is required.');
        }
        $dateStatus = appointment_date_status($date);
        if (!$dateStatus['ok']) {
            mcp_error($id, -32602, $dateStatus['message']);
        }
        $summary = appointment_availability_summary($date);
        mcp_result($id, [
            'content' => [[
                'type' => 'text',
                'text' => 'Available times for ' . $date . ': ' . implode(', ', $summary['available_slots']),
            ]],
            'structuredContent' => [
                'preferred_date' => $date,
                'date_status' => $summary['date_status'],
                'available_slots' => $summary['available_slots'],
                'booked_slots' => $summary['booked_slots'],
                'slot_format' => 'HH:MM',
                'range' => ['from' => date('Y-m-d'), 'to' => date('Y-m-d', strtotime('+3 months'))],
                'closed_dates' => flexifeet_holiday_dates(),
            ],
        ]);
    }
    if ($name === 'get_site_context') {
        $section = strtolower(normalize_text($arguments['section'] ?? 'all', 40));
        $blogs = array_map(static function (array $post): array {
            return [
                'title' => $post['title'] ?? '',
                'slug' => $post['slug'] ?? '',
                'url' => absolute_url('blog-post.php?slug=' . ($post['slug'] ?? '')),
                'excerpt' => $post['excerpt'] ?? '',
                'status' => $post['status'] ?? '',
            ];
        }, read_blog_posts(true));
        $context = [
            'business' => [
                'name' => BUSINESS_NAME,
                'phone' => BUSINESS_PHONE,
                'email' => BUSINESS_EMAIL,
                'address' => BUSINESS_ADDRESS,
                'site_url' => SITE_URL,
            ],
            'services' => [
                'Custom diabetic shoes',
                'Orthopaedic footwear',
                'Custom offload insoles',
                'Flat feet insoles',
                'Diabetic and compression socks',
                '3D foot scanning',
                'Pressure assessment',
                'Footwear fitting and follow-up',
            ],
            'products' => flexifeet_product_topics(),
            'problems' => flexifeet_problem_topics(),
            'blogs' => $blogs,
            'booking' => [
                'date_range' => ['from' => date('Y-m-d'), 'to' => date('Y-m-d', strtotime('+3 months'))],
                'closed_dates' => flexifeet_holiday_dates(),
                'required_fields' => ['name', 'phone', 'email', 'preferred_date', 'preferred_time', 'visit_type'],
                'visit_types' => ['Foot Assessment', 'Custom Shoes / Footwear Fitting', 'Customised Insole Assessment', 'Pressure Sensor Scan', 'Follow-up'],
            ],
        ];
        $allowed = ['services', 'products', 'problems', 'blogs', 'booking'];
        $payload = in_array($section, $allowed, true) ? [$section => $context[$section]] : $context;
        mcp_result($id, [
            'content' => [[
                'type' => 'text',
                'text' => 'Flexi Feet context returned for section: ' . ($section ?: 'all'),
            ]],
            'structuredContent' => $payload,
        ]);
    }
    if ($name !== 'book_appointment') {
        mcp_error($id, -32602, 'Unknown tool or invalid arguments.');
    }
    $payload = [
        'name' => normalize_text($arguments['name'] ?? '', 100),
        'phone' => normalize_text($arguments['phone'] ?? '', 40),
        'email' => normalize_text($arguments['email'] ?? '', 100),
        'preferred_date' => normalize_text($arguments['preferred_date'] ?? '', 20),
        'preferred_time' => normalize_text($arguments['preferred_time'] ?? '', 20),
        'visit_type' => normalize_text($arguments['visit_type'] ?? '', 100),
        'notes' => normalize_text($arguments['notes'] ?? 'Booked through remote MCP endpoint.', 1000),
    ];
    if ($payload['name'] === '' || $payload['phone'] === '' || $payload['email'] === '' || $payload['preferred_date'] === '' || $payload['preferred_time'] === '') {
        mcp_error($id, -32602, 'Missing required booking fields.');
    }
    if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
        mcp_error($id, -32602, 'Invalid email address.');
    }
    $dateStatus = appointment_date_status($payload['preferred_date']);
    if (!$dateStatus['ok']) {
        mcp_error($id, -32602, $dateStatus['message']);
    }
    $availability = appointment_availability_summary($payload['preferred_date'], $payload['preferred_time']);
    if (!$availability['open']) {
        mcp_error($id, -32602, 'Requested date is outside booking hours. Use get_available_slots for recommendations.');
    }
    if (!$availability['preferred_available']) {
        mcp_error($id, -32602, 'Requested time is already booked or unavailable. Use get_available_slots before booking.');
    }
    $appointment = create_appointment($payload);
    $mail = notify_booking_emails($appointment);
    mcp_result($id, [
        'content' => [[
            'type' => 'text',
            'text' => 'Appointment request created: ' . $appointment['id'],
        ]],
        'structuredContent' => ['appointment_id' => $appointment['id'], 'mail' => $mail],
    ]);
}

mcp_error($id, -32601, 'Method not found.');
