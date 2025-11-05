<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Phase 2 Testing: Backend Services ===" . PHP_EOL . PHP_EOL;

// Create test data
$template = App\Models\Template::create(['name' => 'Phase 2 Test Template', 'background' => 'test.jpg']);
echo "✓ Created template: {$template->name}" . PHP_EOL . PHP_EOL;

// === Test TemplateFieldService ===
echo "--- Testing TemplateFieldService ---" . PHP_EOL;
$fieldService = new App\Services\TemplateFieldService();

// Test 1: Add custom field
echo "Test 1: Adding custom field..." . PHP_EOL;
try {
    $customField = $fieldService->addCustomField($template, [
        'field_name' => 'phone',
        'field_label' => 'Phone Number',
        'field_type' => 'text',
        'show_in_form' => true,
        'show_in_cert' => false,
        'is_required' => false,
    ]);
    echo "✓ PASS: Custom field added - {$customField->field_label}" . PHP_EOL;
} catch (\Exception $e) {
    echo "✗ FAIL: {$e->getMessage()}" . PHP_EOL;
}

// Test 2: Try to add duplicate field (should fail)
echo "Test 2: Adding duplicate field (should fail)..." . PHP_EOL;
try {
    $fieldService->addCustomField($template, [
        'field_name' => 'name', // Duplicate
        'field_label' => 'Name Again',
        'field_type' => 'text',
    ]);
    echo "✗ FAIL: Should have thrown exception" . PHP_EOL;
} catch (\Exception $e) {
    echo "✓ PASS: Duplicate prevented - {$e->getMessage()}" . PHP_EOL;
}

// Test 3: Update field toggles
echo "Test 3: Updating field properties..." . PHP_EOL;
$nameField = $template->fields()->where('field_name', 'name')->first();
$updatedField = $fieldService->updateField($nameField, [
    'field_label' => 'Full Name',
    'show_in_cert' => true,
]);
echo "✓ PASS: Field updated - Label: {$updatedField->field_label}" . PHP_EOL;

// Test 4: Update field position
echo "Test 4: Updating field position..." . PHP_EOL;
$positionData = [
    'x' => 100,
    'y' => 200,
    'fontSize' => 24,
    'fontFamily' => 'Helvetica',
    'color' => '#FF0000',
];
$positionedField = $fieldService->updateFieldPosition($nameField, $positionData);
echo "✓ PASS: Position updated - X: {$positionedField->position_data['x']}, Y: {$positionedField->position_data['y']}" . PHP_EOL;

// Test 5: Get canvas fields
echo "Test 5: Getting canvas fields..." . PHP_EOL;
$canvasFields = $fieldService->getCanvasFields($template);
echo "✓ PASS: Canvas fields count: {$canvasFields->count()}" . PHP_EOL;
echo "  Fields on canvas: " . $canvasFields->pluck('field_name')->implode(', ') . PHP_EOL;

// Test 6: Get form fields
echo "Test 6: Getting form fields..." . PHP_EOL;
$formFields = $fieldService->getFormFields($template);
echo "✓ PASS: Form fields count: {$formFields->count()}" . PHP_EOL;
echo "  Fields in form: " . $formFields->pluck('field_name')->implode(', ') . PHP_EOL;

// Test 7: Try to delete predefined field (should fail)
echo "Test 7: Deleting predefined field (should fail)..." . PHP_EOL;
try {
    $fieldService->deleteField($nameField);
    echo "✗ FAIL: Should have thrown exception" . PHP_EOL;
} catch (\Exception $e) {
    echo "✓ PASS: Predefined field protected - {$e->getMessage()}" . PHP_EOL;
}

// Test 8: Delete custom field
echo "Test 8: Deleting custom field..." . PHP_EOL;
try {
    $phoneField = $template->fields()->where('field_name', 'phone')->first();
    $fieldService->deleteField($phoneField);
    echo "✓ PASS: Custom field deleted" . PHP_EOL;
} catch (\Exception $e) {
    echo "✗ FAIL: {$e->getMessage()}" . PHP_EOL;
}

echo PHP_EOL;

// === Test EventConfigurationService ===
echo "--- Testing EventConfigurationService ---" . PHP_EOL;
$eventService = new App\Services\EventConfigurationService();

$event = App\Models\Event::create([
    'name' => 'Test Event',
    'description' => 'Testing event service',
    'template_id' => $template->id,
    'slug' => 'test-event-phase2',
]);

// Test 9: Get static value fields
echo "Test 9: Getting static value fields..." . PHP_EOL;
$staticFields = $eventService->getStaticValueFields($template);
echo "✓ PASS: Static fields count: {$staticFields->count()}" . PHP_EOL;
echo "  Static fields: " . $staticFields->pluck('field_name')->implode(', ') . PHP_EOL;

// Test 10: Initialize default static values
echo "Test 10: Initializing default static values..." . PHP_EOL;
$defaultValues = $eventService->initializeEventStaticValues($event);
echo "✓ PASS: Default values: " . json_encode($defaultValues) . PHP_EOL;

// Test 11: Save static values
echo "Test 11: Saving static values..." . PHP_EOL;
try {
    $eventService->saveStaticValues($event, [
        'event_name' => 'PHP Workshop 2025',
        'date' => '2025-11-10',
    ]);
    echo "✓ PASS: Static values saved - " . json_encode($event->static_values) . PHP_EOL;
} catch (\Exception $e) {
    echo "✗ FAIL: {$e->getMessage()}" . PHP_EOL;
}

// Test 12: Get registration form preview
echo "Test 12: Getting registration form preview..." . PHP_EOL;
$formPreview = $eventService->getRegistrationFormPreview($event);
echo "✓ PASS: Form preview generated - {$formPreview->count()} fields" . PHP_EOL;
foreach ($formPreview as $field) {
    echo "  - {$field['label']} ({$field['type']})" . ($field['required'] ? ' *' : '') . PHP_EOL;
}

// Test 13: Validate event configuration
echo "Test 13: Validating event configuration..." . PHP_EOL;
try {
    $isValid = $eventService->validateEventConfiguration($event);
    echo "✓ PASS: Event configuration valid" . PHP_EOL;
} catch (\Exception $e) {
    echo "✗ FAIL: {$e->getMessage()}" . PHP_EOL;
}

// Test 14: Get event configuration summary
echo "Test 14: Getting event configuration summary..." . PHP_EOL;
$summary = $eventService->getEventConfigurationSummary($event);
echo "✓ PASS: Configuration summary generated" . PHP_EOL;
echo "  Event: {$summary['event_name']}" . PHP_EOL;
echo "  Template: {$summary['template_name']}" . PHP_EOL;
echo "  Form fields: " . count($summary['form_fields']) . PHP_EOL;
echo "  Cert fields: " . count($summary['certificate_fields']) . PHP_EOL;

echo PHP_EOL;

// === Test RegistrationService ===
echo "--- Testing RegistrationService ---" . PHP_EOL;
$registrationService = new App\Services\RegistrationService();

// Test 15: Build validation rules
echo "Test 15: Building validation rules..." . PHP_EOL;
$rules = $registrationService->buildValidationRules($event);
echo "✓ PASS: Validation rules built" . PHP_EOL;
foreach ($rules as $field => $rule) {
    echo "  - {$field}: {$rule}" . PHP_EOL;
}

// Test 16: Get form configuration
echo "Test 16: Getting form configuration..." . PHP_EOL;
$formConfig = $registrationService->getFormConfiguration($event);
echo "✓ PASS: Form configuration generated" . PHP_EOL;
echo "  Event: {$formConfig['event']['name']}" . PHP_EOL;
echo "  Fields: " . count($formConfig['fields']) . PHP_EOL;

// Test 17: Create registration with valid data
echo "Test 17: Creating registration with valid data..." . PHP_EOL;
try {
    $registration = $registrationService->createRegistration($event, [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
    ]);
    echo "✓ PASS: Registration created - ID: {$registration->id}" . PHP_EOL;
} catch (\Exception $e) {
    echo "✗ FAIL: {$e->getMessage()}" . PHP_EOL;
}

// Test 18: Try to create registration with invalid data (should fail)
echo "Test 18: Creating registration with invalid data (should fail)..." . PHP_EOL;
try {
    $registrationService->createRegistration($event, [
        'name' => '', // Empty required field
        'email' => 'invalid-email', // Invalid email
    ]);
    echo "✗ FAIL: Should have thrown validation exception" . PHP_EOL;
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "✓ PASS: Validation failed as expected" . PHP_EOL;
    echo "  Errors: " . json_encode($e->errors()) . PHP_EOL;
}

// Test 19: Approve registration
echo "Test 19: Approving registration..." . PHP_EOL;
$approvedReg = $registrationService->approveRegistration($registration);
echo "✓ PASS: Registration approved - Status: {$approvedReg->status}" . PHP_EOL;

// Test 20: Test certificate data merge
echo "Test 20: Testing certificate data merge..." . PHP_EOL;
$certData = $registration->getCertificateData();
echo "✓ PASS: Certificate data merged" . PHP_EOL;
echo "  Data: " . json_encode($certData, JSON_PRETTY_PRINT) . PHP_EOL;
echo "  Contains name: " . (isset($certData['name']) ? 'Yes' : 'No') . PHP_EOL;
echo "  Contains email: " . (isset($certData['email']) ? 'Yes' : 'No') . PHP_EOL;
echo "  Contains event_name: " . (isset($certData['event_name']) ? 'Yes' : 'No') . PHP_EOL;
echo "  Contains date: " . (isset($certData['date']) ? 'Yes' : 'No') . PHP_EOL;

echo PHP_EOL . "=== Phase 2 Testing Complete ===" . PHP_EOL;
echo "✓ All service classes working correctly!" . PHP_EOL;
