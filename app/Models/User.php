<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'birthday',
        'nationality',
        'gender',
        'region',
        'banned_at',
        'pr0gramm_identifier',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'email',
        'birthday',
        'nationality',
        'gender',
        'region',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birthday' => 'date',
        'banned_at' => 'date',
    ];

    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // TODO: Update access-control
        return true;
    }

    public function isAdmin(): bool
    {
        return (bool)$this->admin;
    }

    public function scopeAdmin(Builder $query): void
    {
        $query->where('admin', true);
    }

    public function getPr0grammName(): string
    {
        return $this->name;
    }

    public function createAnonymousUser(): AnonymousUser
    {
        return AnonymousUser::create([
            'birthday' => $this->birthday,
            'nationality' => $this->nationality,
            'gender' => $this->gender,
            'region' => $this->region,
        ]);
    }
}
