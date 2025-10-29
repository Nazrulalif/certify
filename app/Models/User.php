<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasUuids, Searchable;

    /**
     * Role constants
     */
    const ROLE_ROOT = 1;
    const ROLE_USER = 2;

    /**
     * Role labels
     */
    const ROLES = [
        self::ROLE_ROOT => 'Root',
        self::ROLE_USER => 'User',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
            'role' => 'integer',
        ];
    }

        /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->getRoleName(),
        ];
    }

    public function getAvatarAttribute() {
        $name = urlencode($this->name);
        $bgColor = substr(md5($this->name), 0, 6); // Hasilkan warna tetap ikut nama
        return "https://ui-avatars.com/api/?name={$name}&size=128&background={$bgColor}&color=fff";
    }

    /**
     * Check if user is root
     */
    public function isRoot(): bool
    {
        return $this->role === self::ROLE_ROOT;
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === true;
    }

    /**
     * Get role name
     */
    public function getRoleName(): string
    {
        return self::ROLES[$this->role] ?? 'Unknown';
    }

    /**
     * Scope to get only active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope to get only root users
     */
    public function scopeRoots($query)
    {
        return $query->where('role', self::ROLE_ROOT);
    }

    /**
     * Scope to get only regular users
     */
    public function scopeUsers($query)
    {
        return $query->where('role', self::ROLE_USER);
    }
}
