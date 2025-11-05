<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use SoftDeletes, HasUuids, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'background',
        'is_default',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot()
    {
        parent::boot();

        // When deleting a template, clean up the background image
        static::deleting(function ($template) {
            if ($template->background && \Storage::disk('public')->exists($template->background)) {
                \Storage::disk('public')->delete($template->background);
            }
        });

        // Initialize predefined fields when creating template
        static::created(function ($template) {
            $predefinedFields = [
                [
                    'field_name' => 'name',
                    'field_label' => 'Participant Name',
                    'field_type' => 'text',
                    'show_in_form' => true,
                    'show_in_cert' => true,
                    'is_required' => true,
                    'is_predefined' => true,
                    'order' => 1,
                    'position_data' => [
                        'x' => 50,
                        'y' => 50,
                        'fontSize' => 30,
                    ]
                ],
                [
                    'field_name' => 'email',
                    'field_label' => 'Email Address',
                    'field_type' => 'email',
                    'show_in_form' => true,
                    'show_in_cert' => false,
                    'is_required' => true,
                    'is_predefined' => true,
                    'order' => 2,
                    'position_data' => [
                        'x' => 50,
                        'y' => 40,
                        'fontSize' => 30,
                    ]
                ],
                [
                    'field_name' => 'event_name',
                    'field_label' => 'Event Name',
                    'field_type' => 'text',
                    'show_in_form' => false,
                    'show_in_cert' => true,
                    'is_required' => false,
                    'is_predefined' => true,
                    'order' => 3,
                    'position_data' => [
                        'x' => 50,
                        'y' => 30,
                        'fontSize' => 30,
                    ]
                ],
                [
                    'field_name' => 'date',
                    'field_label' => 'Event Date',
                    'field_type' => 'date',
                    'show_in_form' => false,
                    'show_in_cert' => true,
                    'is_required' => false,
                    'is_predefined' => true,
                    'order' => 4,
                    'position_data' => [
                        'x' => 50,
                        'y' => 20,
                        'fontSize' => 30,
                    ]
                ]
            ];
            
            foreach ($predefinedFields as $field) {
                $template->fields()->create($field);
            }
        });
    }

    /**
     * Get the fields for the template.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(TemplateField::class)->orderBy('order');
    }

    /**
     * Get only fields shown in registration form.
     */
    public function formFields(): HasMany
    {
        return $this->hasMany(TemplateField::class)->formFields()->orderBy('order');
    }

    /**
     * Get only fields shown on certificate.
     */
    public function certFields(): HasMany
    {
        return $this->hasMany(TemplateField::class)->certificateFields()->orderBy('order');
    }

    /**
     * Get fields that need static values (not in form but on cert).
     */
    public function staticValueFields(): HasMany
    {
        return $this->hasMany(TemplateField::class)->staticValueFields()->orderBy('order');
    }

    /**
     * Get the events using this template.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the user who created the template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the template.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the background image URL.
     */
    public function getBackgroundUrlAttribute(): ?string
    {
        return $this->background ? asset('storage/' . $this->background) : null;
    }

    /**
     * Scope to get default template.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

   
}
