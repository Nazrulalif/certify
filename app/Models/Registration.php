<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use SoftDeletes, HasUuids, HasFactory;
    protected $fillable = [
        'event_id',
        'form_data',
        'status',
        'registered_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'registered_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    public function getFieldValue(string $fieldName): ?string
    {
        return $this->form_data[$fieldName] ?? null;
    }

    /**
     * Validate registration data against template fields.
     */
    public function validateAgainstTemplate(): bool
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

    /**
     * Get complete certificate data (form data + static values).
     */
    public function getCertificateData(): array
    {
        if (!$this->event) {
            throw new \Exception('Event not found.');
        }
        
        return $this->event->getCertificateData($this);
    }

    public static function getStatuses(): array
    {
        return [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
    }
}
