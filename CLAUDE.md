# Certificate Generator - Laravel 12 Project

## Tech Stack

-   Laravel 12 + Metronic (Bootstrap 5)
-   SQLite + DomPDF + Fabric.js

---

## System Architecture

### User Roles

-   **Root**: Full access, manage users, system settings
-   **User** (Admin): Create templates/events, generate certificates, manage registrations

### Core Modules

1. **Template System**: Upload background, drag-drop field positioning
2. **Event Management**: Create events, build registration forms, public URLs
3. **Certificate Generation**: From registrations, Excel bulk, or manual entry
4. **Verification**: Public page with QR code validation

---

## Application Flow

### 1. Template Creation (Root/User)

```
Upload Background → Add Fields (Fabric.js) → Drag/Position → Save Template
```

### 2. Event Setup (User)

```
Create Event → Select Template → Build Registration Form → Publish URL
```

### 3. Registration (Public)

```
Fill Form → Submit → Stored as Registration
```

### 4. Certificate Generation (User)

```
Option A: Select Event → Pick Registrations → Generate
Option B: Upload Excel → Validate → Generate Bulk
Option C: Manual Entry → Generate Single

↓
Generate PDF + QR Code → Store → Download/Email
```

### 5. Verification (Public)

```
Scan QR / Enter ID → Verify → Show Certificate Details
```

---

## Database Schema

```
users (id, name, email, password, role)
templates (id, name, background, is_default)
template_fields (id, template_id, field_name, x, y, width, height, font_size, color)
events (id, name, template_id, registration_enabled, public_url)
event_fields (id, event_id, field_name, field_type, required)
registrations (id, event_id, data, status, created_at)
certificates (id, event_id, registration_id, certificate_number, qr_code, pdf_path, emailed_at)
```

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

## Phase 2: Template Management

### Database

-   [x] `templates` table migration
-   [x] `template_fields` table migration

### Backend

-   [x] Template model
-   [x] TemplateField model
-   [x] Template controller (CRUD)
-   [x] Image upload handling
-   [ ] Default template seeder

### Frontend

-   [x] Template list page
-   [x] Template create/edit form
-   [x] Fabric.js integration
-   [x] Drag & drop editor interface
-   [x] Field properties panel (name, type, font size, color)
-   [x] Position saving (x, y, width, height)
-   [x] Template preview

### Features

-   [x] Upload background image (JPG/PNG)
-   [x] Add text placeholders
-   [x] Drag/resize/rotate fields
-   [x] Save field positions to DB
-   [x] Delete template
-   [x] Set default template

---

## Phase 3: Event Management ✅ COMPLETE

### Database

-   [x] `events` table migration
-   [x] `event_fields` table migration
-   [x] `registrations` table migration

### Backend

-   [x] Event model with slug generation
-   [x] EventField model
-   [x] Registration model
-   [x] Event controller (CRUD)
-   [x] Registration controller with DataTables
-   [x] Public registration form controller

### Frontend

-   [x] Event list page (grid cards)
-   [x] Event create/edit form with Blade components
-   [x] Template selection dropdown
-   [x] Form builder interface
-   [x] Add/remove form fields dynamically
-   [x] Field type selection (text, email, date, textarea, select, number)
-   [x] Required field toggle
-   [x] Public registration form (no auth)
-   [x] Registration list with DataTables
-   [x] Registration status management
-   [x] Bulk delete registrations

### Features

-   [x] Create event with template
-   [x] Enable/disable registration toggle
-   [x] Dynamic form builder with JavaScript
-   [x] Auto-generate unique slug
-   [x] Public registration URL (/register/{slug})
-   [x] View registrations with server-side DataTables
-   [x] Bulk operations (delete)
-   [x] Custom Blade form components (input, textarea, select)

---

## Phase 4: Certificate Generation (In Progress - 85%)

### Database

-   [x] `certificates` table migration (UUID primary key)
-   [x] Foreign keys: event_id, registration_id, generated_by

### Backend

-   [x] Certificate model with UUID
-   [x] Certificate controller (CRUD + DataTables)
-   [x] CertificateService class
-   [x] DomPDF integration (barryvdh/laravel-dompdf v3.1.1)
-   [x] QR code generation (simplesoftwareio/simple-qrcode v4.2.0)
-   [x] Certificate number auto-generation (CERT-YYYY-NNNNNN)
-   [x] Bulk generation from registrations
-   [x] Manual entry generation
-   [x] Regenerate certificate functionality
-   [x] VerificationController for public verification
-   [ ] Excel import/export (PhpSpreadsheet) - Pending
-   [ ] Queue jobs for bulk generation - Pending
-   [ ] Email delivery system - Pending

### Frontend

-   [x] Certificate list page with DataTables
-   [x] Generation options page (3 methods)
-   [x] Method 1: From registrations modal
    -   [x] Select event dropdown
    -   [x] Load registrations without certificates
    -   [x] Checkbox selection with "Select All"
    -   [x] Bulk generate with progress feedback
-   [x] Method 2: Manual entry modal
    -   [x] Dynamic form based on event fields
    -   [x] Field validation
    -   [x] Single certificate generation
-   [x] Method 3: Excel import modal (UI only)
    -   [ ] Excel upload functionality - Pending
    -   [ ] Template download - Pending
    -   [ ] Data preview - Pending
-   [x] Certificate action buttons (view, download, regenerate, delete)
-   [x] Bulk delete certificates
-   [ ] Certificate preview modal - Pending
-   [ ] Bulk download as ZIP - Pending

### Features

-   [x] Generate from event registrations (bulk)
-   [x] Single certificate generation (manual)
-   [x] Auto-increment certificate numbers per year
-   [x] QR code generation with verification URL
-   [x] PDF generation using DomPDF
-   [x] PDF template view with field positioning
-   [x] Storage in /storage/app/certificates/
-   [x] Download single PDF
-   [x] Regenerate certificate (updates PDF)
-   [x] Delete with automatic file cleanup
-   [x] DataTables with search/sort/pagination
-   [ ] Excel template with dynamic columns - Pending
-   [ ] Parse Excel and validate - Pending
-   [ ] Bulk download certificates (ZIP) - Pending
-   [ ] Certificate preview modal - Pending
-   [ ] Email single certificate - Pending
-   [ ] Bulk email certificates - Pending
-   [ ] Email queue for large batches - Pending
-   [ ] Email template customization - Pending

---

## Phase 5: Verification System (Pending)

### Backend

-   [x] VerificationController created
-   [x] Public verification routes
-   [ ] Certificate lookup optimization

### Frontend

-   [ ] Public verification page (/verify)
-   [ ] Certificate details display
-   [ ] QR code scanner integration (optional)
-   [ ] Invalid certificate message

### Features

-   [x] Verify by certificate number API
-   [x] Display certificate info JSON
-   [ ] Public verification form UI
-   [ ] Show certificate PDF/image
-   [ ] Verification status display
-   [ ] QR code scanner with camera

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

## Testing Checklist

-   [ ] Template CRUD operations
-   [ ] Field positioning accuracy
-   [ ] Event creation with forms
-   [ ] Public registration submission
-   [ ] Certificate PDF generation
-   [ ] Excel bulk upload
-   [ ] QR code generation
-   [ ] Public verification
-   [ ] Role-based access
-   [ ] File upload validation

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

**Last Updated**: November 2, 2025
**Current Phase**: Phase 4 - Certificate Generation (85% Complete)
**Next Steps**:

1. Test certificate generation (from registrations & manual entry)
2. Complete Excel import functionality
3. Build verification system UI
4. Add email delivery system

### Phase 1 Completed ✅:

-   ✅ Laravel 12 installation
-   ✅ SQLite database configured
-   ✅ Role system implemented (Root = 1, User = 2)
-   ✅ Middleware created (RootMiddleware, UserMiddleware, CheckActiveStatus)
-   ✅ User model updated with role helper methods
-   ✅ UserSeeder created with test accounts
-   ✅ Database migrated and seeded
-   ✅ Metronic template integration (by user)
-   ✅ Dashboard layouts created (Root & User)
-   ✅ Role-based dashboard controller

### Phase 2 Completed ✅:

-   ✅ Templates & TemplateFields database tables
-   ✅ Template and TemplateField models with relationships
-   ✅ TemplateController with full CRUD operations
-   ✅ Image upload handling for backgrounds
-   ✅ Template routes with middleware protection
-   ✅ Template index page with grid view
-   ✅ Template create form with image preview
-   ✅ Template edit page with Fabric.js editor
-   ✅ Drag & drop field positioning
-   ✅ Field properties panel (font, color, size, alignment)
-   ✅ Save/load field positions
-   ✅ Set default template feature
-   ✅ Delete template with image cleanup

### Phase 3 Completed ✅:

-   ✅ Events, EventFields, Registrations database tables
-   ✅ Event model with auto-slug generation
-   ✅ EventField & Registration models with relationships
-   ✅ EventController with full CRUD operations
-   ✅ RegistrationController with DataTables support
-   ✅ Public registration form (no authentication)
-   ✅ Event routes (protected + public)
-   ✅ Event index page with card grid layout
-   ✅ Event create/edit forms with Blade components
-   ✅ Dynamic form builder with JavaScript
-   ✅ Registration list with server-side DataTables
-   ✅ Bulk delete registrations functionality
-   ✅ Custom form components (x-form.input, x-form.textarea, x-form.select)

### Phase 4 Progress (85%):

-   ✅ Certificates table migration with UUID
-   ✅ Certificate model with relationships
-   ✅ CertificateController with CRUD + generation
-   ✅ CertificateService for PDF/QR generation
-   ✅ DomPDF integration (v3.1.1)
-   ✅ QR code generation (simple-qrcode v4.2.0)
-   ✅ Auto certificate number (CERT-YYYY-NNNNNN)
-   ✅ Certificate list with DataTables
-   ✅ Generation UI with 3 methods
-   ✅ Method 1: From registrations (bulk)
-   ✅ Method 2: Manual entry (single)
-   ✅ Method 3: Excel import (UI placeholder)
-   ✅ Download PDF functionality
-   ✅ Regenerate certificate feature
-   ✅ Bulk delete with file cleanup
-   ✅ VerificationController backend
-   ⏳ Excel import functionality (pending)
-   ⏳ Email delivery system (pending)
-   ⏳ Queue jobs for bulk operations (pending)

### Test Accounts:

-   **Root**: root@certify.com / password
-   **User**: user@certify.com / password
-   **Inactive**: inactive@certify.com / password (for testing)

### Template Features:

-   **Create Templates**: Upload background image (JPG/PNG, max 5MB)
-   **Fabric.js Editor**: Visual drag-and-drop field positioning
-   **Field Properties**: Name, type, font family, size, color, alignment, bold, italic, rotation
-   **Default Template**: Set any template as default for events
-   **Template Preview**: View all templates with thumbnail previews

### Event Features:

-   **Create Events**: Link to certificate template
-   **Form Builder**: Dynamic registration form with multiple field types
-   **Public Registration**: No-auth public form accessible via /register/{slug}
-   **Registration Management**: DataTables list with search, sort, filter
-   **Bulk Operations**: Delete multiple registrations at once

### Certificate Features:

-   **Generation Methods**:
    -   Bulk from event registrations
    -   Manual single entry
    -   Excel import (UI ready)
-   **PDF Generation**: DomPDF with template field positioning
-   **QR Codes**: Auto-generated with verification URL
-   **Certificate Numbers**: Auto-increment CERT-2025-000001 format
-   **Management**: DataTables list, download, regenerate, delete
-   **File Cleanup**: Auto-delete PDFs/QR codes on certificate deletion
