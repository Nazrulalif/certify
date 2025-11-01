<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventField extends Model
{

    use SoftDeletes, HasUuids, HasFactory;
    protected $fillable = [
        'event_id',
        'field_name',
        'field_label',
        'field_type',
        'required',
        'options',
        'order',
    ];

    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
        'order' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public static function getFieldTypes(): array
    {
        return [
            'text' => 'Text',
            'email' => 'Email',
            'number' => 'Number',
            'date' => 'Date',
            'textarea' => 'Textarea',
            'select' => 'Select Dropdown',
        ];
    }
}
