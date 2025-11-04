<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventFieldValue extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_id',
        'template_field_id',
        'value',
    ];

    /**
     * Get the event that owns the field value.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the template field that this value belongs to.
     */
    public function templateField(): BelongsTo
    {
        return $this->belongsTo(TemplateField::class);
    }
}
