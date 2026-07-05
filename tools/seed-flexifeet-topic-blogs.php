<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$sources = [
    'CDC foot care' => 'https://www.cdc.gov/diabetes/diabetes-complications/diabetes-and-your-feet.html',
    'American Diabetes Association foot care tips' => 'https://diabetes.org/health-wellness/diabetes-and-your-feet/8-tips-protect-your-feet',
    'Mayo Clinic bunions' => 'https://www.mayoclinic.org/diseases-conditions/bunions/symptoms-causes/syc-20354799',
    'Mayo Clinic plantar fasciitis' => 'https://www.mayoclinic.org/diseases-conditions/plantar-fasciitis/diagnosis-treatment/drc-20354851',
    'Mayo Clinic podiatry conditions' => 'https://www.mayoclinichealthsystem.org/services-and-treatments/podiatry',
    'Wikipedia diabetic shoe' => 'https://en.wikipedia.org/wiki/Diabetic_shoe',
    'Wikipedia diabetic foot' => 'https://en.wikipedia.org/wiki/Diabetic_foot',
    'Wikipedia diabetic foot ulcer' => 'https://en.wikipedia.org/wiki/Diabetic_foot_ulcer',
    'Wikipedia Charcot neuroarthropathy' => 'https://en.wikipedia.org/wiki/Neuropathic_arthropathy',
    'WonderFoot Orthotics market reference' => 'https://www.wonderfootorthotics.com/',
];

$problemPosts = [
    [
        'topic' => 'Diabetic Neuropathy',
        'slug' => 'diabetic-neuropathy-protective-footwear-malaysia-guide',
        'title' => 'Diabetic Neuropathy: Why Protective Footwear Matters in Malaysia',
        'image' => 'assets/images/conditions/diabetic-neuropathy-reference.png',
        'keyword' => 'diabetic neuropathy shoes Malaysia',
        'why' => 'Diabetic neuropathy can reduce protective sensation, so a person may not feel rubbing, heat, pressure, or a small injury early enough.',
        'risk' => 'This is why daily foot inspection, smooth shoe linings, roomy toe boxes, and socks that do not bunch are important parts of prevention.',
        'help' => 'Flexi Feet helps by assessing foot shape, shoe fit, pressure risk, and footwear depth before recommending diabetic shoes, offload insoles, and diabetic socks.',
        'sources' => ['CDC foot care', 'American Diabetes Association foot care tips', 'Wikipedia diabetic foot'],
    ],
    [
        'topic' => 'Foot Ulcers',
        'slug' => 'diabetic-foot-ulcers-offloading-shoe-fit-flexi-feet',
        'title' => 'Foot Ulcers: How Offloading and Shoe Fit Help Reduce Repeat Pressure',
        'image' => 'assets/images/conditions/foot-ulcers.jpg',
        'keyword' => 'diabetic foot ulcer offloading footwear',
        'why' => 'A foot ulcer is a break in the skin that can become serious when pressure, friction, poor circulation, and reduced sensation are present.',
        'risk' => 'Repeated pressure in the same place can create callus first, then skin breakdown, especially when a shoe is narrow, shallow, or rough inside.',
        'help' => 'Flexi Feet focuses on pressure-aware footwear planning: extra-depth shoes, custom offload insoles, smooth interiors, and fitting follow-up for healed or high-risk areas.',
        'sources' => ['CDC foot care', 'Wikipedia diabetic foot ulcer', 'Wikipedia diabetic shoe'],
    ],
    [
        'topic' => 'Calluses',
        'slug' => 'calluses-on-diabetic-feet-pressure-warning-signs',
        'title' => 'Calluses on Diabetic Feet: A Pressure Warning Sign You Should Not Ignore',
        'image' => 'assets/images/conditions/calluses-corns.jpg',
        'keyword' => 'calluses diabetic feet pressure shoes',
        'why' => 'A callus is thickened skin caused by repeated pressure or friction. On a diabetic foot, that repeated load can be a warning sign.',
        'risk' => 'The callus may look small, but it often shows that the shoe or insole is not distributing pressure well enough for daily walking.',
        'help' => 'Flexi Feet checks where callus appears, what shoes are being worn, and whether a custom insole, offload area, or deeper footwear can reduce repeated stress.',
        'sources' => ['CDC foot care', 'American Diabetes Association foot care tips', 'Mayo Clinic podiatry conditions'],
    ],
    [
        'topic' => 'Corns',
        'slug' => 'corns-toe-pressure-extra-depth-shoes-malaysia',
        'title' => 'Corns and Toe Pressure: When Extra-Depth Shoes Can Help',
        'image' => 'assets/images/conditions/calluses-corns.jpg',
        'keyword' => 'corns toe pressure extra depth shoes',
        'why' => 'Corns are concentrated pressure spots, often around toes, joints, or bony areas that rub against footwear.',
        'risk' => 'For people with diabetes or poor sensation, a corn can hide deeper irritation and should not be treated only as a cosmetic skin issue.',
        'help' => 'Flexi Feet helps by matching toe-box depth, width, smooth lining, and insole height so the shoe does not press the toes into new pressure.',
        'sources' => ['CDC foot care', 'Mayo Clinic podiatry conditions', 'Wikipedia diabetic shoe'],
    ],
    [
        'topic' => 'Poor Circulation',
        'slug' => 'poor-circulation-feet-diabetic-shoes-socks-guide',
        'title' => 'Poor Circulation in the Feet: Footwear and Sock Choices That Matter',
        'image' => 'assets/images/conditions/poor-circulation-reference.png',
        'keyword' => 'poor circulation feet diabetic socks shoes',
        'why' => 'Poor circulation can slow healing and make the feet less tolerant of rubbing, tight elastic, or pressure marks.',
        'risk' => 'Shoes and socks should avoid unnecessary compression unless it is clinically appropriate, and they should leave no deep marks after wear.',
        'help' => 'Flexi Feet reviews shoe fit, sock choice, swelling patterns, and daily walking needs before suggesting diabetic socks, compression options, or custom footwear.',
        'sources' => ['CDC foot care', 'American Diabetes Association foot care tips', 'Wikipedia diabetic foot'],
    ],
    [
        'topic' => 'Hammer Toes',
        'slug' => 'hammer-toes-shoe-depth-custom-footwear-malaysia',
        'title' => 'Hammer Toes: Why Shoe Depth and Smooth Interiors Are Important',
        'image' => 'assets/images/conditions/hammer-toes-reference.png',
        'keyword' => 'hammer toes extra depth shoes Malaysia',
        'why' => 'Hammer toes bend abnormally, which can make the top or tip of the toe rub inside ordinary shoes.',
        'risk' => 'If the shoe is shallow, the toe may press upward into the upper or forward into the front of the shoe, creating redness or corns.',
        'help' => 'Flexi Feet supports hammer toe accommodation with extra-depth footwear, wider toe boxes, smooth interiors, and insoles that do not lift toes into pressure.',
        'sources' => ['Mayo Clinic podiatry conditions', 'CDC foot care', 'Wikipedia diabetic shoe'],
    ],
    [
        'topic' => 'Bunions',
        'slug' => 'bunions-wide-toe-box-custom-shoes-malaysia',
        'title' => 'Bunions and Wide Toe Boxes: Choosing Shoes That Do Not Fight the Foot',
        'image' => 'assets/images/conditions/bunions.jpg',
        'keyword' => 'bunions wide toe box custom shoes Malaysia',
        'why' => 'A bunion is a bony bump at the base of the big toe that can make the forefoot wider and harder to fit.',
        'risk' => 'Narrow shoes can press on the bunion area, pull the big toe further into crowding, and create repeated redness or soreness.',
        'help' => 'Flexi Feet assesses width, depth, material flexibility, and insole space so bunion-sensitive feet are accommodated instead of squeezed.',
        'sources' => ['Mayo Clinic bunions', 'Mayo Clinic podiatry conditions', 'CDC foot care'],
    ],
    [
        'topic' => 'Flat Feet',
        'slug' => 'flat-feet-custom-insoles-kuala-lumpur-guide',
        'title' => 'Flat Feet: When Custom Insoles Are Worth Considering',
        'image' => 'assets/images/conditions/flat-feet.jpg',
        'keyword' => 'flat feet custom insoles Kuala Lumpur',
        'why' => 'Flat feet can change how load moves through the arch, heel, ankle, knee, and shoe.',
        'risk' => 'A generic arch insert may feel too hard, too high, or too weak because the support must match the person, activity, and footwear.',
        'help' => 'Flexi Feet uses assessment and fitting to design flat feet insoles that balance support, comfort, material stiffness, and daily shoe compatibility.',
        'sources' => ['Mayo Clinic podiatry conditions', 'WonderFoot Orthotics market reference', 'Wikipedia diabetic shoe'],
    ],
    [
        'topic' => 'Charcot Foot',
        'slug' => 'charcot-foot-shape-accommodation-malaysia-guide',
        'title' => 'Charcot Foot: Why Shape Accommodation and Medical Guidance Matter',
        'image' => 'assets/images/conditions/charcot-foot-updated.jpg',
        'keyword' => 'Charcot foot custom footwear Malaysia',
        'why' => 'Charcot foot can change foot shape after neuropathy-related bone and joint damage, often making ordinary footwear unsafe or impossible to fit well.',
        'risk' => 'This condition needs medical involvement. Footwear planning should avoid pressure on new bony areas and should not replace clinical care.',
        'help' => 'Flexi Feet helps with custom footwear and insole accommodation after medical guidance, focusing on room, stability, and reduced friction.',
        'sources' => ['Wikipedia Charcot neuroarthropathy', 'Wikipedia diabetic foot', 'CDC foot care'],
    ],
    [
        'topic' => 'Heel Pain',
        'slug' => 'heel-pain-plantar-fasciitis-supportive-footwear-malaysia',
        'title' => 'Heel Pain and Plantar Fasciitis: Supportive Footwear Basics',
        'image' => 'assets/images/conditions/heel-pain.jpg',
        'keyword' => 'heel pain plantar fasciitis footwear Malaysia',
        'why' => 'Heel pain may be linked to plantar fascia irritation, load changes, poor cushioning, or shoes that do not support the foot well.',
        'risk' => 'Many people keep changing shoes without checking arch support, heel stability, calf tightness, or daily standing patterns.',
        'help' => 'Flexi Feet reviews the shoe, foot posture, and pressure needs before suggesting supportive walking shoes, custom insoles, or footwear adjustments.',
        'sources' => ['Mayo Clinic plantar fasciitis', 'Mayo Clinic podiatry conditions', 'WonderFoot Orthotics market reference'],
    ],
    [
        'topic' => 'Partial Foot Amputation',
        'slug' => 'partial-foot-amputation-shoe-fillers-custom-insoles',
        'title' => 'Partial Foot Amputation: Shoe Fillers, Insoles, and Daily Stability',
        'image' => 'assets/images/conditions/amputation.jpg',
        'keyword' => 'partial foot amputation shoe filler custom insole',
        'why' => 'After partial foot amputation, the foot may no longer fill or load the shoe in the usual way.',
        'risk' => 'Empty space, sliding, pressure at the remaining forefoot, and balance changes can make ordinary footwear uncomfortable or risky.',
        'help' => 'Flexi Feet can plan shoe fillers, custom insoles, and extra-depth footwear so the shoe, insert, and remaining foot work together.',
        'sources' => ['Wikipedia diabetic shoe', 'Wikipedia diabetic foot', 'CDC foot care'],
    ],
];

$productPosts = [
    [
        'topic' => 'Therapeutic Comfort Shoes',
        'slug' => 'therapeutic-comfort-shoes-diabetic-orthopaedic-guide',
        'title' => 'Therapeutic Comfort Shoes: What Makes Them Different from Ordinary Shoes?',
        'image' => 'assets/images/products/therapeutic-comfort-shoes.png',
        'keyword' => 'therapeutic comfort shoes Malaysia',
        'why' => 'Therapeutic comfort shoes are chosen for more than softness. They need depth, stable support, enough width, and an interior that avoids unnecessary rubbing.',
        'risk' => 'A shoe can feel comfortable in the shop but still create pressure after hours of walking if it does not match foot shape or insole needs.',
        'help' => 'Flexi Feet fits therapeutic comfort shoes around the person, the insole, and the reason for protection, especially for diabetic and orthopaedic needs.',
        'sources' => ['Wikipedia diabetic shoe', 'CDC foot care', 'American Diabetes Association foot care tips'],
    ],
    [
        'topic' => 'Knit Comfort Shoes',
        'slug' => 'knit-comfort-shoes-sensitive-feet-fitting-guide',
        'title' => 'Knit Comfort Shoes for Sensitive Feet: Flexibility with the Right Support',
        'image' => 'assets/images/products/pink-knit-comfort-shoe.png',
        'keyword' => 'knit comfort shoes sensitive feet',
        'why' => 'Knit uppers can feel gentle because they flex around the foot, which may help people who dislike stiff shoe pressure.',
        'risk' => 'Flexibility alone is not enough. Sensitive feet still need stable soles, enough depth, proper width, and room for the insole.',
        'help' => 'Flexi Feet checks whether a knit comfort shoe is suitable for the user or whether a more structured custom footwear option is safer.',
        'sources' => ['CDC foot care', 'Wikipedia diabetic shoe', 'WonderFoot Orthotics market reference'],
    ],
    [
        'topic' => 'Walking Shoes',
        'slug' => 'walking-shoes-custom-insoles-diabetic-feet-malaysia',
        'title' => 'Walking Shoes and Custom Insoles: How to Make Daily Steps Safer',
        'image' => 'assets/images/products/navy-knit-walking-shoe.png',
        'keyword' => 'walking shoes custom insoles diabetic feet',
        'why' => 'Walking shoes need to manage repeated load, not just look sporty. Stability, cushioning, width, and insole compatibility all matter.',
        'risk' => 'If a walking shoe is too shallow, adding a custom insole can crowd the foot and create new toe or forefoot pressure.',
        'help' => 'Flexi Feet matches walking shoes with custom insoles and foot assessment so daily walking feels supported without squeezing the foot.',
        'sources' => ['CDC foot care', 'American Diabetes Association foot care tips', 'WonderFoot Orthotics market reference'],
    ],
    [
        'topic' => 'Custom Orthotic Insoles',
        'slug' => 'custom-orthotic-insoles-3d-foot-scan-kuala-lumpur',
        'title' => 'Custom Orthotic Insoles: Why Assessment Matters More than a Generic Insert',
        'image' => 'assets/images/products/custom-orthotic-insoles.png',
        'keyword' => 'custom orthotic insoles 3D foot scan Kuala Lumpur',
        'why' => 'Custom orthotic insoles should be built around foot shape, pressure needs, material choice, and the shoes the person actually wears.',
        'risk' => 'A generic insert may support the arch but miss forefoot pressure, heel stability, diabetic skin risk, or shoe-depth problems.',
        'help' => 'Flexi Feet combines assessment, 3D foot scanning, and fitting so the insole is designed as part of the footwear system.',
        'sources' => ['WonderFoot Orthotics market reference', 'Wikipedia diabetic shoe', 'Mayo Clinic podiatry conditions'],
    ],
    [
        'topic' => 'Adjustable Medical Sandals',
        'slug' => 'adjustable-medical-sandals-swollen-sensitive-feet',
        'title' => 'Adjustable Medical Sandals: Helpful When Feet Swell or Shoes Feel Too Tight',
        'image' => 'assets/images/products/adjustable-medical-sandal.png',
        'keyword' => 'adjustable medical sandals swollen feet',
        'why' => 'Adjustable sandals can help when swelling changes through the day or when a closed shoe is difficult to put on.',
        'risk' => 'A sandal still needs enough sole structure and should not expose a high-risk diabetic foot to unnecessary injury.',
        'help' => 'Flexi Feet helps decide when adjustable footwear is suitable and when diabetic shoes or custom orthopaedic footwear are safer.',
        'sources' => ['CDC foot care', 'American Diabetes Association foot care tips', 'Wikipedia diabetic shoe'],
    ],
    [
        'topic' => 'In-Shoe Orthotic Fitting',
        'slug' => 'in-shoe-orthotic-fitting-avoid-new-pressure',
        'title' => 'In-Shoe Orthotic Fitting: Avoiding New Pressure While Adding Support',
        'image' => 'assets/images/products/orthotic-in-shoe.png',
        'keyword' => 'in-shoe orthotic fitting custom insole',
        'why' => 'An orthotic only works well if it fits inside the shoe without lifting the foot into tight areas.',
        'risk' => 'Too much insole height can make the toe box shallow, press the heel collar, or change how the shoe closes.',
        'help' => 'Flexi Feet checks shoe depth, removable insoles, heel hold, toe room, and pressure comfort before finalizing an orthotic fit.',
        'sources' => ['Wikipedia diabetic shoe', 'CDC foot care', 'WonderFoot Orthotics market reference'],
    ],
    [
        'topic' => 'Supportive Comfort Sandals',
        'slug' => 'supportive-comfort-sandals-home-daily-footwear',
        'title' => 'Supportive Comfort Sandals: Better Daily Support than Flat Slippers',
        'image' => 'assets/images/products/supportive-comfort-sandals.png',
        'keyword' => 'supportive comfort sandals foot support',
        'why' => 'Flat slippers are easy to wear but often provide little arch support, heel stability, or pressure control.',
        'risk' => 'For sensitive feet, an unsupportive sandal can increase fatigue, slipping, toe gripping, or repeated pressure.',
        'help' => 'Flexi Feet can suggest supportive sandals for appropriate users and explain when a closed shoe or custom insole is a better choice.',
        'sources' => ['Mayo Clinic podiatry conditions', 'CDC foot care', 'American Diabetes Association foot care tips'],
    ],
    [
        'topic' => 'Diabetic & Compression Socks',
        'slug' => 'diabetic-compression-socks-difference-foot-care',
        'title' => 'Diabetic and Compression Socks: Choosing the Right Sock for the Right Foot',
        'image' => 'assets/images/products/diabetic-compression-socks.png',
        'keyword' => 'diabetic compression socks Malaysia',
        'why' => 'Diabetic socks focus on reducing rubbing, moisture, bunching, and tight marks. Compression socks are different and should match swelling and circulation needs.',
        'risk' => 'The wrong sock can leave deep elastic marks, trap moisture, fold inside the shoe, or add pressure to sensitive skin.',
        'help' => 'Flexi Feet helps match socks with diabetic shoes, insoles, and daily use so the sock supports the footwear plan instead of fighting it.',
        'sources' => ['CDC foot care', 'American Diabetes Association foot care tips', 'Wikipedia diabetic foot'],
    ],
];

function source_links(array $labels, array $sources): string
{
    $items = [];
    foreach ($labels as $label) {
        if (isset($sources[$label])) {
            $items[] = '- [' . $label . '](' . $sources[$label] . ')';
        }
    }
    return implode("\n", $items);
}

function post_content(array $post, array $sources): string
{
    return "# " . $post['title'] . "\n\n"
        . "## What it is\n\n"
        . $post['why'] . "\n\n"
        . "## Why it matters\n\n"
        . $post['risk'] . " For people with diabetes, reduced sensation or circulation can make small fit problems more serious, so footwear should be checked before redness, callus, or rubbing becomes a bigger issue.\n\n"
        . "## How Flexi Feet helps\n\n"
        . $post['help'] . " Our role is footwear, insole, sock, scanning, fitting, and follow-up support. We do not replace medical diagnosis or urgent care, and we encourage customers with wounds, infection signs, sudden swelling, or severe pain to see a qualified healthcare professional.\n\n"
        . "## What to bring to a fitting\n\n"
        . "- The shoes you wear most often\n"
        . "- Any current insoles, socks, or braces\n"
        . "- Photos or notes showing where redness, callus, pain, or rubbing appears\n"
        . "- Relevant medical advice from your doctor, podiatrist, or wound-care team\n\n"
        . "## Why this matters in Malaysia\n\n"
        . "Local customers often compare custom orthotic providers, shoe shops, online inserts, and comfort footwear. The important difference is not the label on the product; it is whether the assessment connects foot condition, pressure, shoe depth, materials, and follow-up. Flexi Feet builds the recommendation around the user's actual foot and daily walking needs.\n\n"
        . "## Research notes\n\n"
        . source_links($post['sources'], $sources) . "\n";
}

$allPosts = array_merge($problemPosts, $productPosts);
$created = 0;
$updated = 0;

foreach ($allPosts as $index => $item) {
    $existing = find_blog_post($item['slug'], false);
    $payload = [
        'title' => $item['title'],
        'slug' => $item['slug'],
        'excerpt' => $item['why'] . ' Learn how Flexi Feet approaches fit, pressure, and daily footwear support.',
        'content' => post_content($item, $sources),
        'status' => 'Published',
        'featured_image' => $item['image'],
        'seo_title' => $item['title'],
        'seo_description' => $item['why'] . ' Flexi Feet explains practical footwear, insole, and sock considerations for safer daily walking.',
        'focus_keyword' => $item['keyword'],
        'canonical_url' => '',
        'social_image' => $item['image'],
        'noindex' => false,
    ];
    $id = is_array($existing) ? (string) ($existing['id'] ?? '') : null;
    save_blog_post($payload, $id ?: null);
    if ($id) {
        $updated++;
    } else {
        $created++;
    }
}

echo json_encode(['created' => $created, 'updated' => $updated, 'total_seed_posts' => count($allPosts)], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
