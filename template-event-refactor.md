# Template-Event System Refactor


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
| event_name | Text | ☐ | ☑ | - | - (Predefined) |
| date | Date | ☐ | ☑ | - | - (Predefined) |
| custom_field | Text | ☑ | ☑ | ☐ | [Remove] |

**Predefined Fields** (No remove button):
- `name` - Participant name
- `email` - Contact email
- `event_name` - Event/course name
- `date` - Event/completion date

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

#### Step 3: Position Fields on Canvas

**Canvas Behavior:**
- Only fields with "Show in Cert" = ☑ appear on Fabric.js canvas
- remain the same with current

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

**Public URL:** `/register/{id}`

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

## 4. Certificate Generation

**Process:**
1. Fetch registration data (form fields)
2. Fetch event static values (non-form cert fields)
3. Merge both datasets
4. Apply to template positioning
5. Generate PDF using certificate background + positioned text

**Data Merging Example:**
```php
// Registration data (from form)
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "123-456-7890"
}

// Event static values
{
    "event_name": "Web Development Workshop 2025",
    "date": "November 4, 2025"
}

// Merged certificate data
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "123-456-7890",
    "event_name": "Web Development Workshop 2025",
    "date": "November 4, 2025"
}
```

---

## Technical Implementation Plan

### Phase 1: Database Schema & Models (Week 1)

#### 1.1 Create `template_fields` Migration

**File:** `database/migrations/YYYY_MM_DD_create_template_fields_table.php`

```sql
Schema::create('template_fields', function (Blueprint $table) {
    $table->id();
    $table->foreignId('template_id')->constrained()->onDelete('cascade');
    $table->string('field_name'); // 'name', 'email', 'event_name', etc.
    $table->string('field_label')->nullable(); // Display label
    $table->enum('field_type', ['text', 'email', 'date', 'number', 'textarea']);
    $table->boolean('show_in_form')->default(true);
    $table->boolean('show_in_cert')->default(true);
    $table->boolean('is_required')->default(false);
    $table->boolean('is_predefined')->default(false); // Can't be deleted
    $table->json('position_data')->nullable(); // x, y, fontSize, fontFamily, etc.
    $table->integer('order')->default(0); // Display order
    $table->timestamps();
    
    // Ensure unique field names per template
    $table->unique(['template_id', 'field_name']);
});
```

#### 1.2 Modify `events` Table Migration

**File:** `database/migrations/YYYY_MM_DD_add_static_values_to_events.php`

```sql
Schema::table('events', function (Blueprint $table) {
    $table->json('static_values')->nullable()->after('template_id');
    // Store: {"event_name": "Workshop 2025", "date": "2025-11-04"}
});
```

#### 1.3 Create `TemplateField` Model

**File:** `app/Models/TemplateField.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateField extends Model
{
    protected $fillable = [
        'template_id',
        'field_name',
        'field_label',
        'field_type',
        'show_in_form',
        'show_in_cert',
        'is_required',
        'is_predefined',
        'position_data',
        'order'
    ];

    protected $casts = [
        'show_in_form' => 'boolean',
        'show_in_cert' => 'boolean',
        'is_required' => 'boolean',
        'is_predefined' => 'boolean',
        'position_data' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    // Scope: Only fields shown on certificate
    public function scopeCertificateFields($query)
    {
        return $query->where('show_in_cert', true);
    }

    // Scope: Only fields shown in registration form
    public function scopeFormFields($query)
    {
        return $query->where('show_in_form', true);
    }

    // Scope: Fields that need static values in events
    public function scopeStaticValueFields($query)
    {
        return $query->where('show_in_form', false)
                     ->where('show_in_cert', true);
    }
}
```

#### 1.4 Update `Template` Model

**File:** `app/Models/Template.php`

```php
// Add to existing Template model

public function fields()
{
    return $this->hasMany(TemplateField::class)->orderBy('order');
}

public function formFields()
{
    return $this->fields()->formFields();
}

public function certFields()
{
    return $this->fields()->certificateFields();
}

public function staticValueFields()
{
    return $this->fields()->staticValueFields();
}

// Initialize predefined fields when creating template
public static function boot()
{
    parent::boot();
    
    static::created(function ($template) {
        $predefinedFields = [
            [
                'field_name' => 'name',
                'field_label' => 'Participant Name',
                'field_type' => 'text',
                'show_in_form' => true,
                'show_in_cert' => true,
                'is_required' => true,
                'is_predefined' => true,
                'order' => 1
            ],
            [
                'field_name' => 'email',
                'field_label' => 'Email Address',
                'field_type' => 'email',
                'show_in_form' => true,
                'show_in_cert' => false,
                'is_required' => true,
                'is_predefined' => true,
                'order' => 2
            ],
            [
                'field_name' => 'event_name',
                'field_label' => 'Event Name',
                'field_type' => 'text',
                'show_in_form' => false,
                'show_in_cert' => true,
                'is_required' => false,
                'is_predefined' => true,
                'order' => 3
            ],
            [
                'field_name' => 'date',
                'field_label' => 'Event Date',
                'field_type' => 'date',
                'show_in_form' => false,
                'show_in_cert' => true,
                'is_required' => false,
                'is_predefined' => true,
                'order' => 4
            ]
        ];
        
        foreach ($predefinedFields as $field) {
            $template->fields()->create($field);
        }
    });
}
```

#### 1.5 Update `Event` Model

**File:** `app/Models/Event.php`

```php
// Add to existing Event model

protected $casts = [
    'static_values' => 'array',
];

public function template()
{
    return $this->belongsTo(Template::class);
}

// Get certificate data for a registration
public function getCertificateData(Registration $registration)
{
    return array_merge(
        $this->static_values ?? [],
        $registration->form_data ?? []
    );
}

// Get fields that need static values
public function getStaticValueFields()
{
    if (!$this->template) {
        return collect();
    }
    
    return $this->template->staticValueFields;
}

// Validate static values against template
public function validateStaticValues(array $staticValues)
{
    $requiredFields = $this->getStaticValueFields()
        ->pluck('field_name')
        ->toArray();
    
    foreach ($requiredFields as $field) {
        if (!isset($staticValues[$field]) || empty($staticValues[$field])) {
            throw new \Exception("Static value for '{$field}' is required.");
        }
    }
    
    return true;
}
```

#### 1.6 Update `Registration` Model

**File:** `app/Models/Registration.php`

```php
// Add to existing Registration model

protected $casts = [
    'form_data' => 'array',
];

// Validate registration data against template fields
public function validateAgainstTemplate()
{
    if (!$this->event || !$this->event->template) {
        throw new \Exception('Event or template not found.');
    }
    
    $formFields = $this->event->template->formFields;
    $formData = $this->form_data ?? [];
    
    foreach ($formFields as $field) {
        if ($field->is_required) {
            if (!isset($formData[$field->field_name]) || empty($formData[$field->field_name])) {
                throw new \Exception("Field '{$field->field_label}' is required.");
            }
        }
    }
    
    return true;
}

// Get complete certificate data (form data + static values)
public function getCertificateData()
{
    if (!$this->event) {
        throw new \Exception('Event not found.');
    }
    
    return $this->event->getCertificateData($this);
}
```

---

### Phase 2: Backend Services (Week 2)

#### 2.1 Create `TemplateFieldService`

**File:** `app/Services/TemplateFieldService.php`

```php
<?php

namespace App\Services;

use App\Models\Template;
use App\Models\TemplateField;

class TemplateFieldService
{
    /**
     * Add custom field to template
     */
    public function addCustomField(Template $template, array $fieldData)
    {
        // Validate field name is unique
        $exists = $template->fields()
            ->where('field_name', $fieldData['field_name'])
            ->exists();
            
        if ($exists) {
            throw new \Exception('Field name already exists in this template.');
        }
        
        // Get next order number
        $maxOrder = $template->fields()->max('order');
        $fieldData['order'] = $maxOrder + 1;
        $fieldData['is_predefined'] = false;
        
        return $template->fields()->create($fieldData);
    }
    
    /**
     * Update field properties (toggles, type, etc.)
     */
    public function updateField(TemplateField $field, array $data)
    {
        // Prevent changing predefined field names
        if ($field->is_predefined && isset($data['field_name'])) {
            unset($data['field_name']);
        }
        
        $field->update($data);
        return $field->fresh();
    }
    
    /**
     * Update field position on canvas
     */
    public function updateFieldPosition(TemplateField $field, array $positionData)
    {
        $field->position_data = $positionData;
        $field->save();
        
        return $field;
    }
    
    /**
     * Delete custom field (cannot delete predefined)
     */
    public function deleteField(TemplateField $field)
    {
        if ($field->is_predefined) {
            throw new \Exception('Cannot delete predefined field.');
        }
        
        return $field->delete();
    }
    
    /**
     * Reorder fields
     */
    public function reorderFields(Template $template, array $fieldOrder)
    {
        // $fieldOrder = [field_id => new_order]
        foreach ($fieldOrder as $fieldId => $order) {
            $template->fields()
                ->where('id', $fieldId)
                ->update(['order' => $order]);
        }
    }
    
    /**
     * Get fields for canvas (show_in_cert = true)
     */
    public function getCanvasFields(Template $template)
    {
        return $template->certFields()
            ->orderBy('order')
            ->get();
    }
    
    /**
     * Get fields for registration form (show_in_form = true)
     */
    public function getFormFields(Template $template)
    {
        return $template->formFields()
            ->orderBy('order')
            ->get();
    }
}
```

#### 2.2 Create `EventConfigurationService`

**File:** `app/Services/EventConfigurationService.php`

```php
<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Template;

class EventConfigurationService
{
    /**
     * Get static value fields for event based on template
     */
    public function getStaticValueFields(Template $template)
    {
        return $template->staticValueFields()
            ->orderBy('order')
            ->get();
    }
    
    /**
     * Validate and save static values
     */
    public function saveStaticValues(Event $event, array $staticValues)
    {
        // Validate all required static fields are provided
        $event->validateStaticValues($staticValues);
        
        $event->static_values = $staticValues;
        $event->save();
        
        return $event;
    }
    
    /**
     * Get preview of registration form
     */
    public function getRegistrationFormPreview(Event $event)
    {
        if (!$event->template) {
            throw new \Exception('Event template not found.');
        }
        
        return $event->template->formFields()
            ->orderBy('order')
            ->get()
            ->map(function ($field) {
                return [
                    'name' => $field->field_name,
                    'label' => $field->field_label,
                    'type' => $field->field_type,
                    'required' => $field->is_required,
                ];
            });
    }
}
```

#### 2.3 Update `CertificateService`

**File:** `app/Services/CertificateService.php`

```php
<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\Certificate;

class CertificateService
{
    /**
     * Generate certificate for registration
     */
    public function generateCertificate(Registration $registration)
    {
        // Get merged data (registration + static values)
        $certificateData = $registration->getCertificateData();
        
        // Get template with field positions
        $template = $registration->event->template;
        
        if (!$template) {
            throw new \Exception('Template not found.');
        }
        
        // Get certificate fields with positions
        $certFields = $template->certFields;
        
        // Generate PDF using template background + positioned data
        $pdfPath = $this->renderCertificatePDF($template, $certFields, $certificateData);
        
        // Create certificate record
        $certificate = Certificate::create([
            'registration_id' => $registration->id,
            'event_id' => $registration->event_id,
            'certificate_number' => $this->generateCertificateNumber($registration->event),
            'certificate_data' => $certificateData,
            'pdf_path' => $pdfPath,
            'issued_at' => now(),
        ]);
        
        return $certificate;
    }
    
    /**
     * Render PDF with positioned fields
     */
    private function renderCertificatePDF($template, $fields, $data)
    {
        // Use existing PDF library (DomPDF, TCPDF, etc.)
        // Position text on certificate background based on field position_data
        
        $pdf = app('dompdf.wrapper');
        $html = view('pdf.certificate', [
            'template' => $template,
            'fields' => $fields,
            'data' => $data
        ])->render();
        
        $pdf->loadHTML($html);
        $filename = 'certificate_' . uniqid() . '.pdf';
        $path = storage_path('app/certificates/' . $filename);
        
        $pdf->save($path);
        
        return 'certificates/' . $filename;
    }
    
    /**
     * Generate unique certificate number
     */
    private function generateCertificateNumber($event)
    {
        $prefix = 'CERT-' . date('Y') . '-';
        $count = Certificate::where('event_id', $event->id)->count() + 1;
        
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Bulk generate certificates for event
     */
    public function bulkGenerateCertificates(Event $event)
    {
        $registrations = $event->registrations()
            ->whereDoesntHave('certificate')
            ->get();
        
        $generated = [];
        
        foreach ($registrations as $registration) {
            try {
                $certificate = $this->generateCertificate($registration);
                $generated[] = $certificate;
            } catch (\Exception $e) {
                \Log::error('Certificate generation failed: ' . $e->getMessage());
            }
        }
        
        return $generated;
    }
}
```

---

### Phase 3: Controller Updates (Week 3)

#### 3.1 Update `TemplateController`

**Key Changes:**
```php
// Create template with predefined fields auto-initialized
public function store(Request $request)
{
    $template = Template::create([
        'name' => $request->name,
        'background_image' => $request->file('background')->store('templates'),
        'is_default' => $request->is_default ?? false,
    ]);
    
    // Predefined fields already created via Template::boot()
    
    return response()->json($template->load('fields'));
}

// Add custom field
public function addField(Request $request, Template $template)
{
    $fieldService = app(TemplateFieldService::class);
    
    $field = $fieldService->addCustomField($template, $request->validated());
    
    return response()->json($field);
}

// Update field toggles/properties
public function updateField(Request $request, TemplateField $field)
{
    $fieldService = app(TemplateFieldService::class);
    
    $field = $fieldService->updateField($field, $request->validated());
    
    return response()->json($field);
}

// Update field position on canvas
public function updateFieldPosition(Request $request, TemplateField $field)
{
    $fieldService = app(TemplateFieldService::class);
    
    $field = $fieldService->updateFieldPosition($field, $request->position_data);
    
    return response()->json($field);
}

// Delete custom field
public function deleteField(TemplateField $field)
{
    $fieldService = app(TemplateFieldService::class);
    
    $fieldService->deleteField($field);
    
    return response()->json(['message' => 'Field deleted']);
}

// Get canvas fields only
public function getCanvasFields(Template $template)
{
    $fieldService = app(TemplateFieldService::class);
    
    return response()->json($fieldService->getCanvasFields($template));
}
```

#### 3.2 Update `EventController`

**Key Changes:**
```php
// Show create form with template selection
public function create()
{
    $templates = Template::with('fields')->get();
    
    return view('events.create', compact('templates'));
}

// Store event with static values
public function store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'description' => 'required',
        'template_id' => 'required|exists:templates,id',
        'static_values' => 'required|array',
    ]);
    
    $event = Event::create([
        'name' => $request->name,
        'description' => $request->description,
        'slug' => Str::slug($request->slug ?? $request->name),
        'template_id' => $request->template_id,
    ]);
    
    // Save static values
    $configService = app(EventConfigurationService::class);
    $configService->saveStaticValues($event, $request->static_values);
    
    return redirect()->route('events.show', $event);
}

// Get static value fields via AJAX when template is selected
public function getStaticValueFields(Template $template)
{
    $configService = app(EventConfigurationService::class);
    
    return response()->json($configService->getStaticValueFields($template));
}

// Get registration form preview
public function getRegistrationFormPreview(Event $event)
{
    $configService = app(EventConfigurationService::class);
    
    return response()->json($configService->getRegistrationFormPreview($event));
}
```

#### 3.3 Update `RegistrationController`

**Key Changes:**
```php
// Show public registration form
public function create($eventSlug)
{
    $event = Event::where('slug', $eventSlug)
        ->with(['template.formFields'])
        ->firstOrFail();
    
    if (!$event->is_registration_active) {
        abort(403, 'Registration is currently closed.');
    }
    
    $formFields = $event->template->formFields()
        ->orderBy('order')
        ->get();
    
    return view('registrations.create', compact('event', 'formFields'));
}

// Store registration with validation
public function store(Request $request, $eventSlug)
{
    $event = Event::where('slug', $eventSlug)->firstOrFail();
    
    if (!$event->is_registration_active) {
        abort(403, 'Registration is currently closed.');
    }
    
    // Build dynamic validation rules from template fields
    $rules = [];
    $formFields = $event->template->formFields;
    
    foreach ($formFields as $field) {
        $fieldRules = [];
        
        if ($field->is_required) {
            $fieldRules[] = 'required';
        }
        
        // Add type-specific validation
        if ($field->field_type === 'email') {
            $fieldRules[] = 'email';
        } elseif ($field->field_type === 'date') {
            $fieldRules[] = 'date';
        } elseif ($field->field_type === 'number') {
            $fieldRules[] = 'numeric';
        }
        
        $rules[$field->field_name] = implode('|', $fieldRules);
    }
    
    $validated = $request->validate($rules);
    
    // Create registration
    $registration = Registration::create([
        'event_id' => $event->id,
        'form_data' => $validated,
    ]);
    
    return redirect()->route('registrations.success', $registration->id);
}
```

#### 3.4 Update `CertificateController`

**Key Changes:**
```php
// Generate single certificate
public function generate(Registration $registration)
{
    $certificateService = app(CertificateService::class);
    
    $certificate = $certificateService->generateCertificate($registration);
    
    return response()->json($certificate);
}

// Generate all certificates for event
public function bulkGenerate(Event $event)
{
    $certificateService = app(CertificateService::class);
    
    $certificates = $certificateService->bulkGenerateCertificates($event);
    
    return response()->json([
        'message' => count($certificates) . ' certificates generated',
        'certificates' => $certificates
    ]);
}

// Download certificate
public function download(Certificate $certificate)
{
    return response()->download(storage_path('app/' . $certificate->pdf_path));
}
```

---

### Phase 4: Frontend Implementation (Week 4)

#### 4.1 Template Builder UI

**File:** `resources/views/templates/create.blade.php` (or Vue component)

**Components Needed:**
1. **Field Definition Table**
   - Display predefined fields (no remove button)
   - Display custom fields (with remove button)
   - Add new field button
   - Checkboxes for: Show in Form, Show in Cert, Required
   - Field type dropdown

2. **Canvas Area (Fabric.js)**
   - Load only fields with `show_in_cert = true`
   - Allow drag-drop positioning
   - Font size/family controls
   - Save positions to `position_data`

3. **JavaScript Logic:**
```javascript
// When toggles change, sync with canvas
document.querySelectorAll('.show-in-cert-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const fieldName = this.dataset.fieldName;
        const showInCert = this.checked;
        
        if (showInCert) {
            addFieldToCanvas(fieldName);
        } else {
            removeFieldFromCanvas(fieldName);
        }
    });
});

// Save field positions
canvas.on('object:modified', function(e) {
    const field = e.target;
    const fieldId = field.fieldId;
    
    saveFieldPosition(fieldId, {
        x: field.left,
        y: field.top,
        fontSize: field.fontSize,
        fontFamily: field.fontFamily,
        color: field.fill
    });
});
```

#### 4.2 Event Creation UI

**File:** `resources/views/events/create.blade.php`

**Components Needed:**
1. **Basic Information Form**
   - Event name, description, slug
   - Template dropdown

2. **Static Values Form (AJAX-loaded)**
```javascript
// When template is selected
document.getElementById('template_id').addEventListener('change', function() {
    const templateId = this.value;
    
    fetch(`/api/templates/${templateId}/static-value-fields`)
        .then(res => res.json())
        .then(fields => {
            renderStaticValueForm(fields);
        });
});

function renderStaticValueForm(fields) {
    const container = document.getElementById('static-values-container');
    container.innerHTML = '';
    
    fields.forEach(field => {
        const input = createInputElement(field);
        container.appendChild(input);
    });
}
```

3. **Registration Form Preview**
```javascript
// Show preview of public form
fetch(`/api/events/${eventId}/registration-form-preview`)
    .then(res => res.json())
    .then(fields => {
        renderFormPreview(fields);
    });
```

#### 4.3 Public Registration Form

**File:** `resources/views/registrations/create.blade.php`

**Dynamic Form Rendering:**
```blade
<form method="POST" action="{{ route('registrations.store', $event->slug) }}">
    @csrf
    
    <h2>Register for: {{ $event->name }}</h2>
    
    @foreach($formFields as $field)
        <div class="form-group">
            <label for="{{ $field->field_name }}">
                {{ $field->field_label }}
                @if($field->is_required) <span class="required">*</span> @endif
            </label>
            
            @if($field->field_type === 'textarea')
                <textarea 
                    name="{{ $field->field_name }}" 
                    id="{{ $field->field_name }}"
                    {{ $field->is_required ? 'required' : '' }}
                ></textarea>
            @else
                <input 
                    type="{{ $field->field_type }}" 
                    name="{{ $field->field_name }}"
                    id="{{ $field->field_name }}"
                    {{ $field->is_required ? 'required' : '' }}
                >
            @endif
            
            @error($field->field_name)
                <span class="error">{{ $message }}</span>
            @enderror
        </div>
    @endforeach
    
    <button type="submit">Submit Registration</button>
</form>
```

---

## Timeline Summary

| Phase | Duration | Key Deliverables |
|-------|----------|------------------|
| Phase 1: Database & Models | 3-5 days | Migrations, models, relationships |
| Phase 2: Backend Services | 5-7 days | Service classes, business logic |
| Phase 3: Controllers | 4-6 days | API endpoints, controller updates |
| Phase 4: Frontend | 7-10 days | UI components, JavaScript logic |

**TOTAL ESTIMATED TIME: 3-4 weeks (fresh database, no migration needed)**

---

## Implementation Checklist

### Phase 1: Database & Models ✅
- [ ] Create `template_fields` migration
- [ ] Add `static_values` column to `events` table
- [ ] Create `TemplateField` model with scopes
- [ ] Update `Template` model with relationships and boot() method
- [ ] Update `Event` model with static_values casting and methods
- [ ] Update `Registration` model with validation methods
- [ ] Test all model relationships in Tinker

### Phase 2: Backend Services ✅
- [ ] Create `TemplateFieldService.php`
  - [ ] addCustomField()
  - [ ] updateField()
  - [ ] updateFieldPosition()
  - [ ] deleteField()
  - [ ] getCanvasFields()
  - [ ] getFormFields()
- [ ] Create `EventConfigurationService.php`
  - [ ] getStaticValueFields()
  - [ ] saveStaticValues()
  - [ ] getRegistrationFormPreview()
- [ ] Update `CertificateService.php`
  - [ ] generateCertificate() with data merging
  - [ ] renderCertificatePDF()
  - [ ] bulkGenerateCertificates()

### Phase 3: Controllers ✅
- [ ] Update `TemplateController`
  - [ ] store() - create with predefined fields
  - [ ] addField() - add custom field
  - [ ] updateField() - update field properties
  - [ ] updateFieldPosition() - save canvas positions
  - [ ] deleteField() - remove custom field
  - [ ] getCanvasFields() - fetch cert fields
- [ ] Update `EventController`
  - [ ] create() - show template selection
  - [ ] store() - save with static values
  - [ ] getStaticValueFields() - AJAX endpoint
  - [ ] getRegistrationFormPreview() - form preview
- [ ] Update `RegistrationController`
  - [ ] create() - dynamic form rendering
  - [ ] store() - dynamic validation
- [ ] Update `CertificateController`
  - [ ] generate() - single certificate
  - [ ] bulkGenerate() - all certificates
  - [ ] download() - PDF download

### Phase 4: Frontend ✅
- [ ] **Template Builder Page**
  - [ ] Field definition table component
  - [ ] Add custom field form
  - [ ] Toggle switches (Show in Form, Show in Cert, Required)
  - [ ] Fabric.js canvas integration
  - [ ] Sync table checkboxes with canvas
  - [ ] Save field positions on drag
  - [ ] Delete custom field button
- [ ] **Event Creation Page**
  - [ ] Template dropdown
  - [ ] AJAX load static value fields
  - [ ] Dynamic static values form
  - [ ] Registration form preview panel
  - [ ] Enable/Disable registration toggle
- [ ] **Public Registration Page**
  - [ ] Dynamic form field rendering
  - [ ] Field type handling (text, email, date, textarea, number)
  - [ ] Client-side validation
  - [ ] Required field indicators
- [ ] **Certificate Preview/Download**
  - [ ] Certificate list with download links
  - [ ] Bulk generation button
  - [ ] Generation status indicators

---

## Quick Start Guide

### Step 1: Run Migrations (Day 1)
```bash
php artisan make:migration create_template_fields_table
php artisan make:migration add_static_values_to_events_table
# Edit migrations as per Phase 1 specifications
php artisan migrate
```

### Step 2: Create Models (Day 1-2)
```bash
php artisan make:model TemplateField
# Update Template, Event, Registration models as per Phase 1
```

### Step 3: Create Services (Day 3-5)
```bash
php artisan make:service TemplateFieldService
php artisan make:service EventConfigurationService
# Update CertificateService
```

### Step 4: Update Controllers (Day 6-9)
```bash
# Update existing controllers with new methods
# Add routes in routes/web.php or routes/api.php
```

### Step 5: Build Frontend (Day 10-19)
```bash
# Update Blade templates
# Add JavaScript for dynamic forms
# Integrate Fabric.js canvas updates
npm run dev
```

---

## Testing Strategy

### Manual Testing Checklist
- [ ] Create template with predefined fields
- [ ] Add custom field to template
- [ ] Toggle "Show in Form" and verify canvas updates
- [ ] Toggle "Show in Cert" and verify form preview
- [ ] Position fields on canvas and save
- [ ] Create event and select template
- [ ] Fill static values (event_name, date)
- [ ] Preview registration form
- [ ] Submit public registration
- [ ] Generate certificate and verify data merge
- [ ] Download certificate PDF

### Test in Laravel Tinker
```php
// Test Template creation with auto fields
$template = Template::create(['name' => 'Test Template', 'background_image' => 'test.jpg']);
$template->fields; // Should have 4 predefined fields

// Test field relationships
$template->formFields; // Only show_in_form = true
$template->certFields; // Only show_in_cert = true
$template->staticValueFields; // show_in_form=false, show_in_cert=true

// Test Event with static values
$event = Event::create([
    'name' => 'Workshop',
    'template_id' => $template->id,
    'static_values' => ['event_name' => 'Laravel Workshop', 'date' => '2025-11-04']
]);

// Test data merge
$registration = Registration::create([
    'event_id' => $event->id,
    'form_data' => ['name' => 'John', 'email' => 'john@example.com']
]);
$registration->getCertificateData(); // Should merge static + form data
```

---

## API Endpoints Reference

### Template Management
```
POST   /api/templates                    - Create template
POST   /api/templates/{id}/fields        - Add custom field
PATCH  /api/template-fields/{id}         - Update field properties
PATCH  /api/template-fields/{id}/position - Update field position
DELETE /api/template-fields/{id}         - Delete custom field
GET    /api/templates/{id}/canvas-fields - Get certificate fields
```

### Event Management
```
POST   /api/events                              - Create event
GET    /api/templates/{id}/static-value-fields  - Get static fields
GET    /api/events/{id}/registration-preview    - Preview form
```

### Registration
```
GET    /register/{slug}                   - Public registration form
POST   /register/{slug}                   - Submit registration
```

### Certificate
```
POST   /api/certificates/generate/{registration_id}  - Generate one
POST   /api/certificates/bulk/{event_id}             - Generate all
GET    /api/certificates/{id}/download               - Download PDF
```

---

## Key Design Decisions

### Why JSON for `static_values` and `form_data`?
- **Flexibility**: Field structure can change without schema changes
- **Template-driven**: Data structure adapts to template fields
- **Simple queries**: Can cast to array in Laravel easily

### Why separate `show_in_form` and `show_in_cert`?
- **Maximum flexibility**: Some fields only for certificate (event_name, date)
- **Some fields only for data** (email) - not shown on certificate
- **Some fields both** (participant name)

### Why predefined fields can't be deleted?
- **Data integrity**: Ensures core fields always exist
- **Prevents errors**: Certificate generation expects these fields
- **User safety**: Can't accidentally break system

### Why auto-initialize predefined fields in Template::boot()?
- **Developer experience**: No manual field creation needed
- **Consistency**: Every template has same base fields
- **Less error-prone**: Can't forget to add essential fields

---

## Common Gotchas & Solutions

### Issue: Canvas not updating when toggling "Show in Cert"
**Solution:** Add event listener to checkboxes that calls `addFieldToCanvas()` or `removeFieldFromCanvas()`

### Issue: Validation fails on registration form
**Solution:** Check that field names match between template fields and form input names

### Issue: Certificate shows empty values
**Solution:** Verify static_values are saved in event and form_data in registration

### Issue: Can't delete predefined field
**Solution:** This is by design - predefined fields have `is_predefined = true`

### Issue: Field positions not saving
**Solution:** Ensure `updateFieldPosition()` is called on `object:modified` Fabric.js event

---

## Success Criteria

### Phase 1 Complete When:
✅ All migrations run without errors  
✅ Models have correct relationships  
✅ Can create template and see 4 predefined fields in database  
✅ Tinker tests pass  

### Phase 2 Complete When:
✅ All service classes created  
✅ Can add/update/delete custom fields via services  
✅ Static value validation works  
✅ Data merge logic returns correct combined data  

### Phase 3 Complete When:
✅ All API endpoints respond correctly  
✅ Can create template via API  
✅ Can create event with static values  
✅ Registration validation works with dynamic rules  

### Phase 4 Complete When:
✅ Template builder UI functional  
✅ Canvas syncs with field table  
✅ Event creation loads static value form  
✅ Public registration form renders dynamically  
✅ Can generate and download certificate  

---

## Conclusion

### Implementation Strategy: **FRESH START APPROACH** ✅

Since you're starting with fresh data, this refactor is **SIGNIFICANTLY SIMPLER**:

✅ **No data migration needed** - skip Phase 5  
✅ **No complex rollback required** - just rebuild if needed  
✅ **Faster timeline** - 3-4 weeks instead of 6-8  
✅ **Lower risk** - no existing data to corrupt  
✅ **Clean implementation** - no legacy compatibility code  

### Next Steps:
1. **Start with Phase 1** - Get database foundation right
2. **Test thoroughly in Tinker** - Verify relationships before moving on
3. **Build services incrementally** - One service at a time
4. **UI last** - Backend must be solid first

---

**Last Updated:** November 4, 2025  
**Status:** Planning Complete - Ready for Implementation  
**Next Step:** Begin Phase 1 - Create migrations and models