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
        $maxOrder = $template->fields()->max('order') ?? 0;
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
        
        // Prevent changing is_predefined flag
        if (isset($data['is_predefined'])) {
            unset($data['is_predefined']);
        }
        
        $field->update($data);
        return $field->fresh();
    }
    
    /**
     * Update field position on canvas
     */
    public function updateFieldPosition(TemplateField $field, array $positionData)
    {
        $field->position_data = array_merge($field->position_data ?? [], $positionData);
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
        
        return $template->fields()->orderBy('order')->get();
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
    
    /**
     * Bulk update field toggles
     */
    public function updateFieldToggles(TemplateField $field, bool $showInForm, bool $showInCert, bool $isRequired)
    {
        $field->update([
            'show_in_form' => $showInForm,
            'show_in_cert' => $showInCert,
            'is_required' => $isRequired,
        ]);
        
        return $field->fresh();
    }
    
    /**
     * Get field by name
     */
    public function getFieldByName(Template $template, string $fieldName): ?TemplateField
    {
        return $template->fields()
            ->where('field_name', $fieldName)
            ->first();
    }
}
