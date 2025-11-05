<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateField extends Model
{
    use HasUuids, HasFactory;
    // use SoftDeletes, HasUuids, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'template_id',
        'field_name',
        'field_label',
        'field_type',
        'show_in_form',
        'show_in_cert',
        'is_required',
        'is_predefined',
        'position_data',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'show_in_form' => 'boolean',
        'show_in_cert' => 'boolean',
        'is_required' => 'boolean',
        'is_predefined' => 'boolean',
        'position_data' => 'array',
    ];

    /**
     * Get the template that owns the field.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Scope: Only fields shown on certificate
     */
    public function scopeCertificateFields($query)
    {
        return $query->where('show_in_cert', true);
    }

    /**
     * Scope: Only fields shown in registration form
     */
    public function scopeFormFields($query)
    {
        return $query->where('show_in_form', true);
    }

    /**
     * Scope: Fields that need static values in events (not in form but on cert)
     */
    public function scopeStaticValueFields($query)
    {
        return $query->where('show_in_form', false)
                     ->where('show_in_cert', true);
    }

    /**
     * Get field styles as array for rendering.
     * Works with position_data JSON structure
     */
    public function getStylesAttribute(): array
    {
        $positionData = $this->position_data ?? [];
        
        return [
            'position' => 'absolute',
            'left' => ($positionData['x'] ?? 0) . 'px',
            'top' => ($positionData['y'] ?? 0) . 'px',
            'width' => isset($positionData['width']) ? $positionData['width'] . 'px' : 'auto',
            'height' => isset($positionData['height']) ? $positionData['height'] . 'px' : 'auto',
            'font-size' => ($positionData['fontSize'] ?? 16) . 'px',
            'font-family' => $positionData['fontFamily'] ?? 'Arial',
            'color' => $positionData['color'] ?? '#000000',
            'text-align' => $positionData['textAlign'] ?? 'left',
            'font-weight' => ($positionData['bold'] ?? false) ? 'bold' : 'normal',
            'font-style' => ($positionData['italic'] ?? false) ? 'italic' : 'normal',
            'transform' => 'rotate(' . ($positionData['rotation'] ?? 0) . 'deg)',
        ];
    }
}
