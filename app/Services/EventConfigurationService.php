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
                    'id' => $field->id,
                    'name' => $field->field_name,
                    'label' => $field->field_label,
                    'type' => $field->field_type,
                    'required' => $field->is_required,
                    'order' => $field->order,
                ];
            });
    }
    
    /**
     * Validate event configuration before activation
     */
    public function validateEventConfiguration(Event $event): bool
    {
        // Check if template exists
        if (!$event->template) {
            throw new \Exception('Event must have a template.');
        }
        
        // Check if static values are set
        $staticFields = $this->getStaticValueFields($event->template);
        
        if ($staticFields->isNotEmpty()) {
            foreach ($staticFields as $field) {
                if (!isset($event->static_values[$field->field_name]) || 
                    empty($event->static_values[$field->field_name])) {
                    throw new \Exception("Static value for '{$field->field_label}' is required.");
                }
            }
        }
        
        return true;
    }
    
    /**
     * Get complete event configuration summary
     */
    public function getEventConfigurationSummary(Event $event): array
    {
        $template = $event->template;
        
        if (!$template) {
            throw new \Exception('Template not found.');
        }
        
        return [
            'event_name' => $event->name,
            'template_name' => $template->name,
            'static_values' => $event->static_values ?? [],
            'form_fields' => $this->getRegistrationFormPreview($event),
            'certificate_fields' => $template->certFields()
                ->orderBy('order')
                ->get()
                ->map(function ($field) {
                    return [
                        'name' => $field->field_name,
                        'label' => $field->field_label,
                        'type' => $field->field_type,
                        'has_position' => !empty($field->position_data),
                    ];
                }),
            'registration_enabled' => $event->registration_enabled,
            'public_url' => $event->public_url,
        ];
    }
    
    /**
     * Initialize event with default static values from template
     */
    public function initializeEventStaticValues(Event $event): array
    {
        $staticFields = $this->getStaticValueFields($event->template);
        $defaultValues = [];
        
        foreach ($staticFields as $field) {
            // Provide default values based on field name
            if ($field->field_name === 'event_name') {
                $defaultValues['event_name'] = $event->name;
            } elseif ($field->field_name === 'date') {
                $defaultValues['date'] = now()->format('Y-m-d');
            } else {
                $defaultValues[$field->field_name] = '';
            }
        }
        
        return $defaultValues;
    }
}
