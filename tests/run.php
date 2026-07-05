<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$failures = [];

function run_tool_or_fail(string $script, string $label): void
{
    global $failures;
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../' . $script);
    exec($command . ' 2>&1', $output, $code);
    if ($code !== 0) {
        $failures[] = $label . " failed.\n" . implode("\n", $output);
    }
}

function assert_true(bool $condition, string $message): void
{
    global $failures;
    if (!$condition) {
        $failures[] = $message;
    }
}

function assert_same(string $expected, string $actual, string $message): void
{
    global $failures;
    if ($expected !== $actual) {
        $failures[] = $message . "\nExpected: {$expected}\nActual:   {$actual}";
    }
}

function assert_contains(string $needle, string $haystack, string $message): void
{
    global $failures;
    if (strpos($haystack, $needle) === false) {
        $failures[] = $message . "\nMissing: {$needle}\nHTML: {$haystack}";
    }
}

run_tool_or_fail('tools/generate-project-map.php', 'Project map generation');
run_tool_or_fail('tools/validate-project-map.php', 'Project map validation');

assert_true(!is_social_reel_url('https://www.instagram.com/reel/abc'), 'Instagram Reels should no longer be accepted for the Shorts section.');
assert_true(!is_social_reel_url('https://m.instagram.com/reel/abc'), 'Mobile Instagram Reels should no longer be accepted for the Shorts section.');
assert_same('', canonical_reel_url('https://www.instagram.com/reel/DFzlzIOqvKv/?utm_source=ig_web_button_share_sheet'), 'Instagram URLs should not be canonicalized into Shorts.');
assert_same('', reel_thumbnail_from_url('https://www.instagram.com/reel/ABC123xyz/'), 'Instagram thumbnails should not be generated for the Shorts section.');
assert_same('https://www.youtube.com/shorts/K8ZBQpI09VA', canonical_reel_url('https://www.youtube.com/shorts/K8ZBQpI09VA?feature=share'), 'YouTube Shorts tracking URLs should be saved as clean Shorts URLs.');
assert_same('https://i.ytimg.com/vi/K8ZBQpI09VA/hqdefault.jpg', reel_thumbnail_from_url('https://www.youtube.com/shorts/K8ZBQpI09VA'), 'YouTube Shorts thumbnail should be derived from the video id.');
assert_same('YouTube Short K8ZBQpI09VA', reel_title_from_url('https://www.youtube.com/shorts/K8ZBQpI09VA', 4), 'YouTube Short title should be automatic.');

assert_same('https://cdn.example.com/image.jpg', admin_media_src('https://cdn.example.com/image.jpg'), 'Absolute admin media URLs should not be prefixed.');
assert_same('../assets/uploads/image.jpg', admin_media_src('assets/uploads/image.jpg'), 'Relative admin media URLs should keep the admin parent prefix.');

ob_start();
render_agent_discovery_tags();
$agentTags = ob_get_clean();
assert_contains('href="https://flexifeet.net/llms.txt"', $agentTags, 'Agent discovery tags should point agents to llms.txt.');
assert_contains('href="https://flexifeet.net/mcp.php"', $agentTags, 'Agent discovery tags should expose the MCP endpoint.');

$markdown = <<<MD
# Foot care guide

Intro with **bold care**, *gentle support*, [booking](https://flexifeet.net/#booking), and `scan data`.

## Checklist

- Diabetic shoes
- Offload insoles

1. Book a fitting
2. Bring reports

> Helpful note

![Pressure scan](assets/images/foot-scanning-monitor.png)
MD;

$html = render_post_content($markdown);
assert_contains('<h1>Foot care guide</h1>', $html, 'Markdown H1 should render.');
assert_contains('<strong>bold care</strong>', $html, 'Markdown bold should render.');
assert_contains('<em>gentle support</em>', $html, 'Markdown emphasis should render.');
assert_contains('<a href="https://flexifeet.net/#booking" target="_blank" rel="noopener">booking</a>', $html, 'Markdown links should render safely.');
assert_contains('<code>scan data</code>', $html, 'Inline code should render.');
assert_contains('<ul><li>Diabetic shoes</li><li>Offload insoles</li></ul>', $html, 'Unordered lists should render.');
assert_contains('<ol><li>Book a fitting</li><li>Bring reports</li></ol>', $html, 'Ordered lists should render.');
assert_contains('<blockquote><p>Helpful note</p></blockquote>', $html, 'Blockquotes should render.');
assert_contains('<figure class="blog-inline-image"><img src="assets/images/foot-scanning-monitor.png" alt="Pressure scan"></figure>', $html, 'Markdown images should use site image styling.');

$invalidMailResult = notify_booking_emails([
    'id' => 'QA-MAIL-1',
    'name' => 'QA Mail',
    'email' => '',
]);
assert_same('skipped_mailbox_forwarding', (string) $invalidMailResult['owner'], 'Booking notifier should not send a separate owner email.');
assert_true($invalidMailResult['user'] === false, 'Booking notifier should not send a user email when no valid user address exists.');

$groundingContext = flexifeet_support_grounding_context('Do you provide diabetic shoes and 3D foot scanning in Sentul?', ['reply' => '']);
assert_contains('custom diabetic shoes', $groundingContext, 'Gemma support should be grounded with project service details.');
assert_contains(BUSINESS_PHONE, $groundingContext, 'Gemma support grounding should include contact details.');
$snippet = flexifeet_support_snippet('Malaysia provides custom footwear and orthopaedic support for diabetic foot care.', 12);
assert_true(str_ends_with($snippet, '.'), 'Support snippets should end cleanly.');
assert_true(strpos($snippet, 'Malaysi.') === false, 'Support snippets should not cut words in the middle.');
$semanticReply = support_bot_reply('Do you provide diabetic shoes and 3D foot scanning in Sentul?');
assert_same(GOOGLE_AI_MODEL, (string) ($semanticReply['model'] ?? ''), 'Support replies should use the configured Google Gemma model.');
assert_same('service', (string) ($semanticReply['intent'] ?? ''), 'Service questions should be classified as service intent.');
assert_true(stripos((string) ($semanticReply['reply'] ?? ''), 'Flexi Feet') !== false, 'Support replies should mention Flexi Feet.');
assert_true((string) ($semanticReply['response_id'] ?? '') !== '', 'Support replies should include a response id for feedback.');
assert_true(!array_key_exists('raw', $semanticReply), 'Support replies should not expose remote AI raw output.');
$suggestionUrls = array_map(fn($item) => $item['url'] ?? '', $semanticReply['suggestions'] ?? []);
assert_true(count($suggestionUrls) === count(array_unique($suggestionUrls)), 'Support suggestions should not contain duplicate URLs.');
$greetingReply = support_bot_reply('hello');
assert_same('greeting', (string) ($greetingReply['intent'] ?? ''), 'Generic hello prompts should get a greeting/support entry response.');
assert_true(stripos((string) ($greetingReply['reply'] ?? ''), 'diabetic shoes') !== false, 'Greeting response should explain what the support agent can help with.');
assert_true(stripos((string) ($greetingReply['reply'] ?? ''), 'Maya') !== false, 'Greeting response should use the Maya persona name.');
$namedGreetingReply = support_bot_reply('hello Maya');
assert_same('greeting', (string) ($namedGreetingReply['intent'] ?? ''), 'Greeting Maya by name should not be swallowed by booking/general handling.');
$identityReply = support_bot_reply('what model you are?');
assert_same('identity', (string) ($identityReply['intent'] ?? ''), 'Support model identity prompts should stay in scope.');
assert_same(GOOGLE_AI_MODEL, (string) ($identityReply['model'] ?? ''), 'Support identity replies should report the configured Gemma model.');
assert_true(in_array((string) ($identityReply['engine'] ?? ''), ['google_ai_studio', 'local_grounded_fallback'], true), 'Support identity replies should use the Gemma path when configured or grounded fallback otherwise.');
assert_true(stripos((string) ($identityReply['reply'] ?? ''), 'Flexi Feet') !== false, 'Support identity replies should remain grounded in Flexi Feet.');
assert_true(stripos((string) ($identityReply['reply'] ?? ''), 'Maya') !== false, 'Support identity replies should use the Maya persona name.');
$bookingReply = support_bot_reply('I want to book an appointment');
assert_same('booking', (string) ($bookingReply['intent'] ?? ''), 'Booking prompts should start booking intent.');
assert_true((string) ($bookingReply['reply'] ?? '') !== '', 'Booking prompts should get a model-grounded reply.');
assert_true(stripos((string) ($bookingReply['reply'] ?? ''), 'I can help request a Flexi Feet appointment step by step') === false, 'Booking prompts should not use the old scripted placeholder.');
$malayReply = support_bot_reply('Saya mahu kasut diabetes dan temujanji imbasan kaki 3D');
assert_same('ms', (string) ($malayReply['language'] ?? ''), 'Malay support queries should be detected.');
assert_true(stripos((string) ($malayReply['reply'] ?? ''), 'Flexi Feet') !== false, 'Malay support replies should remain grounded in Flexi Feet.');
assert_same('ta', flexifeet_support_detect_language('நீரிழிவு காலணி கிடைக்குமா'), 'Tamil support queries should be detected.');
assert_same('zh', flexifeet_support_detect_language('我想预约糖尿病鞋'), 'Chinese support queries should be detected.');
$unicodeTokens = flexifeet_support_tokenize('கால் 扫描 kasut diabetes');
assert_true(in_array('கால்', $unicodeTokens, true) || in_array('扫描', $unicodeTokens, true) || in_array('kasut', $unicodeTokens, true), 'Support tokenizer should keep multilingual tokens.');
$offTopicReply = support_bot_reply('Can you help me trade cryptocurrency?');
assert_same(GOOGLE_AI_MODEL, (string) ($offTopicReply['model'] ?? ''), 'Out-of-scope replies should still report the configured Gemma model.');
assert_same('general', (string) ($offTopicReply['intent'] ?? ''), 'General prompts should go to Gemma instead of a hard-coded out-of-scope placeholder.');
assert_true(in_array((string) ($offTopicReply['engine'] ?? ''), ['google_ai_studio', 'local_grounded_fallback'], true), 'General prompts should use Gemma when configured or grounded fallback if unavailable.');
$randomReply = support_bot_reply('purple spaceship mango keyboard');
assert_same('general', (string) ($randomReply['intent'] ?? ''), 'Random unrelated prompts should not be blocked by the local scope gate.');
$policyReply = support_bot_reply('What is the return policy and delivery time for custom diabetic shoes?');
assert_same('service', (string) ($policyReply['intent'] ?? ''), 'Policy and delivery prompts should reason from Flexi Feet grounding docs.');
assert_true(stripos((string) ($policyReply['reply'] ?? ''), 'Flexi Feet') !== false, 'Policy and delivery replies should stay grounded in Flexi Feet.');
assert_true(stripos((string) ($policyReply['reply'] ?? ''), '3 to 4 weeks') !== false, 'Policy and delivery replies should include delivery timing.');
assert_true(stripos((string) ($policyReply['reply'] ?? ''), 'change of mind') !== false, 'Policy and delivery replies should include custom return policy.');
$servicesReply = support_bot_reply('What services do you offer?');
assert_same('service', (string) ($servicesReply['intent'] ?? ''), 'Generic service prompts should stay in scope.');
assert_true(stripos((string) ($servicesReply['reply'] ?? ''), '3D foot scanning') !== false, 'Generic service prompts should explain actual services.');
$locationReply = support_bot_reply('Where is your shop?');
assert_same('service', (string) ($locationReply['intent'] ?? ''), 'Location prompts should stay in scope.');
assert_true(stripos((string) ($locationReply['reply'] ?? ''), 'Residency Awani') !== false, 'Location prompts should include the shop address.');
$hoursReply = support_bot_reply('Are you open on Sunday?');
assert_same('service', (string) ($hoursReply['intent'] ?? ''), 'Hours prompts should stay in scope.');
assert_true(stripos((string) ($hoursReply['reply'] ?? ''), 'Sunday is closed') !== false, 'Sunday prompts should mention Sunday closure.');

$hadFeedbackFile = is_file(SUPPORT_FEEDBACK_FILE);
$originalFeedbackJson = $hadFeedbackFile ? (string) file_get_contents(SUPPORT_FEEDBACK_FILE) : '';
try {
    $feedbackResult = create_support_feedback([
        'response_id' => $semanticReply['response_id'] ?? 'TEST',
        'rating' => 'like',
        'intent' => 'service',
        'language' => 'en',
        'message' => 'Do you provide diabetic shoes and 3D foot scanning in Sentul?',
    ]);
    assert_true($feedbackResult['ok'] === true, 'Support feedback should be saved.');
    assert_true(count(read_support_feedback()) >= 1, 'Support feedback storage should contain the saved signal.');
} finally {
    if ($hadFeedbackFile) {
        file_put_contents(SUPPORT_FEEDBACK_FILE, $originalFeedbackJson, LOCK_EX);
    } elseif (is_file(SUPPORT_FEEDBACK_FILE)) {
        unlink(SUPPORT_FEEDBACK_FILE);
    }
}

$originalPostsJson = is_file(BLOG_POSTS_FILE) ? (string) file_get_contents(BLOG_POSTS_FILE) : '';
try {
    $post = save_blog_post([
        'title' => 'QA Slug Fallback Post',
        'slug' => '',
        'excerpt' => 'Short excerpt',
        'content' => str_repeat('Useful content. ', 40),
        'status' => 'Draft',
    ]);
    assert_same('qa-slug-fallback-post', $post['slug'], 'Empty blog slug should fall back to the title.');

    $updated = save_blog_post([
        'title' => 'QA Slug Fallback Post',
        'slug' => $post['slug'],
        'excerpt' => 'Updated excerpt',
        'content' => str_repeat('Useful content. ', 40),
        'status' => 'Draft',
    ], $post['id']);
    $matches = array_values(array_filter(read_blog_posts(false), fn($item) => ($item['id'] ?? '') === $post['id']));
    assert_true(count($matches) === 1, 'Updating an existing post id should not create a duplicate.');
    assert_same($post['id'], $updated['id'], 'Updating a post should preserve its id.');
} finally {
    file_put_contents(BLOG_POSTS_FILE, $originalPostsJson, LOCK_EX);
}

$originalReelsJson = is_file(REELS_FILE) ? (string) file_get_contents(REELS_FILE) : '';
try {
    $reel = save_reel([
        'url' => 'https://www.youtube.com/shorts/K8ZBQpI09VA?feature=share',
        'status' => 'Active',
    ]);
    assert_same('https://www.youtube.com/shorts/K8ZBQpI09VA', $reel['url'], 'Saving a short should store the clean canonical Shorts URL.');
    assert_same('YouTube Short K8ZBQpI09VA', $reel['title'], 'Saving a short should generate the title from the URL.');
    assert_same('https://i.ytimg.com/vi/K8ZBQpI09VA/hqdefault.jpg', $reel['thumbnail'], 'Saving a short should generate the thumbnail from YouTube.');
    assert_true((int) $reel['sort_order'] >= 1, 'Saving a short should set a positive sort order.');
} finally {
    file_put_contents(REELS_FILE, $originalReelsJson, LOCK_EX);
}

if (!empty($failures)) {
    fwrite(STDERR, implode("\n\n", $failures) . "\n");
    exit(1);
}

echo "All tests passed.\n";
