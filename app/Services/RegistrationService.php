<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\Validator;

class RegistrationService
{
    /**
     * Build validation rules from template fields
     */
    public function buildValidationRules(Event $event): array
    {
        $rules = [];
        $formFields = $event->template->formFields;
        
        foreach ($formFields as $field) {
            $fieldRules = [];
            
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }
            
            // Add type-specific validation
            switch ($field->field_type) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'text':
                case 'textarea':
                    $fieldRules[] = 'string';
                    if ($field->field_type === 'text') {
                        $fieldRules[] = 'max:255';
                    }
                    break;
            }
            
            $rules[$field->field_name] = implode('|', $fieldRules);
        }
        
        return $rules;
    }
    
    /**
     * Build validation messages from template fields
     */
    public function buildValidationMessages(Event $event): array
    {
        $messages = [];
        $formFields = $event->template->formFields;
        
        foreach ($formFields as $field) {
            $label = $field->field_label;
            
            $messages["{$field->field_name}.required"] = "The {$label} field is required.";
            $messages["{$field->field_name}.email"] = "The {$label} must be a valid email address.";
            $messages["{$field->field_name}.date"] = "The {$label} must be a valid date.";
            $messages["{$field->field_name}.numeric"] = "The {$label} must be a number.";
        }
        
        return $messages;
    }
    
    /**
     * Validate registration data against template
     */
    public function validateRegistrationData(Event $event, array $data): \Illuminate\Validation\Validator
    {
        $rules = $this->buildValidationRules($event);
        $messages = $this->buildValidationMessages($event);
        
        return Validator::make($data, $rules, $messages);
    }
    
    /**
     * Create registration with validation
     */
    public function createRegistration(Event $event, array $formData): Registration
    {
        // Validate data
        $validator = $this->validateRegistrationData($event, $formData);
        
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
        
        // Create registration
        $registration = Registration::create([
            'event_id' => $event->id,
            'form_data' => $validator->validated(),
            'status' => 'pending',
            'registered_at' => now(),
        ]);
        
        return $registration;
    }
    
    /**
     * Update registration data
     */
    public function updateRegistration(Registration $registration, array $formData): Registration
    {
        // Validate data against template
        $validator = $this->validateRegistrationData($registration->event, $formData);
        
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
        
        $registration->update([
            'form_data' => $validator->validated(),
        ]);
        
        return $registration->fresh();
    }
    
    /**
     * Get form field configuration for frontend
     */
    public function getFormConfiguration(Event $event): array
    {
        if (!$event->template) {
            throw new \Exception('Event template not found.');
        }
        
        $formFields = $event->template->formFields()
            ->orderBy('order')
            ->get();
        
        return [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'slug' => $event->slug,
            ],
            'fields' => $formFields->map(function ($field) {
                return [
                    'name' => $field->field_name,
                    'label' => $field->field_label,
                    'type' => $field->field_type,
                    'required' => $field->is_required,
                    'order' => $field->order,
                    'placeholder' => $this->getFieldPlaceholder($field),
                ];
            })->toArray(),
        ];
    }
    
    /**
     * Get appropriate placeholder text for field
     */
    protected function getFieldPlaceholder($field): string
    {
        $placeholders = [
            'name' => 'Enter your full name',
            'email' => 'Enter your email address',
            'phone' => 'Enter your phone number',
            'date' => 'Select date',
        ];
        
        return $placeholders[$field->field_name] ?? "Enter {$field->field_label}";
    }
    
    /**
     * Check if registration is open
     */
    public function isRegistrationOpen(Event $event): bool
    {
        return $event->registration_enabled;
    }
    
    /**
     * Approve registration
     */
    public function approveRegistration(Registration $registration): Registration
    {
        $registration->update(['status' => 'approved']);
        return $registration->fresh();
    }
    
    /**
     * Reject registration
     */
    public function rejectRegistration(Registration $registration): Registration
    {
        $registration->update(['status' => 'rejected']);
        return $registration->fresh();
    }
    
    /**
     * Bulk approve registrations
     */
    public function bulkApproveRegistrations(array $registrationIds): array
    {
        $approved = [];
        
        foreach ($registrationIds as $id) {
            try {
                $registration = Registration::findOrFail($id);
                $this->approveRegistration($registration);
                $approved[] = $registration;
            } catch (\Exception $e) {
                // Log error but continue
            }
        }
        
        return $approved;
    }
}
