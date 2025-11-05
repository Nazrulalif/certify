# Phase 3: API Routes Reference

## Template Field Management Routes

### Add Custom Field
```
POST /templates/{template}/fields
```
**Body:**
```json
{
  "field_name": "phone",
  "field_label": "Phone Number",
  "field_type": "text",
  "show_in_form": true,
  "show_in_cert": false,
  "is_required": false
}
```

### Update Field Properties
```
PATCH /template-fields/{field}
```
**Body:**
```json
{
  "field_label": "Updated Label",
  "show_in_form": true,
  "show_in_cert": true,
  "is_required": false
}
```

### Update Field Position
```
PATCH /template-fields/{field}/position
```
**Body:**
```json
{
  "position_data": {
    "x": 100,
    "y": 200,
    "fontSize": 24,
    "fontFamily": "Arial",
    "color": "#000000",
    "textAlign": "left",
    "bold": false,
    "italic": false,
    "rotation": 0
  }
}
```

### Delete Custom Field
```
DELETE /template-fields/{field}
```

### Get Canvas Fields (show_in_cert = true)
```
GET /templates/{template}/canvas-fields
```

### Get Form Fields (show_in_form = true)
```
GET /templates/{template}/form-fields
```

---

## Event Configuration Routes

### Get Static Value Fields
```
GET /templates/{template}/static-value-fields
```
**Returns:** Fields that need static values (show_in_form=false, show_in_cert=true)

### Get Registration Form Preview
```
GET /events/{event}/registration-form-preview
```
**Returns:** Form fields configuration for public registration

### Get Event Configuration Summary
```
GET /events/{event}/configuration-summary
```
**Returns:** Complete overview of event configuration

### Save Static Values
```
POST /events/{event}/static-values
```
**Body:**
```json
{
  "static_values": {
    "event_name": "Laravel Workshop 2025",
    "date": "2025-11-10"
  }
}
```

---

## Registration Routes

### Show Public Registration Form
```
GET /register/{slug}
```
Public route - displays dynamic form based on template fields

### Submit Registration
```
POST /register/{slug}
```
**Body:** Dynamic based on template fields
```json
{
  "name": "John Doe",
  "email": "john@example.com"
}
```

---

## Usage Examples

### Creating a Template with Fields

1. **Create Template:**
```bash
POST /templates
```

2. **Add Custom Field:**
```bash
POST /templates/{template_id}/fields
Body: { "field_name": "company", "field_label": "Company Name", ... }
```

3. **Update Field Position on Canvas:**
```bash
PATCH /template-fields/{field_id}/position
Body: { "position_data": { "x": 150, "y": 250, ... } }
```

### Creating an Event

1. **Create Event:**
```bash
POST /events
Body: { "name": "Workshop", "template_id": "...", ... }
```

2. **Set Static Values:**
```bash
POST /events/{event_id}/static-values
Body: { "static_values": { "event_name": "Workshop", "date": "2025-11-10" } }
```

3. **Preview Registration Form:**
```bash
GET /events/{event_id}/registration-form-preview
```

### Public Registration Flow

1. **User visits:**
```
GET /register/workshop-slug
```

2. **Form submits to:**
```
POST /register/workshop-slug
```

3. **Data stored in:**
```
registrations.form_data (JSON)
```

4. **Certificate generated with:**
```
Merged data = event.static_values + registration.form_data
```

---

## Testing with cURL

### Add Field
```bash
curl -X POST http://localhost/templates/{id}/fields \
  -H "Content-Type: application/json" \
  -d '{"field_name":"phone","field_label":"Phone","field_type":"text","show_in_form":true,"show_in_cert":false,"is_required":false}'
```

### Get Canvas Fields
```bash
curl http://localhost/templates/{id}/canvas-fields
```

### Save Static Values
```bash
curl -X POST http://localhost/events/{id}/static-values \
  -H "Content-Type: application/json" \
  -d '{"static_values":{"event_name":"Workshop 2025","date":"2025-11-10"}}'
```

---

## Route Names for Blade Views

```php
// Template Field Management
route('templates.fields.add', $template)
route('template-fields.update', $field)
route('template-fields.update-position', $field)
route('template-fields.delete', $field)
route('templates.canvas-fields', $template)
route('templates.form-fields', $template)

// Event Configuration
route('templates.static-value-fields', $template)
route('events.registration-form-preview', $event)
route('events.configuration-summary', $event)
route('events.save-static-values', $event)

// Public Registration
route('register.show', $event->slug)
route('register.store', $event->slug)
```

---

## Status: ✅ All Routes Configured
