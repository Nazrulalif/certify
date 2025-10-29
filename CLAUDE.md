# Certificate Generator - Laravel 12 Project

## Tech Stack
- Laravel 12 + Metronic (Bootstrap 5)
- SQLite + DomPDF + Fabric.js

---

## System Architecture

### User Roles
- **Root**: Full access, manage users, system settings
- **User** (Admin): Create templates/events, generate certificates, manage registrations

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
- [x] Laravel 12 installation
- [x] SQLite database configuration
- [x] Authentication system (Root, User roles)
- [x] Metronic template integration
- [x] Basic dashboard layout
- [x] User seeder (Root, User)
- [x] Role middleware

---

## Phase 2: Template Management
### Database
- [x] `templates` table migration
- [x] `template_fields` table migration

### Backend
- [x] Template model
- [x] TemplateField model
- [x] Template controller (CRUD)
- [x] Image upload handling
- [ ] Default template seeder

### Frontend
- [x] Template list page
- [x] Template create/edit form
- [x] Fabric.js integration
- [x] Drag & drop editor interface
- [x] Field properties panel (name, type, font size, color)
- [x] Position saving (x, y, width, height)
- [x] Template preview

### Features
- [x] Upload background image (JPG/PNG)
- [x] Add text placeholders
- [x] Drag/resize/rotate fields
- [x] Save field positions to DB
- [x] Delete template
- [x] Set default template

---

## Phase 3: Event Management
### Database
- [ ] `events` table migration
- [ ] `event_fields` table migration
- [ ] `registrations` table migration

### Backend
- [ ] Event model
- [ ] EventField model
- [ ] Registration model
- [ ] Event controller (CRUD)
- [ ] Registration controller
- [ ] Public registration form controller

### Frontend
- [ ] Event list page
- [ ] Event create/edit form
- [ ] Template selection dropdown
- [ ] Form builder interface
- [ ] Add/remove form fields
- [ ] Field type selection (text, email, date, dropdown, number)
- [ ] Required field toggle
- [ ] Public registration form (no auth)
- [ ] Registration list per event
- [ ] Registration status management

### Features
- [ ] Create event with template
- [ ] Enable/disable registration
- [ ] Dynamic form builder
- [ ] Generate public URL
- [ ] View registrations
- [ ] Export registrations

---

## Phase 4: Certificate Generation
### Database
- [ ] `certificates` table migration

### Backend
- [ ] Certificate model
- [ ] Certificate controller
- [ ] DomPDF integration
- [ ] Excel import/export (PhpSpreadsheet)
- [ ] QR code generation
- [ ] Bulk generation queue jobs
- [ ] Certificate number generator

### Frontend
- [ ] Certificate list page
- [ ] Generation options page
  - [ ] From registrations
  - [ ] Bulk Excel upload
  - [ ] Single manual entry
- [ ] Excel template download
- [ ] Certificate preview modal
- [ ] Bulk selection interface
- [ ] Download/email options

### Features
- [ ] Generate from event registrations
- [ ] Excel template with dynamic columns
- [ ] Parse Excel and validate
- [ ] Bulk certificate generation
- [ ] Single certificate generation
- [ ] Unique certificate ID/number
- [ ] QR code with verification URL
- [ ] PDF storage
- [ ] Download single PDF
- [ ] Bulk download certificates (ZIP)
- [ ] Preview certificate before download
- [ ] Email single certificate
- [ ] Bulk email certificates (blast)
- [ ] Email queue for large batches
- [ ] Email template customization
- [ ] Regenerate certificate

---

## Phase 5: Verification System
### Backend
- [ ] Public verification controller
- [ ] Certificate lookup by ID

### Frontend
- [ ] Public verification page
- [ ] Certificate details display
- [ ] QR code scanner integration (optional)
- [ ] Invalid certificate message

### Features
- [ ] Verify by certificate number
- [ ] Display certificate info
- [ ] Show certificate PDF/image
- [ ] Verification status

---

## Phase 6: Additional Features
### Settings (Root Only)
- [ ] User management (CRUD)
- [ ] System settings
- [ ] Email configuration

### Reports & Analytics
- [ ] Total certificates generated
- [ ] Certificates by event
- [ ] Registration statistics
- [ ] Export reports

### UI/UX
- [ ] Responsive design
- [ ] Loading states
- [ ] Success/error notifications
- [ ] Form validation
- [ ] Confirmation modals

---

## Testing Checklist
- [ ] Template CRUD operations
- [ ] Field positioning accuracy
- [ ] Event creation with forms
- [ ] Public registration submission
- [ ] Certificate PDF generation
- [ ] Excel bulk upload
- [ ] QR code generation
- [ ] Public verification
- [ ] Role-based access
- [ ] File upload validation

---

## Deployment Checklist
- [ ] Environment configuration
- [ ] Database migration
- [ ] Storage permissions
- [ ] Queue worker setup
- [ ] Default data seeding
- [ ] PDF fonts configuration
- [ ] Backup strategy

---

## Notes
- Keep it simple, no over-engineering
- Ask before generating long code
- Use Bootstrap 5 (Metronic) components
- SQLite for database
- Store certificates in `/storage/app/certificates`
- Store templates in `/storage/app/templates`

---

## Current Progress
**Last Updated**: October 29, 2025
**Current Phase**: Phase 2 - Template Management ✅ COMPLETE
**Next Phase**: Phase 3 - Event Management

### Phase 1 Completed ✅:
- ✅ Laravel 12 installation
- ✅ SQLite database configured
- ✅ Role system implemented (Root = 1, User = 2)
- ✅ Middleware created (RootMiddleware, UserMiddleware, CheckActiveStatus)
- ✅ User model updated with role helper methods
- ✅ UserSeeder created with test accounts
- ✅ Database migrated and seeded
- ✅ Metronic template integration (by user)
- ✅ Dashboard layouts created (Root & User)
- ✅ Role-based dashboard controller

### Phase 2 Completed ✅:
- ✅ Templates & TemplateFields database tables
- ✅ Template and TemplateField models with relationships
- ✅ TemplateController with full CRUD operations
- ✅ Image upload handling for backgrounds
- ✅ Template routes with middleware protection
- ✅ Template index page with grid view
- ✅ Template create form with image preview
- ✅ Template edit page with Fabric.js editor
- ✅ Drag & drop field positioning
- ✅ Field properties panel (font, color, size, alignment)
- ✅ Save/load field positions
- ✅ Set default template feature
- ✅ Delete template with image cleanup

### Test Accounts:
- **Root**: root@certify.com / password
- **User**: user@certify.com / password
- **Inactive**: inactive@certify.com / password (for testing)

### Template Features:
- **Create Templates**: Upload background image (JPG/PNG, max 5MB)
- **Fabric.js Editor**: Visual drag-and-drop field positioning
- **Field Properties**: Name, type, font family, size, color, alignment, bold, italic, rotation
- **Default Template**: Set any template as default for events
- **Template Preview**: View all templates with thumbnail previews