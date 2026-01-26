<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

final class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_guest',
        'guest_token',
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
     * Create a guest user
     */
    /** @phpstan-ignore-next-line */
    public static function createGuest(array $data = []): self
    {
        return self::create(array_merge([
            'is_guest' => true,
            'guest_token' => Str::random(32),
            'password' => bcrypt(Str::random(20)), // Random password for security
        ], $data));
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get all addresses for this user
     */
    /** @phpstan-ignore-next-line */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get orders for this user
     */
    /** @phpstan-ignore-next-line */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get primary billing address
     */
    public function primaryBillingAddress(): ?Address
    {
        return $this->addresses()
            ->billing()
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Get primary shipping address
     */
    public function primaryShippingAddress(): ?Address
    {
        return $this->addresses()
            ->shipping()
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Convert guest to registered user
     */
    public function convertToRegistered(string $password): bool
    {
        return $this->update([
            'is_guest' => false,
            'password' => bcrypt($password),
            'guest_token' => null,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Check if user is a guest
     */
    public function isGuest(): bool
    {
        return $this->is_guest;
    }

    /**
     * Check if user is registered
     */
    public function isRegistered(): bool
    {
        return ! $this->is_guest;
    }

    /**
     * Scope for guest users
     */
    /** @phpstan-ignore-next-line */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function guests($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_guest', true);
    }

    /**
     * Scope for registered users
     */
    /** @phpstan-ignore-next-line */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function registered($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_guest', false);
    }

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
            'is_guest' => 'boolean',
        ];
    }
}
