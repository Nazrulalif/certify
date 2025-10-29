<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateField extends Model
{
    use SoftDeletes, HasUuids, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'template_id',
        'field_name',
        'field_type',
        'x',
        'y',
        'width',
        'height',
        'font_size',
        'font_family',
        'color',
        'text_align',
        'bold',
        'italic',
        'rotation',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'x' => 'decimal:2',
        'y' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'font_size' => 'integer',
        'bold' => 'boolean',
        'italic' => 'boolean',
        'rotation' => 'decimal:2',
    ];

    /**
     * Get the template that owns the field.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Get field styles as array for rendering.
     */
    public function getStylesAttribute(): array
    {
        return [
            'position' => 'absolute',
            'left' => $this->x . 'px',
            'top' => $this->y . 'px',
            'width' => $this->width ? $this->width . 'px' : 'auto',
            'height' => $this->height ? $this->height . 'px' : 'auto',
            'font-size' => $this->font_size . 'px',
            'font-family' => $this->font_family,
            'color' => $this->color,
            'text-align' => $this->text_align,
            'font-weight' => $this->bold ? 'bold' : 'normal',
            'font-style' => $this->italic ? 'italic' : 'normal',
            'transform' => 'rotate(' . $this->rotation . 'deg)',
        ];
    }
}
