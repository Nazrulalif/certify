<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Event extends Model
{
    use SoftDeletes, HasUuids, HasFactory, Searchable;
    protected $fillable = [
        'name',
        'description',
        'template_id',
        'registration_enabled',
        'slug',
        'static_values',
        'created_by',
    ];

    protected $casts = [
        'registration_enabled' => 'boolean',
        'static_values' => 'array',
    ];

        /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->name);

                // Ensure unique slug
                $count = 1;
                $originalSlug = $event->slug;
                while (static::where('slug', $event->slug)->exists()) {
                    $event->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(EventField::class)->orderBy('order');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function getPublicUrlAttribute(): string
    {
        return url("/register/{$this->slug}");
    }

    /**
     * Get certificate data for a registration (merge static + form data).
     */
    public function getCertificateData(Registration $registration): array
    {
        return array_merge(
            $this->static_values ?? [],
            $registration->form_data ?? []
        );
    }

    /**
     * Get fields that need static values.
     */
    public function getStaticValueFields()
    {
        if (!$this->template) {
            return collect();
        }
        
        return $this->template->staticValueFields;
    }

    /**
     * Validate static values against template.
     */
    public function validateStaticValues(array $staticValues): bool
    {
        $requiredFields = $this->getStaticValueFields()
            ->pluck('field_name')
            ->toArray();
        
        foreach ($requiredFields as $field) {
            if (!isset($staticValues[$field]) || empty($staticValues[$field])) {
                throw new \Exception("Static value for '{$field}' is required.");
            }
        }
        
        return true;
    }
}
