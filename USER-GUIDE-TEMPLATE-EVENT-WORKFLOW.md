# User Guide: Template-Event Certificate Workflow

## Overview
This guide explains the complete workflow for creating certificate templates, configuring events, and generating certificates using the refactored system.

## Key Concepts

### 1. **Templates** (Single Source of Truth)
Templates define:
- Certificate design (background image)
- All available fields with their properties
- Field visibility rules (form vs certificate)
- Field requirements and validation

### 2. **Template Fields**
Each field has:
- **Field Name**: Unique identifier (e.g., `name`, `email`, `company`)
- **Field Label**: User-friendly display name
- **Field Type**: text, email, date, number, textarea
- **Show in Form**: Display in registration form for participants to fill
- **Show in Cert**: Display on generated certificate
- **Required**: Must be filled if shown in form

### 3. **Predefined Fields** (Auto-created)
Every template automatically gets 4 predefined fields:
- **name**: Participant name (form + cert)
- **email**: Participant email (form only)
- **event_name**: Event name (cert only, static per event)
- **date**: Event date (cert only, static per event)

### 4. **Static Values**
Fields shown only on certificate (not in form) must have static values set per event. These values appear on ALL certificates for that event.

---

## Workflow

### Step 1: Create a Template

1. **Navigate to Templates** → Click "Create Template"
2. **Upload Background Image**: Choose your certificate background (PNG/JPG)
3. **Enter Template Name**: e.g., "Workshop Certificate 2024"
4. **Save**: Template is created with 4 predefined fields automatically

### Step 2: Configure Template Fields

After creation, you'll see the Template Editor with:
- **Field Configuration Table** (top): Shows all fields with toggle checkboxes
- **Canvas Editor** (bottom): Visual positioning of certificate fields

#### Field Configuration Table:
- ✅ **Show in Form**: Check to include in registration form
- ✅ **Show in Cert**: Check to display on certificate
- ✅ **Required**: Check to make field mandatory (only if shown in form)

#### Working with Fields:

**A. Predefined Fields** (Cannot be deleted):
- **name**: Already checked for both form and cert
- **email**: Already checked for form only
- **event_name**: Already checked for cert only (requires static value)
- **date**: Already checked for cert only (requires static value)

**B. Add Custom Fields**:
1. Click "Add Custom Field" button
2. Fill in:
   - Field Name: `company_name` (lowercase, underscores)
   - Field Label: `Company Name`
   - Field Type: `text`
   - ☑ Show in Registration Form (if participants should fill it)
   - ☑ Show on Certificate (if it should appear on cert)
   - ☑ Required Field (if mandatory)
3. Click "Add Field"

**C. Toggle Field Visibility**:
- **Uncheck "Show in Cert"**: Field is removed from canvas immediately
- **Check "Show in Cert"**: Field is added to canvas automatically
- **Uncheck "Show in Form"**: "Required" checkbox is disabled

**D. Delete Custom Fields**:
- Click trash icon next to custom field
- Predefined fields show "Protected" and cannot be deleted

### Step 3: Position Fields on Certificate Canvas

1. **Only fields with "Show in Cert" ✓ appear on canvas**
2. **Drag fields** to desired position
3. **Click field** to see properties panel:
   - Font Size
   - Font Family
   - Color
   - Bold/Italic
   - Text Alignment
4. **Apply Properties** to update selected field
5. **Save Fields** button to save all positions to database

**Tips:**
- Use "Download Preview" to test certificate with dummy data
- Fields auto-scale to match original image dimensions
- Rotation supported via canvas controls

### Step 4: Create an Event

1. **Navigate to Events** → Click "Create Event"
2. **Fill Basic Info**:
   - Event Name: e.g., "AI Workshop - January 2025"
   - Description: Optional event details
   - **Select Template**: Choose from dropdown
3. **When template is selected**, two sections appear:

#### A. Certificate Static Values:
- Shows fields marked "Show in Cert" but NOT "Show in Form"
- For predefined fields: `event_name` and `date`
- For any custom cert-only fields
- **Fill these values** - they apply to ALL certificates for this event

Example:
```
Event Name: AI Workshop - January 2025
Date: 2025-01-15
```

#### B. Registration Form Preview:
- Shows exactly how participants will see the form
- Displays all fields marked "Show in Form"
- Shows required field indicators (*)
- Use "Refresh" to update after template changes

4. **Enable/Disable Registration** toggle
5. **Create Event**

### Step 5: Participants Register

**Public URL**: `yoursite.com/register/{event-slug}`

Participants see a form with:
- All fields where "Show in Form" is checked
- Required fields marked with *
- Validation based on field types (email, date, etc.)

**Form Data Collected**:
- Predefined: `name`, `email`
- Custom fields: `company_name`, `position`, etc.

**Not Collected** (static values):
- `event_name` (admin set at event level)
- `date` (admin set at event level)

### Step 6: Generate Certificates

#### Automatic Generation:
- System merges: **Registration Form Data** + **Event Static Values**
- Certificate shows ALL fields marked "Show in Cert"
- Positioned exactly as designed in template editor

#### Certificate Data Example:
```json
{
  "name": "John Doe",           // From registration form
  "email": "john@example.com",  // From registration form (not on cert)
  "event_name": "AI Workshop",  // From event static values
  "date": "2025-01-15",         // From event static values
  "company_name": "Acme Corp"   // From registration form
}
```

#### Admin Actions:
- View registrations for event
- Generate individual certificates
- Bulk generate all certificates
- Download PDF certificates

---

## Field Visibility Matrix

| Field Config | In Reg Form? | On Certificate? | Data Source | Example Use Case |
|-------------|-------------|----------------|-------------|------------------|
| Form ✓ Cert ✓ | Yes | Yes | Participant fills | Participant name |
| Form ✓ Cert ✗ | Yes | No | Participant fills | Email (for contact only) |
| Form ✗ Cert ✓ | No | Yes | Admin sets (static) | Event name, event date |
| Form ✗ Cert ✗ | No | No | N/A | Disabled field |

---

## Common Scenarios

### Scenario 1: Standard Workshop Certificate
**Fields:**
- `name`: Form ✓, Cert ✓, Required ✓
- `email`: Form ✓, Cert ✗, Required ✓
- `event_name`: Form ✗, Cert ✓ (static: "AI Masterclass")
- `date`: Form ✗, Cert ✓ (static: "2025-01-15")

**Result:** Simple form with name + email, certificate shows name + event + date

### Scenario 2: Company Training Certificate
**Fields:**
- `name`: Form ✓, Cert ✓, Required ✓
- `email`: Form ✓, Cert ✗, Required ✓
- `company_name`: Form ✓, Cert ✓, Required ✓
- `department`: Form ✓, Cert ✓, Required ✗
- `event_name`: Form ✗, Cert ✓ (static)
- `date`: Form ✗, Cert ✓ (static)

**Result:** Form collects company details, certificate includes company info

### Scenario 3: Conference Certificate with Speaker Name
**Fields:**
- `name`: Form ✓, Cert ✓, Required ✓ (attendee)
- `email`: Form ✓, Cert ✗, Required ✓
- `event_name`: Form ✗, Cert ✓ (static: "Tech Conference 2025")
- `speaker`: Form ✗, Cert ✓ (static: "Dr. Jane Smith")
- `date`: Form ✗, Cert ✓ (static: "2025-03-10")

**Result:** Attendees register with just name/email, cert shows conference + speaker + date

---

## Best Practices

### Template Design:
1. ✅ Create template background first (design in Canva/Photoshop)
2. ✅ Start with predefined fields, add custom as needed
3. ✅ Test with "Download Preview" before creating events
4. ✅ Use consistent font sizes (16-24pt works well)
5. ✅ Position fields with adequate spacing

### Field Management:
1. ✅ Use clear field labels ("Full Name" not "name")
2. ✅ Keep field names lowercase with underscores
3. ✅ Mark email fields for collecting contact info
4. ✅ Use "Required" for essential information only
5. ✅ Add help text for complex fields

### Event Configuration:
1. ✅ Fill all static values before enabling registration
2. ✅ Use registration preview to verify form appearance
3. ✅ Test registration flow before going live
4. ✅ Disable registration after event ends

### Certificate Generation:
1. ✅ Review registrations before bulk generation
2. ✅ Verify static values are correct
3. ✅ Generate test certificate first
4. ✅ Download certificates immediately after generation

---

## Troubleshooting

### "Field not appearing on canvas"
➜ Check "Show in Cert" is enabled in Field Configuration Table

### "Required checkbox is disabled"
➜ "Show in Form" must be checked first to enable "Required"

### "Static values section not showing"
➜ Template must have at least one field with "Show in Cert ✓" and "Show in Form ✗"

### "Registration form is empty"
➜ Template must have at least one field with "Show in Form ✓"

### "Certificate missing field data"
➜ Check if field requires static value (cert only) and verify event static values are filled

### "Cannot delete field"
➜ Predefined fields (name, email, event_name, date) are protected and cannot be deleted

---

## API Endpoints (For Developers)

### Template Field Management:
- `POST /templates/{id}/fields` - Add custom field
- `PATCH /template-fields/{id}` - Update field properties
- `PATCH /template-fields/{id}/position` - Update field position
- `DELETE /template-fields/{id}` - Delete custom field

### Event Configuration:
- `GET /templates/{id}/static-value-fields` - Get fields needing static values
- `GET /templates/{id}/registration-form-preview` - Get form preview from template
- `POST /events/{id}/static-values` - Save event static values
- `GET /events/{id}/registration-form-preview` - Get form preview for event

### Registration:
- `GET /register/{slug}` - Public registration form
- `POST /register/{slug}` - Submit registration
- `GET /events/{id}/registrations` - List registrations (admin)

---

## Database Schema Quick Reference

### `template_fields` Table:
```
id, template_id, field_name, field_label, field_type,
show_in_form, show_in_cert, is_required, is_predefined,
order, position_data (JSON)
```

### `events` Table:
```
id, template_id, name, slug, description,
registration_enabled, static_values (JSON)
```

### `registrations` Table:
```
id, event_id, form_data (JSON), registered_at, status
```

### `certificates` Table:
```
id, registration_id, event_id, certificate_data (JSON),
pdf_path, issued_at
```

---

## Version History
- **v2.0** (Phase 4 Complete): Full refactor with unified field system
- **v1.0** (Legacy): Separate EventField model (deprecated)

---

## Support
For technical issues or questions, contact the development team or refer to:
- `PHASE4-FRONTEND-GUIDE.md` - Frontend implementation details
- `PHASE3-ROUTES.md` - Complete route documentation
- `template-event-refactor.md` - Technical architecture overview
