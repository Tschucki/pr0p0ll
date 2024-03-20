<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Polls\Poll;
use Cog\Contracts\Ban\Bannable as BannableInterface;
use Cog\Laravel\Ban\Traits\Bannable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements BannableInterface, FilamentUser, HasAvatar, MustVerifyEmail
{
    use Bannable;
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
        'email_verified_at',
        'password',
        'birthday',
        'nationality',
        'gender',
        'region',
        'banned_at',
        'pr0gramm_identifier',
        'last_data_change',
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
        'last_data_change' => 'datetime',
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
        return (bool) $this->admin;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return asset('images/feiner-schmuser.png');
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
        return AnonymousUser::create($this->getDemographicData());
    }

    public function getDemographicData(): array
    {
        return [
            'birthday' => $this->birthday,
            'nationality' => $this->nationality,
            'gender' => $this->gender,
            'region' => $this->region,
        ];
    }

    public function notificationSettings(): HasMany
    {
        return $this->hasMany(NotificationSetting::class, 'user_id');
    }

    public function getNotificationSettingsForForm(): array
    {
        $notificationSettings = [];
        NotificationChannel::each(function (NotificationChannel $notificationChannel) use (&$notificationSettings) {
            $notificationSettings[$notificationChannel->getKey()] = [];
            NotificationType::each(function (NotificationType $notificationType) use (&$notificationSettings, $notificationChannel) {
                $notificationSettings[$notificationChannel->getKey()][$notificationType->getKey()] = $this->notificationSettings()->where([
                    'notification_channel_id' => $notificationChannel->getKey(),
                    'notification_type_id' => $notificationType->getKey(),
                ])->value('enabled') ?? false;
            });
        });

        return $notificationSettings;
    }

    public function updateNotificationSettings(array $notificationSettings): void
    {
        foreach ($notificationSettings as $notificationChannelKey => $notificationSetting) {
            foreach ($notificationSetting as $notificationTypeKey => $notificationSettingValue) {
                $this->notificationSettings()->updateOrCreate(
                    [
                        'user_id' => $this->getKey(),
                        'notification_channel_id' => $notificationChannelKey,
                        'notification_type_id' => $notificationTypeKey,
                    ],
                    [
                        'enabled' => $notificationSettingValue,
                        'notification_channel_id' => $notificationChannelKey,
                        'notification_type_id' => $notificationTypeKey,
                    ]);
            }
        }
    }

    public function wantsNotification(NotificationChannel $notificationChannel, NotificationType $notificationType): bool
    {
        return $this->notificationSettings()->where([
            'notification_channel_id' => $notificationChannel->getKey(),
            'notification_type_id' => $notificationType->getKey(),
            'enabled' => true,
        ])->exists();
    }

    public function getNotificationRoutesForType(NotificationType $notificationType): array
    {
        return $this->notificationSettings()->with('notificationChannel')->where('notification_type_id', $notificationType->getKey())->where('enabled', true)->get()->pluck('notificationChannel.route')->toArray();
    }

    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class, 'user_id');
    }

    public function participations(): BelongsToMany
    {
        return $this->belongsToMany(Poll::class, 'participants_2_polls', 'participant_id', 'poll_id')
            ->withTimestamps()
            ->withPivot([
                'rating',
            ]);
    }

    public function canUpdateDemographicData(): bool
    {
        $dLastChange = Carbon::make($this->last_data_change);

        if ($dLastChange === null) {
            return true;
        }

        return $dLastChange->addMonths(2)->isPast();
    }

    public function approvedPolls(): HasMany
    {
        return $this->hasMany(Poll::class, 'user_id')->where('approved', true);
    }
}
