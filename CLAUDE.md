# Certificate Generator - Laravel 12 Project

## Tech Stack

-   Laravel 12 + Metronic (Bootstrap 5)
-   SQLite + DomPDF + Fabric.js + jQuery + DataTables

---

## System Architecture

### User Roles

-   **Root**: Full access, manage users, system settings
-   **User** (Admin): Create templates/events, generate certificates, manage registrations

### Core Modules

1. **Template System**: Upload background, drag-drop field positioning, unified field management
2. **Event Management**: Create events with static values, dynamic registration forms
3. **Certificate Generation**: From registrations, Excel bulk, or manual entry with data merging
4. **Verification**: Public page with QR code validation

### 🎯 **New Refactored Architecture (Phase 4 Completed)**

The system has been completely refactored to use a **unified field system** with flexible configurations:

#### Key Improvements:
- ✅ **Single Source of Truth**: Template fields define all field properties
- ✅ **Flexible Field Toggles**: `show_in_form`, `show_in_cert`, `is_required`
- ✅ **Static Values**: Event-level data stored in JSON (e.g., event name, date, organizer)
- ✅ **Dynamic Forms**: Registration forms auto-generated from template field configuration
- ✅ **Data Merging**: Automatic combination of registration data + static values for certificates
- ✅ **Position Management**: JSON-based field positioning with Fabric.js canvas sync

---

## Application Flow

### 1. Template Creation (Root/User)

```
Upload Background → Add Fields → Configure Toggles → Drag/Position on Canvas → Save Template
```

**Field Configuration Options:**
- `show_in_form`: Display in registration/manual entry forms
- `show_in_cert`: Display on generated certificate PDF
- `is_required`: Make field mandatory in forms
- Position data: x, y, fontSize, fontFamily, color, textAlign, bold, italic, rotation

### 2. Event Setup (User)

```
Create Event → Select Template → Configure Static Values → Publish Registration URL
```

**Static Values**: Event-level certificate data (e.g., event name, date, organizer, location)
- Stored in `events.static_values` JSON column
- Auto-merged with registration data during certificate generation
- No form fields created - template defines the structure

### 3. Registration (Public)

```
Open Public URL → Fill Dynamic Form → Submit → Stored as Registration
```

**Dynamic Form Generation:**
- Form fields loaded from template's `show_in_form = true` fields
- Only required fields marked with asterisk
- Data stored in `registrations.form_data` JSON column

### 4. Certificate Generation (User)

```
Option A: Select Event → Pick Registrations → Generate (merges form_data + static_values)
Option B: Upload Excel → Validate → Generate Bulk
Option C: Manual Entry → Generate Single (merges manual input + static_values)

↓
Merge Data → Generate PDF (only show_in_cert fields) → Generate QR Code → Store → Download/Email
```

**Data Merging Process:**
1. Registration form data OR manual input
2. + Event static values
3. = Complete certificate data
4. Filter fields with `show_in_cert = true` and `position_data`
5. Render on PDF with Fabric.js positioning

### 5. Verification (Public)

```
Scan QR / Enter ID → Verify → Show Certificate Details
```

---

## Database Schema

### **Updated Schema (Phase 4 Refactored)**

```sql
users (
    id, name, email, password, role, is_active, created_at, updated_at
)

templates (
    id UUID, name, background, width, height, is_default, created_by, created_at, updated_at
)

template_fields (
    id UUID, 
    template_id UUID,
    field_name VARCHAR,
    field_label VARCHAR,
    field_type VARCHAR,
    show_in_form BOOLEAN,      -- Show in registration/manual entry forms
    show_in_cert BOOLEAN,      -- Show on certificate PDF
    is_required BOOLEAN,       -- Required field in forms
    options JSON,              -- For select/radio options
    position_data JSON,        -- {x, y, fontSize, fontFamily, color, textAlign, bold, italic, rotation}
    order INT,
    created_at, updated_at
)

events (
    id UUID,
    name VARCHAR,
    slug VARCHAR UNIQUE,
    description TEXT,
    template_id UUID,
    registration_enabled BOOLEAN,
    static_values JSON,        -- Event-level certificate data
    created_by UUID,
    created_at, updated_at
)

registrations (
    id UUID,
    event_id UUID,
    form_data JSON,            -- Dynamic form submission data
    status ENUM('pending', 'approved', 'rejected'),
    registered_at TIMESTAMP,
    created_at, updated_at
)

certificates (
    id UUID,
    event_id UUID,
    registration_id UUID NULLABLE,
    certificate_number VARCHAR UNIQUE,
    data JSON,                 -- Merged certificate data (form_data + static_values)
    qr_code VARCHAR,
    pdf_path VARCHAR,
    generated_by UUID,
    emailed_at TIMESTAMP NULLABLE,
    created_at, updated_at
)
```

### **Key Schema Changes:**

**Old System:**
- Separate `event_fields` table with duplicate field definitions
- `registrations.data` without clear structure
- Manual field mapping between registration and certificate

**New System:**
- ✅ **No `event_fields` table** - Template fields are the single source
- ✅ **`template_fields.show_in_form`** - Controls form visibility
- ✅ **`template_fields.show_in_cert`** - Controls certificate visibility
- ✅ **`template_fields.position_data`** - JSON field positioning
- ✅ **`events.static_values`** - Event-level certificate data
- ✅ **`registrations.form_data`** - Clear form submission data
- ✅ **`certificates.data`** - Auto-merged complete data

### **Predefined Fields (Auto-created on Template Save):**
1. `participant_name` (Text) - Show in form + cert
2. `certificate_id` (Text) - Show in cert only
3. `issue_date` (Date) - Show in cert only  
4. `qr_code` (Text) - Show in cert only

---

## Phase 1: Core Setup

-   [x] Laravel 12 installation
-   [x] SQLite database configuration
-   [x] Authentication system (Root, User roles)
-   [x] Metronic template integration
-   [x] Basic dashboard layout
-   [x] User seeder (Root, User)
-   [x] Role middleware

---

## Phase 2: Template Management ✅ COMPLETE (Refactored)

### Database

-   [x] `templates` table migration with UUID
-   [x] `template_fields` table migration with new columns
-   [x] Added `show_in_form`, `show_in_cert`, `is_required` boolean columns
-   [x] Added `position_data` JSON column
-   [x] Added `options` JSON column for field choices
-   [x] Removed old position columns (x, y, width, height, font_size, etc.)

### Backend

-   [x] Template model with UUID
-   [x] TemplateField model with JSON casting
-   [x] **TemplateFieldService** - Field management service
-   [x] Template controller (CRUD)
-   [x] **6 New API Endpoints** for field management:
    -   [x] `POST /templates/{template}/fields/toggle-show-in-form`
    -   [x] `POST /templates/{template}/fields/toggle-show-in-cert`
    -   [x] `POST /templates/{template}/fields/toggle-required`
    -   [x] `POST /templates/{template}/fields/add-custom`
    -   [x] `DELETE /templates/{template}/fields/{field}`
    -   [x] `POST /templates/{template}/fields/save-positions`
-   [x] Image upload handling
-   [x] Auto-create 4 predefined fields on template save

### Frontend

-   [x] Template list page with preview thumbnails
-   [x] Template create/edit form
-   [x] **Unified Template Builder Interface**:
    -   [x] Field configuration table with toggle switches
    -   [x] Real-time canvas synchronization
    -   [x] Add custom fields dynamically
    -   [x] Delete fields with confirmation
    -   [x] Save field positions button
-   [x] Fabric.js canvas integration
-   [x] Drag & drop field positioning
-   [x] Field properties (fontSize, fontFamily, color, textAlign, bold, italic, rotation)
-   [x] Position data saved to JSON
-   [x] Template preview with correct positioning
-   [x] PDF preview download

### Features

-   [x] Upload background image (JPG/PNG)
-   [x] Auto-create 4 predefined fields
-   [x] Toggle field visibility (form/certificate)
-   [x] Toggle field required status
-   [x] Add custom fields with validation
-   [x] Delete fields (except predefined)
-   [x] Drag/resize/rotate fields on canvas
-   [x] Save position data to JSON structure
-   [x] Canvas-table synchronization
-   [x] Delete template with cleanup
-   [x] Set default template
-   [x] PDF preview generation

### API Endpoints

```
GET    /templates                  - List all templates
GET    /templates/create           - Show create form
POST   /templates                  - Store new template
GET    /templates/{id}             - Show template details
GET    /templates/{id}/edit        - Show edit form
PUT    /templates/{id}             - Update template
DELETE /templates/{id}             - Delete template
GET    /templates/{id}/preview     - Download PDF preview
POST   /templates/{id}/set-default - Set as default

# Field Management APIs (New)
POST   /templates/{id}/fields/toggle-show-in-form   - Toggle form visibility
POST   /templates/{id}/fields/toggle-show-in-cert   - Toggle cert visibility
POST   /templates/{id}/fields/toggle-required       - Toggle required status
POST   /templates/{id}/fields/add-custom            - Add custom field
DELETE /templates/{id}/fields/{fieldId}             - Delete field
POST   /templates/{id}/fields/save-positions        - Save all positions
```

---

## Phase 3: Event Management ✅ COMPLETE (Refactored)

### Database

-   [x] `events` table migration with UUID and slug
-   [x] Added `static_values` JSON column
-   [x] **Removed `event_fields` table** (no longer needed)
-   [x] `registrations` table with `form_data` JSON column

### Backend

-   [x] Event model with slug generation
-   [x] **EventConfigurationService** - Static values management
-   [x] Registration model with form_data accessor
-   [x] **RegistrationService** - Dynamic validation from template
-   [x] Event controller (CRUD)
-   [x] Registration controller with DataTables
-   [x] Public registration form controller
-   [x] **4 New API Endpoints** for event configuration:
    -   [x] `GET /events/{event}/form-preview` - Preview form fields
    -   [x] `GET /events/{event}/static-fields` - Get static field list
    -   [x] `POST /events/{event}/configure-static` - Save static values
    -   [x] `GET /events/{event}/certificate-preview` - Preview certificate

### Frontend

-   [x] Event list page (grid cards)
-   [x] **Refactored Event Create/Edit Form**:
    -   [x] Template selection
    -   [x] Static values configuration (dynamic from template)
    -   [x] No more manual form builder
    -   [x] Form preview from template fields
-   [x] Public registration form with dynamic fields
-   [x] Registration list with DataTables
-   [x] Registration status management
-   [x] Bulk delete registrations

### Features

-   [x] Create event with template
-   [x] Configure static values (event-level data)
-   [x] Enable/disable registration toggle
-   [x] Auto-generate unique slug
-   [x] Public registration URL (/register/{slug})
-   [x] Dynamic form generation from template
-   [x] Form validation based on `is_required` flag
-   [x] Store form data in JSON column
-   [x] View registrations with server-side DataTables
-   [x] Bulk operations (delete)
-   [x] Custom Blade form components

### API Endpoints

```
GET    /events                             - List all events
GET    /events/create                      - Show create form
POST   /events                             - Store new event
GET    /events/{id}                        - Show event details
GET    /events/{id}/edit                   - Show edit form
PUT    /events/{id}                        - Update event
DELETE /events/{id}                        - Delete event

# Event Configuration APIs (New)
GET    /events/{id}/form-preview           - Preview registration form
GET    /events/{id}/static-fields          - Get static field list
POST   /events/{id}/configure-static       - Save static values
GET    /events/{id}/certificate-preview    - Preview certificate data

# Public Registration
GET    /register/{slug}                    - Public registration form
POST   /register/{slug}                    - Submit registration

# Registration Management
GET    /events/{id}/registrations          - List registrations (DataTables)
PATCH  /events/{id}/registrations/{regId}  - Update status
DELETE /events/{id}/registrations/{regId}  - Delete registration
POST   /events/{id}/registrations/bulk-delete - Bulk delete
```

---

## Phase 4: Certificate Generation ✅ COMPLETE (Fully Refactored)

### Database

-   [x] `certificates` table migration (UUID primary key)
-   [x] Foreign keys: event_id, registration_id, generated_by
-   [x] `data` JSON column for merged certificate data

### Backend

-   [x] Certificate model with UUID
-   [x] Certificate controller (CRUD + DataTables)
-   [x] **CertificateService** - Refactored with data merging
-   [x] DomPDF integration (barryvdh/laravel-dompdf v3.1.1)
-   [x] QR code generation (simplesoftwareio/simple-qrcode v4.2.0)
-   [x] Certificate number auto-generation (CERT-YYYY-NNNNNN)
-   [x] **Data Merging Logic**:
    -   [x] From Registrations: `registration.form_data` + `event.static_values`
    -   [x] From Manual Entry: manual input + `event.static_values`
-   [x] **PDF Generation with Filtering**:
    -   [x] Only render fields with `show_in_cert = true`
    -   [x] Only render fields with `position_data` present
    -   [x] Apply Fabric.js positioning from JSON
-   [x] Bulk generation from registrations
-   [x] Manual entry generation (fixed)
-   [x] Regenerate certificate functionality
-   [x] VerificationController for public verification
-   [x] **Excel Import/Export** - **COMPLETE**
    -   [x] Download Excel template with dynamic columns
    -   [x] PhpSpreadsheet integration
    -   [x] Excel file validation
    -   [x] Data type validation (email, date, number, select)
    -   [x] Preview validated data with error highlighting
    -   [x] Bulk certificate generation from Excel
    -   [x] Field mapping from template configuration
-   [ ] Queue jobs for bulk generation - **Pending**
-   [ ] Email delivery system - **Pending**

### Frontend

-   [x] Certificate list page with DataTables
-   [x] Generation options page (3 methods)
-   [x] **Method 1: From Registrations Modal**
    -   [x] Select event dropdown
    -   [x] Load registrations without certificates
    -   [x] Checkbox selection with "Select All"
    -   [x] Bulk generate with progress feedback
-   [x] **Method 2: Manual Entry Modal** (Refactored)
    -   [x] Dynamic form from template `show_in_form` fields
    -   [x] Field validation based on `is_required`
    -   [x] Single certificate generation
    -   [x] Data merged with event static values
-   [x] **Method 3: Excel Import Modal**
    -   [x] UI created
    -   [x] Excel template download functionality
    -   [x] Excel upload with validation
    -   [x] Data preview with error highlighting
    -   [x] PhpSpreadsheet integration
    -   [x] Field mapping and validation
    -   [x] Bulk certificate generation
-   [x] Certificate action buttons (view, download, regenerate, delete)
-   [x] Bulk delete certificates
-   [ ] Certificate preview modal - **Pending**
-   [ ] Bulk download as ZIP - **Pending**

### Features

-   [x] **Generate from Event Registrations** (Bulk)
    -   [x] Automatic data merging
    -   [x] Skip registrations with existing certificates
    -   [x] Error handling with feedback
-   [x] **Single Certificate Generation** (Manual)
    -   [x] Form fields from template configuration
    -   [x] Merge with event static values
    -   [x] Field validation
-   [x] Auto-increment certificate numbers per year
-   [x] QR code generation with verification URL (SVG format)
-   [x] **PDF Generation** (Refactored)
    -   [x] Filter fields by `show_in_cert` flag
    -   [x] Read positioning from `position_data` JSON
    -   [x] Apply scaling for A4 landscape
    -   [x] Support font styling (bold, italic, alignment)
-   [x] Storage in `/storage/app/public/certificates/`
-   [x] Download single PDF
-   [x] Regenerate certificate (updates PDF + QR)
-   [x] Delete with automatic file cleanup
-   [x] DataTables with search/sort/pagination
-   [x] **Bulk Operations** - **COMPLETE**
    -   [x] Bulk delete certificates
    -   [x] Bulk download certificates as ZIP
    -   [x] ZIP file with timestamp naming
    -   [x] Automatic cleanup of temp ZIP files
    -   [x] Progress feedback for bulk operations
-   [x] **Excel Import Features** - **COMPLETE**
    -   [x] Download Excel template with dynamic columns from template
    -   [x] Template includes field validation (required, type, options)
    -   [x] Sample data row in template
    -   [x] Dropdown validation for select fields
    -   [x] Upload Excel file (.xlsx, .xls, .csv)
    -   [x] Parse and validate Excel data
    -   [x] Field type validation (email, date, number, select)
    -   [x] Required field validation
    -   [x] Preview data with error highlighting
    -   [x] Show validation errors per row
    -   [x] Bulk certificate generation from Excel
    -   [x] Progress feedback and error reporting
-   [ ] Certificate preview modal - **Pending**
-   [ ] Email single certificate - **Pending**
-   [ ] Bulk email certificates - **Pending**
-   [ ] Email queue for large batches - **Pending**
-   [ ] Email template customization - **Pending**

### Services Architecture

#### CertificateService Methods:
```php
generateFromRegistration($registration, $userId)
    → Merges registration.form_data + event.static_values
    
generateFromManualData($event, $data, $userId)
    → Merges manual input + event.static_values
    
generateCertificate($event, $template, $data, $userId, $registrationId = null)
    → Core generation logic
    → Creates certificate record
    → Generates QR code (SVG)
    → Generates PDF with filtered fields
    
generateQrCode($certificate)
    → Creates SVG QR code with verification URL
    
generatePdf($certificate, $template, $data, $qrCodePath)
    → Filters fields: show_in_cert = true AND position_data exists
    → Reads positioning from position_data JSON
    → Applies scaling for A4 landscape
    → Renders PDF using DomPDF
    
bulkGenerateFromRegistrations($registrationIds, $userId)
    → Loops through registrations
    → Skips existing certificates
    → Returns success/error arrays
    
regenerate($certificate)
    → Deletes old PDF/QR files
    → Regenerates with latest template
```

### API Endpoints

```
GET    /certificates                      - List all certificates (DataTables)
GET    /certificates/create               - Show generation options page
POST   /certificates/generate-from-registrations - Bulk generate
POST   /certificates/generate-manual      - Single manual entry
GET    /certificates/{id}                 - Show certificate details
```
GET    /certificates/{id}/download        - Download PDF
POST   /certificates/{id}/regenerate      - Regenerate certificate
DELETE /certificates/{id}                 - Delete certificate
POST   /certificates/bulk-delete          - Bulk delete
POST   /certificates/bulk-download        - Bulk download as ZIP

# Excel Import/Export
GET    /events/{id}/certificate-template  - Download Excel template
POST   /certificates/import-excel         - Upload & validate Excel
POST   /certificates/generate-from-excel  - Generate from Excel data

# Verification (Public)
GET    /verify/{certificateNumber}        - Verify certificate
```
```

---

## Phase 5: Verification System ✅ COMPLETE

### Backend

-   [x] VerificationController created
-   [x] Public verification routes
-   [x] Certificate lookup optimization
-   [x] Verify by certificate number API
-   [x] Display certificate info JSON

### Frontend

-   [x] Public verification page (/verify)
-   [x] Certificate search form with auto-focus
-   [x] Certificate details display page
-   [x] QR code scanner integration (HTML5-QRCode library)
-   [x] Invalid certificate message handling
-   [x] Verification status display with icons
-   [x] PDF preview embed
-   [x] Download verified certificate button

### Features

-   [x] Verify by certificate number API
-   [x] Display certificate info JSON
-   [x] Public verification form UI
-   [x] Show certificate PDF preview
-   [x] Verification status display
-   [x] QR code scanner with device camera
-   [x] URL-based verification (direct link to certificate)
-   [x] Guest-friendly layout with branding
-   [x] Responsive design
-   [x] Error handling for invalid certificates
-   [x] Camera permission handling
-   [x] Extract certificate number from QR URL

### Technical Implementation

**Views Created:**
- `resources/views/pages/verify/index.blade.php` - Verification search form
- `resources/views/pages/verify/show.blade.php` - Certificate details page

**JavaScript Created:**
- `public/web/js/verification.js` - QR scanner and form handling

**Libraries Used:**
- HTML5-QRCode v2.3.8 - For QR code scanning
- SweetAlert2 - For user-friendly alerts
- Bootstrap 5 Modal - For QR scanner modal

**Routes:**
```
GET  /verify                    - Show verification form
POST /verify                    - Verify certificate by number
GET  /verify/{certificateNumber} - Show certificate details
```

**QR Scanner Features:**
- Auto camera detection
- Real-time QR code scanning
- URL extraction (supports full verification URLs)
- Graceful fallback if camera unavailable
- Modal-based interface
- Auto-stop on modal close

---

## Phase 5.5: Excel Import/Export ✅ COMPLETE

### Backend Implementation

-   [x] **CertificateController Methods**:
    -   [x] `downloadTemplate(Event $event)` - Generate Excel template
    -   [x] `importExcel(Request $request)` - Validate uploaded Excel
    -   [x] `generateFromExcel(Request $request)` - Bulk generate certificates
-   [x] PhpSpreadsheet integration for Excel handling
-   [x] Dynamic template generation from template fields
-   [x] Field validation (required, type, options)
-   [x] Data type validation (email, date, number, select)
-   [x] Error tracking per row

### Frontend Implementation

-   [x] **Excel Import Modal UI** (updated)
-   [x] **JavaScript** (`create.js` - Excel Import Module)
-   [x] Template download functionality
-   [x] File upload with AJAX validation
-   [x] Preview data rendering with error highlighting
-   [x] Bulk generation with progress feedback

### Features

#### Excel Template Generation
-   [x] Dynamic columns from template form fields
-   [x] Styled header row (blue background, white text)
-   [x] Field labels as column headers
-   [x] Comments with field info (required, type)
-   [x] Sample data row (italicized, gray)
-   [x] Dropdown validation for select fields
-   [x] Auto-sized columns
-   [x] Frozen header row

#### Excel Validation
-   [x] File format validation (.xlsx, .xls, .csv)
-   [x] Max file size: 10MB
-   [x] Header validation (match template fields)
-   [x] Required field validation
-   [x] Email format validation
-   [x] Date format parsing (Excel dates + string dates)
-   [x] Number format validation
-   [x] Select options validation
-   [x] Row-by-row error tracking
-   [x] Skip empty rows

#### Data Preview
-   [x] Show first 10 rows
-   [x] Dynamic column headers
-   [x] Row numbers from Excel
-   [x] Status column (Valid/Invalid)
-   [x] Error highlighting (red background)
-   [x] Tooltips with error details
-   [x] Validation summary (valid/invalid counts)

#### Certificate Generation
-   [x] Process only valid rows
-   [x] Merge Excel data with event static values
-   [x] Transaction-based processing
-   [x] Error tracking per row
-   [x] Success/error count reporting

### API Endpoints

```
GET    /events/{event}/certificate-template  - Download Excel template
POST   /certificates/import-excel            - Upload & validate Excel file
POST   /certificates/generate-from-excel     - Generate certificates from validated data
```

### Usage Workflow

1. **Select Event** → Choose event with configured template
2. **Download Template** → Get Excel file with correct columns
3. **Fill Data** → Enter participant information in Excel
4. **Upload File** → Upload completed Excel file
5. **Validation** → System validates all rows automatically
6. **Preview** → Review data and errors (if any)
7. **Generate** → Create certificates for all valid rows

---

## Phase 5.6: Bulk Operations ✅ COMPLETE

### Backend Implementation

-   [x] **CertificateController Methods**:
    -   [x] `bulkDestroy(Request $request)` - Delete multiple certificates
    -   [x] `bulkDownload(Request $request)` - Download as ZIP
-   [x] ZipArchive integration for ZIP creation
-   [x] Temporary storage cleanup
-   [x] Automatic file deletion after send

### Frontend Implementation

-   [x] **Selection Toolbar**:
    -   [x] Shows when certificates are selected
    -   [x] Displays selection count
    -   [x] Three action buttons (Email, Download, Delete)
-   [x] **JavaScript** (`index-table.js`):
    -   [x] Checkbox selection handling
    -   [x] Exclude header checkbox from selection
    -   [x] Bulk download with loading indicator
    -   [x] Bulk delete with confirmation
    -   [x] Auto-refresh after operations

### Features

#### Bulk Download as ZIP
-   [x] Select multiple certificates via checkboxes
-   [x] Create ZIP file with all selected PDFs
-   [x] Named files: `certificate-number.pdf` inside ZIP
-   [x] ZIP filename: `certificates-YYYY-MM-DD-HHMMSS.zip`
-   [x] Loading indicator during ZIP creation
-   [x] Browser download with proper filename
-   [x] Automatic temp file cleanup
-   [x] Error handling for missing PDFs
-   [x] Success feedback with count

#### Bulk Delete
-   [x] Select multiple certificates via checkboxes
-   [x] Confirmation dialog with count
-   [x] Delete selected certificates and files
-   [x] Success feedback
-   [x] Auto-refresh table
-   [x] Clear selection after delete
-   [x] Error handling

### API Endpoints

```
POST   /certificates/bulk-download  - Download multiple certificates as ZIP
POST   /certificates/bulk-destroy   - Delete multiple certificates
```

### Request/Response Flow

**Bulk Download:**
```
POST /certificates/bulk-download
Body: { ids: [uuid1, uuid2, uuid3, ...] }
→ Returns: ZIP file (application/zip)
   Content-Disposition: attachment; filename="certificates-2025-11-05-143022.zip"
```

**Bulk Delete:**
```
POST /certificates/bulk-destroy
Body: { ids: [uuid1, uuid2, uuid3, ...] }
→ Returns: {
    success: true,
    message: "5 certificate(s) deleted successfully"
}
```

### User Interface

**Selection Toolbar:**
- Appears when one or more certificates are selected
- Shows: `X Selected` (count)
- Buttons:
  - **Email Selected** (blue) - Coming soon
  - **Download Selected** (dark) - Downloads ZIP
  - **Delete Selected** (red) - Bulk delete

**Workflow:**
1. User checks certificate checkboxes
2. Selection toolbar appears
3. User clicks "Download Selected"
4. Loading modal shows
5. ZIP file downloads automatically
6. Success notification appears

---

## Phase 6: Additional Features

### Settings (Root Only)

-   [ ] User management (CRUD)
-   [ ] System settings
-   [ ] Email configuration

### Reports & Analytics

-   [ ] Total certificates generated
-   [ ] Certificates by event
-   [ ] Registration statistics
-   [ ] Export reports

### UI/UX

-   [ ] Responsive design
-   [ ] Loading states
-   [ ] Success/error notifications
-   [ ] Form validation
-   [ ] Confirmation modals

---

---

## 🔥 Phase 4 Refactor Documentation

### Problem Statement (Old System)

**Before Refactor:**
- Templates had `template_fields` for certificate positioning
- Events had separate `event_fields` table for registration forms
- Duplicate field definitions across two tables
- Manual mapping required between registration and certificate fields
- Inflexible - couldn't easily control field visibility
- No clear separation between form fields and certificate fields

### Solution (New System)

**After Refactor:**
- ✅ **Single Source of Truth**: `template_fields` table defines everything
- ✅ **Flexible Toggles**: Control field behavior with boolean flags
- ✅ **Static Values**: Event-level data in JSON, no duplicate tables
- ✅ **Automatic Merging**: Registration data + static values = certificate data
- ✅ **Clean Architecture**: Clear separation of concerns

### Database Schema Changes

#### template_fields Table (Refactored)
```sql
-- ADDED COLUMNS:
show_in_form BOOLEAN DEFAULT 1         -- Show in registration/manual forms
show_in_cert BOOLEAN DEFAULT 1         -- Show on certificate PDF
is_required BOOLEAN DEFAULT 0          -- Required in forms
position_data JSON                     -- {x, y, fontSize, fontFamily, ...}
options JSON                           -- For select/radio field choices

-- REMOVED COLUMNS:
x, y, width, height                    -- Moved to position_data JSON
font_size, font_family, font_weight    -- Moved to position_data JSON
text_align, color, bold, italic        -- Moved to position_data JSON
```

#### events Table (Refactored)
```sql
-- ADDED COLUMNS:
static_values JSON                     -- Event-level certificate data

-- NO MORE event_fields TABLE!
```

#### registrations Table (Refactored)
```sql
-- RENAMED COLUMN:
data → form_data                       -- Clearer naming
```

### Code Architecture Changes

#### New Services

**1. TemplateFieldService**
```php
// Purpose: Manage template field operations
Methods:
- toggleShowInForm($field)
- toggleShowInCert($field)
- toggleRequired($field)
- addCustomField($template, $data)
- deleteField($field)
- saveFieldPositions($template, $positions)
- ensurePredefinedFields($template)
```

**2. EventConfigurationService**
```php
// Purpose: Handle event static values
Methods:
- getStaticFieldsForEvent($event)
- saveStaticValues($event, $values)
- getFormPreview($event)
- getCertificatePreview($event)
```

**3. RegistrationService** (Updated)
```php
// Purpose: Dynamic form handling
Methods:
- getFormConfiguration($event)          // Get fields from template
- createRegistration($event, $data)     // Dynamic validation
- validateFormData($fields, $data)      // Based on is_required flag
```

**4. CertificateService** (Updated)
```php
// Purpose: Generate certificates with data merging
Key Changes:
- generateFromRegistration(): Merges form_data + static_values
- generateFromManualData(): Merges manual input + static_values  
- generatePdf(): Filters fields by show_in_cert + position_data
- Reads positioning from position_data JSON
```

#### New API Endpoints

**Template Field Management (6 endpoints)**
```
POST   /templates/{id}/fields/toggle-show-in-form
POST   /templates/{id}/fields/toggle-show-in-cert
POST   /templates/{id}/fields/toggle-required
POST   /templates/{id}/fields/add-custom
DELETE /templates/{id}/fields/{fieldId}
POST   /templates/{id}/fields/save-positions
```

**Event Configuration (4 endpoints)**
```
GET    /events/{id}/form-preview
GET    /events/{id}/static-fields
POST   /events/{id}/configure-static
GET    /events/{id}/certificate-preview
```

### Frontend Changes

#### Template Builder (resources/views/pages/templates/edit.blade.php)
**Before**: Separate canvas and field list
**After**: Unified interface with:
- Field configuration table with toggle switches
- Real-time canvas synchronization
- Add/delete custom fields
- Save positions button
- AJAX calls with JSON content-type

#### Event Form (resources/views/pages/events/create.blade.php)
**Before**: Manual form builder with field duplication
**After**: 
- Template selection
- Static values configuration (dynamic from template)
- Form preview button
- No more manual field building

#### Registration Form (resources/views/pages/registrations/form.blade.php)
**Before**: Static form fields from event_fields
**After**: Dynamic form generation from template fields with `show_in_form = true`

#### Certificate Generation (resources/views/pages/certificates/create.blade.php)
**Before**: Manual entry used event fields
**After**: Manual entry uses template fields, merges with event static values

### Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     TEMPLATE FIELDS                          │
│  (Single Source of Truth - All Field Definitions)           │
│                                                              │
│  Fields: participant_name, email, phone, event_name, etc.   │
│  Flags: show_in_form, show_in_cert, is_required             │
│  Data: position_data JSON, options JSON                     │
└────────────┬────────────────────────────┬───────────────────┘
             │                            │
             ▼                            ▼
    ┌────────────────┐          ┌─────────────────┐
    │ show_in_form=1 │          │ show_in_cert=1  │
    │                │          │                 │
    │ Registration   │          │ Certificate     │
    │ Form Fields    │          │ PDF Fields      │
    └────────┬───────┘          └────────┬────────┘
             │                           │
             ▼                           │
    ┌─────────────────┐                 │
    │  PUBLIC FORM    │                 │
    │  /register/slug │                 │
    └────────┬────────┘                 │
             │                           │
             ▼                           │
    ┌─────────────────┐                 │
    │  REGISTRATION   │                 │
    │  form_data JSON │                 │
    └────────┬────────┘                 │
             │                           │
             │        ┌──────────────┐   │
             └───────▶│    EVENT     │   │
                      │ static_values│───┘
                      └──────┬───────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │  CERTIFICATE    │
                    │  data JSON      │
                    │  (MERGED DATA)  │
                    └────────┬────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │   PDF OUTPUT    │
                    │ (Filtered by    │
                    │ show_in_cert)   │
                    └─────────────────┘
```

### Migration Path

**No database migration needed!** The refactor reuses existing columns:
1. Added new columns to existing tables
2. Changed validation rules in controllers
3. Updated service logic for data handling
4. Modified views to use new structure

### Benefits of New System

1. ✅ **No Duplication**: Single field definition source
2. ✅ **Flexibility**: Easy field visibility control
3. ✅ **Maintainability**: Change template → all events updated
4. ✅ **Clarity**: Clear separation (form vs certificate fields)
5. ✅ **Scalability**: Easy to add new field types/properties
6. ✅ **Performance**: Less database tables, JSON queries
7. ✅ **DX**: Better developer experience, cleaner code

---

## Testing Checklist

### Phase 1-4 Testing (Refactored System)

-   [x] **Template Management**
    -   [x] Create template with background upload
    -   [x] Field auto-creation (4 predefined fields)
    -   [x] Toggle show_in_form switch
    -   [x] Toggle show_in_cert switch
    -   [x] Toggle is_required switch
    -   [x] Add custom field
    -   [x] Delete custom field
    -   [x] Drag field on canvas
    -   [x] Save field positions
    -   [x] Canvas-table synchronization
    -   [x] PDF preview download
    -   [x] Template delete with cleanup
    -   [x] Set default template

-   [x] **Event Management**
    -   [x] Create event with template
    -   [x] Configure static values
    -   [x] Form preview loads correctly
    -   [x] Certificate preview shows merged data
    -   [x] Public registration URL generation
    -   [x] Enable/disable registration toggle
    -   [x] Event slug uniqueness
    -   [x] Update event static values
    -   [x] Delete event

-   [x] **Public Registration**
    -   [x] Dynamic form generation from template
    -   [x] Required fields validation
    -   [x] Form submission stores form_data JSON
    -   [x] Success page display
    -   [x] Disabled registration handling

-   [x] **Registration Management**
    -   [x] DataTables list with search/filter
    -   [x] Status update (pending/approved/rejected)
    -   [x] View registration data
    -   [x] Bulk delete registrations
    -   [x] Delete single registration

-   [x] **Certificate Generation**
    -   [x] Method 1: From registrations
        -   [x] Load registrations without certificates
        -   [x] Select multiple registrations
        -   [x] Bulk generate with data merging
        -   [x] Skip existing certificates
        -   [x] Error handling display
    -   [x] Method 2: Manual entry
        -   [x] Form fields from template
        -   [x] Field validation
        -   [x] Data merge with static values
        -   [x] Single certificate generation
    -   [x] Method 3: Excel import
        -   [x] Download template functionality
        -   [x] Upload Excel file
        -   [x] Validate data
        -   [x] Preview with errors
        -   [x] Bulk generation

-   [x] **Certificate Management**
    -   [x] DataTables list
    -   [x] View certificate details
    -   [x] Download PDF
    -   [x] PDF rendering with correct positions
    -   [x] QR code embedded in PDF
    -   [x] Regenerate certificate
    -   [x] Delete certificate with file cleanup
    -   [x] Bulk delete certificates

-   [x] **PDF Generation**
    -   [x] Filter fields by show_in_cert flag
    -   [x] Skip fields without position_data
    -   [x] Apply correct positioning from JSON
    -   [x] Font styling (bold, italic)
    -   [x] Text alignment (left, center, right)
    -   [x] Color application
    -   [x] Background image rendering
    -   [x] QR code rendering
    -   [x] A4 landscape scaling

-   [x] **Data Integrity**
    -   [x] Registration form_data structure
    -   [x] Event static_values structure
    -   [x] Certificate data merging
    -   [x] Position_data JSON structure
    -   [x] Field options JSON structure

-   [x] **Role-Based Access**
    -   [x] Root access to all features
    -   [x] User access to templates/events/certificates
    -   [x] Public access to registration only
    -   [x] Middleware protection

-   [ ] **Pending Features**
    -   [ ] Excel import functionality
    -   [ ] Email delivery system
    -   [ ] Queue jobs for bulk operations
    -   [ ] Certificate preview modal
    -   [ ] Bulk download as ZIP
    -   [x] Public verification UI - **COMPLETE**

---

## Deployment Checklist

-   [ ] Environment configuration
-   [ ] Database migration
-   [ ] Storage permissions
-   [ ] Queue worker setup
-   [ ] Default data seeding
-   [ ] PDF fonts configuration
-   [ ] Backup strategy

---

## Notes

-   Keep it simple, no over-engineering
-   Ask before generating long code
-   Use Bootstrap 5 (Metronic) components
-   SQLite for database
-   Store certificates in `/storage/app/certificates`
-   Store templates in `/storage/app/templates`

---

## Current Progress

**Last Updated**: November 5, 2025  
**Current Phase**: Phase 5.6 - Bulk Operations ✅ **COMPLETE (100%)**  
**Next Steps**:

1. ✅ Phase 4 Refactor Complete - Unified field system implemented
2. ✅ Phase 5 Complete - Public verification with QR scanner
3. ✅ Phase 5.5 Complete - Excel import/export functionality
4. ✅ Phase 5.6 Complete - Bulk download (ZIP) & bulk delete
5. ⏳ Email delivery system with queue
6. ⏳ Certificate preview modal
7. ⏳ User management (Root only)
8. ⏳ Reports & Analytics

---

### 🎯 Major Refactor Completed (Phase 4)

**Architectural Changes:**
- ✅ Removed `event_fields` table - Template is single source of truth
- ✅ Added `template_fields.show_in_form`, `show_in_cert`, `is_required` toggles
- ✅ Added `template_fields.position_data` JSON column
- ✅ Added `events.static_values` JSON column for event-level data
- ✅ Changed `registrations.data` to `registrations.form_data`
- ✅ Implemented automatic data merging (form_data + static_values)
- ✅ Updated all services for new data structure
- ✅ Refactored all controllers and views
- ✅ Fixed manual certificate entry
- ✅ Updated PDF generation with field filtering

**New Services:**
- ✅ `TemplateFieldService` - Unified field management
- ✅ `EventConfigurationService` - Static values handling
- ✅ `RegistrationService` - Dynamic form validation
- ✅ `CertificateService` - Data merging & PDF generation

**New API Endpoints (10 total):**
- ✅ 6 Template Field Management endpoints
- ✅ 4 Event Configuration endpoints

---

### Phase 1 Completed ✅

-   ✅ Laravel 12 installation
-   ✅ SQLite database configured
-   ✅ Role system implemented (Root = 1, User = 2)
-   ✅ Middleware created (RootMiddleware, UserMiddleware, CheckActiveStatus)
-   ✅ User model updated with role helper methods
-   ✅ UserSeeder created with test accounts
-   ✅ Database migrated and seeded
-   ✅ Metronic template integration
-   ✅ Dashboard layouts created (Root & User)
-   ✅ Role-based dashboard controller

### Phase 2 Completed ✅ (Refactored)

-   ✅ Templates & TemplateFields database tables (UUID)
-   ✅ New columns: `show_in_form`, `show_in_cert`, `is_required`, `position_data`, `options`
-   ✅ Template and TemplateField models with JSON casting
-   ✅ TemplateController with CRUD operations
-   ✅ **TemplateFieldService** for field management
-   ✅ 6 new API endpoints for field operations
-   ✅ Image upload handling for backgrounds
-   ✅ Template routes with middleware protection
-   ✅ Template index page with preview thumbnails
-   ✅ Template create form with image preview
-   ✅ **Unified Template Builder** with:
    -   ✅ Field configuration table
    -   ✅ Toggle switches (form/cert/required)
    -   ✅ Fabric.js canvas
    -   ✅ Real-time synchronization
    -   ✅ Add/delete custom fields
    -   ✅ Save positions to JSON
-   ✅ Drag & drop field positioning
-   ✅ Field properties (fontSize, fontFamily, color, alignment, bold, italic, rotation)
-   ✅ Auto-create 4 predefined fields
-   ✅ PDF preview with correct positioning
-   ✅ Set default template feature
-   ✅ Delete template with image cleanup

### Phase 3 Completed ✅ (Refactored)

-   ✅ Events table with UUID, slug, and `static_values` JSON
-   ✅ **Removed event_fields table**
-   ✅ Registrations table with `form_data` JSON column
-   ✅ Event model with slug generation
-   ✅ **EventConfigurationService** for static values
-   ✅ Registration model with form_data accessor
-   ✅ **RegistrationService** with dynamic validation
-   ✅ EventController with CRUD operations
-   ✅ 4 new API endpoints for event configuration
-   ✅ RegistrationController with DataTables support
-   ✅ Public registration form (no authentication)
-   ✅ Event routes (protected + public)
-   ✅ Event index page with card grid layout
-   ✅ **Refactored Event create/edit forms**:
    -   ✅ Template selection
    -   ✅ Static values configuration
    -   ✅ No manual form builder
    -   ✅ Form preview from template
-   ✅ **Dynamic registration forms** from template fields
-   ✅ Registration list with server-side DataTables
-   ✅ Bulk delete registrations functionality
-   ✅ Custom form components (x-form.input, x-form.textarea, x-form.select)

### Phase 4 Completed ✅ (100% - Fully Refactored)

-   ✅ Certificates table migration with UUID
-   ✅ Certificate model with relationships
-   ✅ CertificateController with CRUD + generation
-   ✅ **CertificateService refactored** with:
    -   ✅ Data merging logic (form_data + static_values)
    -   ✅ Field filtering (show_in_cert + position_data)
    -   ✅ JSON position reading
    -   ✅ A4 landscape scaling
-   ✅ DomPDF integration (v3.1.1)
-   ✅ QR code generation (simple-qrcode v4.2.0) - SVG format
-   ✅ Auto certificate number (CERT-YYYY-NNNNNN)
-   ✅ Certificate list with DataTables
-   ✅ Generation UI with 3 methods
-   ✅ **Method 1: From Registrations** (bulk)
    -   ✅ Select event dropdown
    -   ✅ Load registrations without certificates
    -   ✅ Checkbox selection with "Select All"
    -   ✅ Automatic data merging
    -   ✅ Bulk generate with progress feedback
-   ✅ **Method 2: Manual Entry** (single) - **FIXED**
    -   ✅ Dynamic form from template fields
    -   ✅ Field validation based on is_required
    -   ✅ Data merge with event static values
    -   ✅ Single certificate generation
-   ✅ Method 3: Excel Import (UI placeholder)
-   ✅ Download PDF functionality
-   ✅ Regenerate certificate feature
-   ✅ Bulk delete with file cleanup
-   ✅ VerificationController backend
-   ✅ **PDF Generation with filtering**:
    -   ✅ Only show_in_cert = true fields
    -   ✅ Only fields with position_data
    -   ✅ Correct positioning from JSON
    -   ✅ Font styling support
-   ⏳ Excel import functionality (pending)
-   ⏳ Email delivery system (pending)
-   ⏳ Queue jobs for bulk operations (pending)
-   ⏳ Certificate preview modal (pending)
-   ⏳ Bulk download as ZIP (pending)

### Phase 5 Completed ✅ (100% - Verification System)

-   ✅ **Public Verification Page** (`/verify`)
    -   ✅ Certificate number search form
    -   ✅ Auto-focus on input field
    -   ✅ Guest-friendly layout with branding
    -   ✅ Responsive design
-   ✅ **Certificate Details Page** (`/verify/{number}`)
    -   ✅ Verified certificate badge
    -   ✅ Certificate information display
    -   ✅ Event details
    -   ✅ Generation information
    -   ✅ PDF preview embed
    -   ✅ Download button
    -   ✅ Verify another button
-   ✅ **QR Code Scanner**
    -   ✅ HTML5-QRCode library integration
    -   ✅ Modal-based scanner interface
    -   ✅ Auto camera detection
    -   ✅ Real-time QR scanning
    -   ✅ URL extraction support
    -   ✅ Graceful error handling
    -   ✅ Camera permission handling
-   ✅ **Error Handling**
    -   ✅ Invalid certificate messages
    -   ✅ SweetAlert2 notifications
    -   ✅ Inline error display
    -   ✅ Camera unavailable fallback
-   ✅ **JavaScript** (`verification.js`)
    -   ✅ AJAX form submission
    -   ✅ QR scanner start/stop
    -   ✅ Modal lifecycle handling
    -   ✅ Certificate number extraction from URLs

---

### Test Accounts

-   **Root**: root@certify.com / password
-   **User**: user@certify.com / password
-   **Inactive**: inactive@certify.com / password (for testing)

---

### System Capabilities Summary

#### Template Features
- ✅ Create templates with background image upload
- ✅ Auto-create 4 predefined fields (participant_name, certificate_id, issue_date, qr_code)
- ✅ Add unlimited custom fields
- ✅ Toggle field visibility (form/certificate)
- ✅ Toggle field required status
- ✅ Drag & drop field positioning with Fabric.js
- ✅ Real-time canvas-table synchronization
- ✅ Save positions to JSON structure
- ✅ Font customization (family, size, color, bold, italic, alignment, rotation)
- ✅ PDF preview generation
- ✅ Set default template
- ✅ Delete with cleanup

#### Event Features
- ✅ Create events linked to templates
- ✅ Configure event-level static values (event name, date, organizer, etc.)
- ✅ Auto-generate unique slug
- ✅ Enable/disable registration toggle
- ✅ Public registration URL (/register/{slug})
- ✅ Form preview from template configuration
- ✅ Certificate preview with merged data
- ✅ No manual form building required

#### Registration Features
- ✅ Dynamic form generation from template fields
- ✅ Only show fields with show_in_form = true
- ✅ Field validation based on is_required flag
- ✅ Support multiple field types (text, email, date, textarea, select, number)
- ✅ Store data in form_data JSON column
- ✅ Public access (no authentication)
- ✅ DataTables management with search/filter
- ✅ Status management (pending/approved/rejected)
- ✅ Bulk delete operations

#### Certificate Features
- ✅ **3 Generation Methods**:
  1. Bulk from event registrations (with data merging)
  2. Manual single entry (with static value merging)
  3. Excel import (UI ready, functionality pending)
- ✅ Automatic data merging (registration/manual data + event static values)
- ✅ PDF generation with field filtering
- ✅ Only render fields with show_in_cert = true
- ✅ Only render fields with position_data
- ✅ QR code generation (SVG format)
- ✅ Auto-increment certificate numbers (CERT-2025-000001)
- ✅ Download individual PDFs
- ✅ Regenerate certificates with updated templates
- ✅ DataTables management
- ✅ Bulk delete with file cleanup
- ✅ Verification API endpoint

#### Data Flow
```
Template Fields (show_in_form=true)
    ↓
Registration Form (dynamic)
    ↓
form_data JSON
    +
Event static_values JSON
    ↓
Certificate data JSON
    ↓
PDF (only show_in_cert=true fields with position_data)
```
