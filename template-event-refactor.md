# Template-Event System Refactor

## Table of Contents
1. [Overview](#overview)
2. [Current System vs New System](#current-system-vs-new-system)
3. [Complete Workflow](#complete-workflow)
4. [Database Schema Changes](#database-schema-changes)
5. [Field Configuration Rules](#field-configuration-rules)
6. [UI/UX Changes](#uiux-changes)
7. [Backend Logic](#backend-logic)
8. [Migration Strategy](#migration-strategy)
9. [Examples & Use Cases](#examples--use-cases)

---

## Overview

### Current Problem
- Template fields and event registration fields are separate
- No connection between what's on certificate and what users fill
- Duplicate field definitions
- Static values (like event_name, date) must be handled separately

### New Solution
- **Single source of truth**: Template fields define EVERYTHING
- **Flexible toggles**: Each field can be "Show in Form" and/or "Show in Cert"
- **Static values per event**: Fields not shown in form can have static values set per event
- **Unified data model**: Registration data + static values = complete certificate data

---

## Current System vs New System

### Current System Flow
```
1. Create Template
   └─ Add fields for certificate positioning

2. Create Event
   ├─ Select template
   └─ Separately define registration form fields (no connection to template)

3. Generate Certificate
   └─ Manual mapping between registration data and template fields
```

### New System Flow
```
1. Create Template
   ├─ Upload background
   ├─ Define ALL fields with toggles:
   │  ├─ Show in Form (user fills)
   │  ├─ Show in Cert (appears on certificate)
   │  └─ Required (validation)
   └─ Position fields (only "Show in Cert" appear on canvas)

2. Create Event
   ├─ Select template
   ├─ Fields auto-load from template
   └─ Set static values for fields where:
       Show in Form = NO + Show in Cert = YES

3. Public Registration
   └─ Form shows only fields with "Show in Form" = YES

4. Generate Certificate
   └─ Merge: Registration data + Static event values
```

---

## Complete Workflow

### 1. Template Creation

#### Step 1: Basic Information
- Template name
- Upload background image (JPG/PNG)
- Set as default (optional)

#### Step 2: Define Fields

**Field Definition Table:**

| Field Name | Field Type | Show in Form | Show in Cert | Required | Action |
|------------|------------|--------------|--------------|----------|--------|
| name | Text | ☑ | ☑ | ☑ | - (Predefined) |
| email | Email | ☑ | ☐ | ☑ | - (Predefined) |
| phone | Text | ☑ | ☐ | ☐ | - (Predefined) |
| event_name | Text | ☐ | ☑ | - | - (Predefined) |
| date | Date | ☐ | ☑ | - | - (Predefined) |
| certificate_id | Text | ☐ | ☑ | - | - (Predefined) |
| custom_field | Text | ☑ | ☑ | ☐ | [Remove] |

**Predefined Fields** (No remove button):
- `name` - Participant name
- `email` - Contact email
- `phone` - Contact phone
- `event_name` - Event/course name
- `date` - Event/completion date
- `certificate_id` - Certificate number

**Custom Fields** (User can add/remove):
- User can add additional fields as needed
- Each custom field has a remove button

**Toggle Behavior:**
- **Show in Form**: Field appears in public registration form
- **Show in Cert**: Field appears on certificate canvas for positioning
- **Required**: Field is mandatory (only applicable if "Show in Form" = YES)

**Field Types Available:**
- Text (single line)
- Email
- Date
- Number
- Textarea (multi-line)
- Select (dropdown)

#### Step 3: Position Fields on Canvas

**Canvas Behavior:**
- Only fields with "Show in Cert" = ☑ appear on Fabric.js canvas
- User can drag, resize, style each field:
  - Font family, size, color
  - Text alignment (left, center, right)
  - Bold, italic
  - Rotation

**Example:**
If template has these fields:
- name (Show in Cert: ☑)
- email (Show in Cert: ☐)
- event_name (Show in Cert: ☑)
- date (Show in Cert: ☑)

Canvas will only show: name, event_name, date

---

### 2. Event Creation

#### Step 1: Basic Information
- Event name
- Event description
- Event slug (auto-generated or custom)
- Select template from dropdown

#### Step 2: Configure Static Values

When template is selected, system auto-loads all template fields where:
- `show_in_form` = NO
- `show_in_cert` = YES

User fills static values that apply to ALL certificates in this event.

**Example Form:**

```
┌─────────────────────────────────────────────┐
│  Static Certificate Values                  │
├─────────────────────────────────────────────┤
│  Event Name:                                │
│  [Web Development Workshop 2025          ]  │
│                                             │
│  Date:                                      │
│  [November 4, 2025                       ]  │
│                                             │
│  Certificate ID Prefix: (auto-generated)    │
│  CERT-2025-                                 │
└─────────────────────────────────────────────┘
```

#### Step 3: Review Registration Form

Preview of what public users will see:
- Shows all fields with "Show in Form" = YES
- Required fields marked with asterisk (*)

**Enable/Disable Registration Toggle:**
- Controls if public registration URL is active

---

### 3. Public Registration Flow

**Public URL:** `/register/{event_slug}`

**Form Display:**
- Only shows fields with `show_in_form` = YES from template
- Validates required fields
- Stores data in `registrations` table as JSON

**Example Form:**

```
┌─────────────────────────────────────────────┐
│  Register for: Web Development Workshop    │
├─────────────────────────────────────────────┤
│  Name: *                                    │
│  [_____________________________________]    │
│                                             │
│  Email: *                                   │
│  [_____________________________________]    │
│                                             │
│  Phone:                                     │
│  [_____________________________________]    │
│                                             │
│          [Submit Registration]              │
└─────────────────────────────────────────────┘
```

---

### 4. Certificate Generation Flow

#### Data Merging Logic

For each certificate, system merges:

**Registration Data** (fields with `show_in_form` = YES):
```json
{
  "name": "Christopher Johnson",
  "email": "chris@example.com",
  "phone": "123-456-7890"
}
```

**Static Event Values** (fields with `show_in_form` = NO, `show_in_cert` = YES):
```json
{
  "event_name": "Web Development Workshop 2025",
  "date": "November 4, 2025",
  "certificate_id": "CERT-2025-000123"
}
```

**Final Certificate Data:**
```json
{
  "name": "Christopher Johnson",
  "event_name": "Web Development Workshop 2025",
  "date": "November 4, 2025",
  "certificate_id": "CERT-2025-000123"
}
```

Note: `email` and `phone` are stored but not shown on certificate (since `show_in_cert` = NO)

---

## Database Schema Changes

### 1. Modify `template_fields` Table

**Add New Columns:**

```php
Schema::table('template_fields', function (Blueprint $table) {
    $table->boolean('show_in_form')->default(true)->after('field_type');
    $table->boolean('show_in_cert')->default(true)->after('show_in_form');
    $table->boolean('is_required')->default(false)->after('show_in_cert');
    $table->boolean('is_predefined')->default(false)->after('is_required');
});
```

**Updated Schema:**
```
template_fields
├─ id (uuid)
├─ template_id (foreign key)
├─ field_name (string)
├─ field_type (string) - text, email, date, number, etc.
├─ show_in_form (boolean) - default true
├─ show_in_cert (boolean) - default true
├─ is_required (boolean) - default false
├─ is_predefined (boolean) - default false (no remove button if true)
├─ x, y, width, height (decimal) - positioning
├─ font_size, font_family, color (string) - styling
├─ text_align (string) - left, center, right
├─ bold, italic (boolean)
├─ rotation (decimal)
├─ timestamps
└─ soft_deletes
```

---

### 2. Create `event_field_values` Table

**Purpose:** Store static values for fields that don't appear in registration form

```php
Schema::create('event_field_values', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
    $table->foreignUuid('template_field_id')->constrained('template_fields')->cascadeOnDelete();
    $table->text('value'); // Static value for this event
    $table->timestamps();

    // Ensure one value per field per event
    $table->unique(['event_id', 'template_field_id']);
});
```

**Schema:**
```
event_field_values
├─ id (uuid)
├─ event_id (foreign key → events)
├─ template_field_id (foreign key → template_fields)
├─ value (text) - The static value
└─ timestamps
```

**Example Data:**
```
| ID | Event ID | Template Field ID | Value |
|----|----------|-------------------|-------|
| 1  | evt-123  | field-abc (event_name) | "Web Dev Workshop 2025" |
| 2  | evt-123  | field-def (date) | "November 4, 2025" |
```

---

### 3. Deprecate `event_fields` Table

**Current `event_fields` table:**
- No longer needed
- Template fields now define everything

**Migration Strategy:**
- Keep table for backward compatibility during transition
- Mark as deprecated in documentation
- Remove in future major version

---

### 4. Update `registrations` Table

**No changes needed** - continues to store JSON data

```
registrations
├─ id (uuid)
├─ event_id (foreign key)
├─ data (json) - Contains values for fields with show_in_form = YES
├─ status (string)
└─ timestamps
```

---

## Field Configuration Rules

### Toggle Combinations

| Show in Form | Show in Cert | Required | Use Case | Example |
|--------------|--------------|----------|----------|---------|
| ☑ | ☑ | ☑ | Dynamic cert field (user fills) | Participant name |
| ☑ | ☑ | ☐ | Optional dynamic field | Middle name |
| ☑ | ☐ | ☑ | Contact info (not on cert) | Email, phone |
| ☑ | ☐ | ☐ | Optional contact info | Additional notes |
| ☐ | ☑ | - | Static cert field | Event name, date |
| ☐ | ☐ | - | Invalid (field serves no purpose) | - |

---

### Predefined Fields

**System provides 6 predefined fields** (cannot be removed):

1. **name** (Text)
   - Default: Show in Form ☑, Show in Cert ☑, Required ☑
   - Purpose: Participant/recipient name

2. **email** (Email)
   - Default: Show in Form ☑, Show in Cert ☐, Required ☑
   - Purpose: Contact email for certificate delivery

3. **phone** (Text)
   - Default: Show in Form ☑, Show in Cert ☐, Required ☐
   - Purpose: Contact phone (optional)

4. **event_name** (Text)
   - Default: Show in Form ☐, Show in Cert ☑
   - Purpose: Event/course/workshop name

5. **date** (Date)
   - Default: Show in Form ☐, Show in Cert ☑
   - Purpose: Event date or completion date

6. **certificate_id** (Text)
   - Default: Show in Form ☐, Show in Cert ☑
   - Purpose: Unique certificate number (auto-generated)

**User can:**
- Toggle "Show in Form" / "Show in Cert" / "Required"
- Modify field properties (font, color, position)
- **Cannot:** Delete these fields

---

### Custom Fields

**User can add unlimited custom fields:**
- Each has a "Remove" button
- All toggle options available
- Useful for specific use cases (organization name, grade, etc.)

---

## UI/UX Changes

### 1. Template Create/Edit Form

#### Field Definition Section

**HTML Structure:**
```html
<div class="card">
    <div class="card-header">
        <h3>Define Template Fields</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Field Name</th>
                    <th>Type</th>
                    <th>Show in Form</th>
                    <th>Show in Cert</th>
                    <th>Required</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="fields-list">
                <!-- Predefined fields (no remove button) -->
                <tr data-field="name" data-predefined="true">
                    <td><input type="text" value="name" readonly /></td>
                    <td>
                        <select name="fields[0][type]">
                            <option value="text" selected>Text</option>
                        </select>
                    </td>
                    <td><input type="checkbox" name="fields[0][show_in_form]" checked /></td>
                    <td><input type="checkbox" name="fields[0][show_in_cert]" checked /></td>
                    <td><input type="checkbox" name="fields[0][is_required]" checked /></td>
                    <td><span class="badge badge-light">Predefined</span></td>
                </tr>
                <!-- More predefined fields... -->

                <!-- Custom fields (with remove button) -->
                <tr data-field="custom-1" data-predefined="false">
                    <td><input type="text" name="fields[6][name]" value="organization" /></td>
                    <td>
                        <select name="fields[6][type]">
                            <option value="text" selected>Text</option>
                        </select>
                    </td>
                    <td><input type="checkbox" name="fields[6][show_in_form]" /></td>
                    <td><input type="checkbox" name="fields[6][show_in_cert]" checked /></td>
                    <td><input type="checkbox" name="fields[6][is_required]" /></td>
                    <td><button class="btn btn-sm btn-danger remove-field">Remove</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-primary" id="add-field-btn">
            <i class="ki-duotone ki-plus"></i> Add Custom Field
        </button>
    </div>
</div>
```

**JavaScript Behavior:**
```javascript
// When "Show in Cert" checkbox changes
$('.show-in-cert-checkbox').on('change', function() {
    const fieldName = $(this).closest('tr').data('field');
    const isChecked = $(this).is(':checked');

    // Show/hide field on canvas
    if (isChecked) {
        addFieldToCanvas(fieldName);
    } else {
        removeFieldFromCanvas(fieldName);
    }
});

// When "Show in Form" is unchecked, disable "Required"
$('.show-in-form-checkbox').on('change', function() {
    const requiredCheckbox = $(this).closest('tr').find('.required-checkbox');
    if (!$(this).is(':checked')) {
        requiredCheckbox.prop('checked', false).prop('disabled', true);
    } else {
        requiredCheckbox.prop('disabled', false);
    }
});
```

---

### 2. Template Canvas (Fabric.js)

**Filter Logic:**
```javascript
// Only show fields with show_in_cert = true
function loadFieldsToCanvas(template) {
    const fieldsToShow = template.fields.filter(field => field.show_in_cert);

    fieldsToShow.forEach(field => {
        // Add to Fabric.js canvas
        addTextFieldToCanvas(field);
    });
}
```

---

### 3. Event Create/Edit Form

#### Static Values Section

**HTML Structure:**
```html
<div class="card" id="static-values-section" style="display: none;">
    <div class="card-header">
        <h3>Configure Static Certificate Values</h3>
        <p class="text-muted">
            These values will be the same for all certificates in this event
        </p>
    </div>
    <div class="card-body" id="static-fields-container">
        <!-- Dynamically loaded based on template -->
    </div>
</div>
```

**JavaScript - Load Static Fields:**
```javascript
$('#template_id').on('change', function() {
    const templateId = $(this).val();

    // Fetch template fields
    $.get(`/api/templates/${templateId}/static-fields`, function(fields) {
        // fields = template fields where show_in_form = false and show_in_cert = true

        let html = '';
        fields.forEach(field => {
            html += `
                <div class="mb-4">
                    <label class="form-label">${field.field_name}</label>
                    ${generateInputField(field)}
                </div>
            `;
        });

        $('#static-fields-container').html(html);
        $('#static-values-section').show();
    });
});

function generateInputField(field) {
    switch(field.field_type) {
        case 'date':
            return `<input type="date" name="static_values[${field.id}]" class="form-control" />`;
        case 'number':
            return `<input type="number" name="static_values[${field.id}]" class="form-control" />`;
        default:
            return `<input type="text" name="static_values[${field.id}]" class="form-control" />`;
    }
}
```

---

### 4. Public Registration Form

**Form Generation Logic:**
```php
// RegistrationController.php
public function show($slug)
{
    $event = Event::where('slug', $slug)->with('template.fields')->firstOrFail();

    // Get only fields with show_in_form = true
    $formFields = $event->template->fields()
        ->where('show_in_form', true)
        ->get();

    return view('registration.form', compact('event', 'formFields'));
}
```

**Blade Template:**
```blade
<form method="POST" action="{{ route('register.store', $event->slug) }}">
    @csrf

    @foreach($formFields as $field)
        <div class="mb-3">
            <label class="form-label">
                {{ ucfirst($field->field_name) }}
                @if($field->is_required) <span class="text-danger">*</span> @endif
            </label>

            @if($field->field_type === 'textarea')
                <textarea
                    name="data[{{ $field->field_name }}]"
                    class="form-control"
                    @if($field->is_required) required @endif
                ></textarea>
            @elseif($field->field_type === 'date')
                <input
                    type="date"
                    name="data[{{ $field->field_name }}]"
                    class="form-control"
                    @if($field->is_required) required @endif
                />
            @else
                <input
                    type="{{ $field->field_type }}"
                    name="data[{{ $field->field_name }}]"
                    class="form-control"
                    @if($field->is_required) required @endif
                />
            @endif
        </div>
    @endforeach

    <button type="submit" class="btn btn-primary">Submit Registration</button>
</form>
```

---

## Backend Logic

### 1. Template Controller

#### Store Method
```php
public function store(Request $request)
{
    // Validate
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'background' => 'required|image|max:5120',
        'fields' => 'required|array',
        'fields.*.field_name' => 'required|string',
        'fields.*.field_type' => 'required|string',
        'fields.*.show_in_form' => 'boolean',
        'fields.*.show_in_cert' => 'boolean',
        'fields.*.is_required' => 'boolean',
        // ... positioning and styling fields
    ]);

    // Create template
    $template = Template::create([
        'name' => $validated['name'],
        'background' => $request->file('background')->store('templates', 'public'),
        'created_by' => auth()->id(),
    ]);

    // Create fields
    foreach ($validated['fields'] as $fieldData) {
        $template->fields()->create($fieldData);
    }

    return redirect()->route('templates.index');
}
```

---

### 2. Event Controller

#### Store Method
```php
public function store(Request $request)
{
    // Validate
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'template_id' => 'required|exists:templates,id',
        'static_values' => 'array',
        'static_values.*' => 'nullable|string',
    ]);

    // Create event
    $event = Event::create([
        'name' => $validated['name'],
        'slug' => Str::slug($validated['name']),
        'template_id' => $validated['template_id'],
        'created_by' => auth()->id(),
    ]);

    // Store static values
    if (isset($validated['static_values'])) {
        foreach ($validated['static_values'] as $fieldId => $value) {
            EventFieldValue::create([
                'event_id' => $event->id,
                'template_field_id' => $fieldId,
                'value' => $value,
            ]);
        }
    }

    return redirect()->route('events.index');
}
```

---

### 3. Certificate Service

#### Generate Method
```php
public function generateCertificate(Event $event, Registration $registration)
{
    // Get template fields that appear on certificate
    $templateFields = $event->template->fields()
        ->where('show_in_cert', true)
        ->get();

    // Merge data sources
    $certificateData = [];

    foreach ($templateFields as $field) {
        if ($field->show_in_form) {
            // Get value from registration data
            $certificateData[$field->field_name] = $registration->data[$field->field_name] ?? '';
        } else {
            // Get value from event static values
            $staticValue = EventFieldValue::where('event_id', $event->id)
                ->where('template_field_id', $field->id)
                ->first();

            $certificateData[$field->field_name] = $staticValue?->value ?? '';
        }
    }

    // Auto-generate certificate_id if not set
    if (isset($certificateData['certificate_id']) && empty($certificateData['certificate_id'])) {
        $certificateData['certificate_id'] = $this->generateCertificateNumber();
    }

    // Generate PDF
    $pdf = $this->generatePDF($event->template, $certificateData);

    // Generate QR code
    $qrCode = $this->generateQRCode($certificateData['certificate_id']);

    // Store certificate
    return Certificate::create([
        'event_id' => $event->id,
        'registration_id' => $registration->id,
        'certificate_number' => $certificateData['certificate_id'],
        'pdf_path' => $pdf,
        'qr_code' => $qrCode,
        'generated_by' => auth()->id(),
    ]);
}
```

---

## Migration Strategy

### Option 1: Fresh Migration (Recommended for Development)

**Create new migration:**
`2025_11_04_update_template_fields_add_toggles.php`

```php
public function up()
{
    Schema::table('template_fields', function (Blueprint $table) {
        $table->boolean('show_in_form')->default(true)->after('field_type');
        $table->boolean('show_in_cert')->default(true)->after('show_in_form');
        $table->boolean('is_required')->default(false)->after('show_in_cert');
        $table->boolean('is_predefined')->default(false)->after('is_required');
    });
}
```

**Create new migration:**
`2025_11_04_create_event_field_values_table.php`

```php
public function up()
{
    Schema::create('event_field_values', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
        $table->foreignUuid('template_field_id')->constrained('template_fields')->cascadeOnDelete();
        $table->text('value');
        $table->timestamps();

        $table->unique(['event_id', 'template_field_id']);
    });
}
```

**Run:**
```bash
php artisan migrate:fresh --seed
```

---

### Option 2: Data Migration (Production)

**Step 1: Add columns**
```bash
php artisan migrate
```

**Step 2: Migrate existing data**
```php
// Database seeder or artisan command
public function migrateExistingTemplates()
{
    $templates = Template::with('fields')->get();

    foreach ($templates as $template) {
        foreach ($template->fields as $field) {
            // Set defaults
            $field->update([
                'show_in_form' => true,
                'show_in_cert' => true,
                'is_required' => false,
                'is_predefined' => in_array($field->field_name, [
                    'name', 'email', 'phone', 'event_name', 'date', 'certificate_id'
                ]),
            ]);
        }
    }
}
```

**Step 3: Migrate event fields to static values**
```php
public function migrateEventFields()
{
    $events = Event::with('fields', 'template.fields')->get();

    foreach ($events as $event) {
        // Match event_fields to template_fields
        foreach ($event->fields as $eventField) {
            $templateField = $event->template->fields()
                ->where('field_name', $eventField->field_name)
                ->first();

            if ($templateField) {
                // This was in event form, so it should show in form
                $templateField->update(['show_in_form' => true]);
            }
        }
    }
}
```

---

## Examples & Use Cases

### Example 1: Workshop Certificate

**Template: "Workshop Certificate"**

| Field Name | Type | Show in Form | Show in Cert | Required | Notes |
|------------|------|--------------|--------------|----------|-------|
| name | Text | ☑ | ☑ | ☑ | Each participant enters |
| email | Email | ☑ | ☐ | ☑ | For delivery |
| phone | Text | ☑ | ☐ | ☐ | Optional contact |
| event_name | Text | ☐ | ☑ | - | Set per event |
| date | Date | ☐ | ☑ | - | Set per event |
| certificate_id | Text | ☐ | ☑ | - | Auto-generated |

**Event: "Web Development 2025"**

Static values:
- event_name: "Web Development Workshop 2025"
- date: "November 4, 2025"

**Registration Form (Public):**
- Name: [_____] *
- Email: [_____] *
- Phone: [_____]

**Certificate Shows:**
- Christopher Johnson (from registration)
- Web Development Workshop 2025 (from event)
- November 4, 2025 (from event)
- CERT-2025-000123 (auto-generated)

---

### Example 2: Academic Certificate with Grade

**Template: "Course Completion"**

| Field Name | Type | Show in Form | Show in Cert | Required | Notes |
|------------|------|--------------|--------------|----------|-------|
| name | Text | ☑ | ☑ | ☑ | Student name |
| email | Email | ☑ | ☐ | ☑ | Contact |
| grade | Select | ☑ | ☑ | ☑ | A, B, C, D, F |
| course_name | Text | ☐ | ☑ | - | Static |
| instructor | Text | ☐ | ☑ | - | Static |
| date | Date | ☐ | ☑ | - | Static |

**Event: "Advanced JavaScript"**

Static values:
- course_name: "Advanced JavaScript Programming"
- instructor: "Dr. Jane Smith"
- date: "December 15, 2025"

**Registration + Grade Entry:**
- Name: [_____] *
- Email: [_____] *
- Grade: [A ▼] * (dropdown)

**Certificate Shows:**
- Student Name
- Advanced JavaScript Programming
- Grade: A
- Instructor: Dr. Jane Smith
- December 15, 2025

---

### Example 3: Complex Event with Organization

**Template: "Professional Training"**

| Field Name | Type | Show in Form | Show in Cert | Required | Notes |
|------------|------|--------------|--------------|----------|-------|
| name | Text | ☑ | ☑ | ☑ | Participant |
| email | Email | ☑ | ☐ | ☑ | Contact |
| organization | Text | ☑ | ☑ | ☑ | Company name |
| job_title | Text | ☑ | ☐ | ☐ | For records |
| event_name | Text | ☐ | ☑ | - | Static |
| trainer | Text | ☐ | ☑ | - | Static |
| hours | Number | ☐ | ☑ | - | Static |
| date | Date | ☐ | ☑ | - | Static |

**Event: "Project Management Training"**

Static values:
- event_name: "Project Management Professional Training"
- trainer: "Sarah Johnson, PMP"
- hours: "40"
- date: "October 2025"

**Registration Form:**
- Name: [_____] *
- Email: [_____] *
- Organization: [_____] *
- Job Title: [_____]

**Certificate Shows:**
- Christopher Johnson (from registration)
- ABC Corporation (from registration)
- Project Management Professional Training (static)
- Sarah Johnson, PMP (static)
- 40 Hours (static)
- October 2025 (static)

---

## Implementation Checklist

### Phase 1: Database
- [ ] Create migration for `template_fields` new columns
- [ ] Create migration for `event_field_values` table
- [ ] Run migrations
- [ ] Update models (Template, TemplateField, Event, EventFieldValue)
- [ ] Add relationships between models

### Phase 2: Template System
- [ ] Update template create form (field definition table)
- [ ] Add predefined fields with no remove button
- [ ] Add "Add Custom Field" functionality
- [ ] Update canvas to filter show_in_cert fields only
- [ ] Update save logic to include new columns

### Phase 3: Event System
- [ ] Update event create form
- [ ] Add template selector with field loading
- [ ] Add static values section (dynamic based on template)
- [ ] Update EventController store method
- [ ] Store static values in event_field_values table

### Phase 4: Registration
- [ ] Update public registration form generation
- [ ] Filter fields by show_in_form = true
- [ ] Apply is_required validation
- [ ] Test form submission

### Phase 5: Certificate Generation
- [ ] Update CertificateService
- [ ] Implement data merging logic (registration + static)
- [ ] Handle certificate_id auto-generation
- [ ] Update PDF generation to use merged data
- [ ] Test with various field combinations

### Phase 6: Testing
- [ ] Test template creation with various field configs
- [ ] Test event creation with static values
- [ ] Test public registration form
- [ ] Test certificate generation
- [ ] Test edge cases (all static, all dynamic, mixed)

---

## Notes

- Keep backward compatibility during transition period
- Validate that at least one field has show_in_cert = true (template must show something)
- Consider adding field order/sort for form display
- Future enhancement: Field dependencies (show field X only if field Y = value)

---

**Document Version:** 1.0
**Created:** November 4, 2025
**Status:** Planning Phase
