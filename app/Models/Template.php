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
    }

    /**
     * Get the fields for the template.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(TemplateField::class);
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
