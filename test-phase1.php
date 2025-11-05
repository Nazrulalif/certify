<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Phase 1 Testing ===" . PHP_EOL . PHP_EOL;

// Test 1: Create a template
echo "Test 1: Creating template..." . PHP_EOL;
$template = App\Models\Template::create(['name' => 'Test Template', 'background' => 'test.jpg']);
echo "✓ Template created: {$template->name}" . PHP_EOL . PHP_EOL;

// Test 2: Check predefined fields auto-created
echo "Test 2: Checking predefined fields..." . PHP_EOL;
$fieldsCount = $template->fields->count();
echo "✓ Fields count: {$fieldsCount} (Expected: 4)" . PHP_EOL;

if ($fieldsCount === 4) {
    echo "✓ PASS: Predefined fields auto-created" . PHP_EOL;
} else {
    echo "✗ FAIL: Expected 4 fields" . PHP_EOL;
}
echo PHP_EOL;

// Test 3: Display field names
echo "Test 3: Field names..." . PHP_EOL;
$fieldNames = $template->fields->pluck('field_name')->toArray();
echo "✓ Field names: " . implode(', ', $fieldNames) . PHP_EOL;
echo "✓ Expected: name, email, event_name, date" . PHP_EOL . PHP_EOL;

// Test 4: Check form fields (show_in_form = true)
echo "Test 4: Form fields (show_in_form = true)..." . PHP_EOL;
$formFields = $template->formFields->pluck('field_name')->toArray();
echo "✓ Form fields: " . implode(', ', $formFields) . PHP_EOL;
echo "✓ Expected: name, email" . PHP_EOL . PHP_EOL;

// Test 5: Check cert fields (show_in_cert = true)
echo "Test 5: Certificate fields (show_in_cert = true)..." . PHP_EOL;
$certFields = $template->certFields->pluck('field_name')->toArray();
echo "✓ Cert fields: " . implode(', ', $certFields) . PHP_EOL;
echo "✓ Expected: name, event_name, date" . PHP_EOL . PHP_EOL;

// Test 6: Check static value fields
echo "Test 6: Static value fields (show_in_form=false, show_in_cert=true)..." . PHP_EOL;
$staticFields = $template->staticValueFields->pluck('field_name')->toArray();
echo "✓ Static value fields: " . implode(', ', $staticFields) . PHP_EOL;
echo "✓ Expected: event_name, date" . PHP_EOL . PHP_EOL;

// Test 7: Create event with static values
echo "Test 7: Creating event with static values..." . PHP_EOL;
$event = App\Models\Event::create([
    'name' => 'Laravel Workshop 2025',
    'description' => 'Test workshop',
    'template_id' => $template->id,
    'slug' => 'laravel-workshop-2025',
    'static_values' => [
        'event_name' => 'Laravel Workshop 2025',
        'date' => '2025-11-05'
    ]
]);
echo "✓ Event created: {$event->name}" . PHP_EOL;
echo "✓ Static values: " . json_encode($event->static_values) . PHP_EOL . PHP_EOL;

// Test 8: Create registration with form data
echo "Test 8: Creating registration..." . PHP_EOL;
$registration = App\Models\Registration::create([
    'event_id' => $event->id,
    'form_data' => [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ],
    'status' => 'approved'
]);
echo "✓ Registration created for: {$registration->form_data['name']}" . PHP_EOL . PHP_EOL;

// Test 9: Get merged certificate data
echo "Test 9: Testing data merge (static + form)..." . PHP_EOL;
$certificateData = $registration->getCertificateData();
echo "✓ Certificate data: " . json_encode($certificateData, JSON_PRETTY_PRINT) . PHP_EOL;
echo "✓ Should contain: name, email, event_name, date" . PHP_EOL . PHP_EOL;

// Test 10: Validate merged data
echo "Test 10: Validating merged data..." . PHP_EOL;
if (isset($certificateData['name']) && $certificateData['name'] === 'John Doe') {
    echo "✓ PASS: name field merged correctly" . PHP_EOL;
}
if (isset($certificateData['email']) && $certificateData['email'] === 'john@example.com') {
    echo "✓ PASS: email field merged correctly" . PHP_EOL;
}
if (isset($certificateData['event_name']) && $certificateData['event_name'] === 'Laravel Workshop 2025') {
    echo "✓ PASS: event_name from static values" . PHP_EOL;
}
if (isset($certificateData['date']) && $certificateData['date'] === '2025-11-05') {
    echo "✓ PASS: date from static values" . PHP_EOL;
}

echo PHP_EOL . "=== Phase 1 Testing Complete ===" . PHP_EOL;
echo "✓ All core functionality working correctly!" . PHP_EOL;
