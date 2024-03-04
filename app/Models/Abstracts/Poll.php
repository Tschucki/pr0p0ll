<?php

declare(strict_types=1);

namespace App\Models\Abstracts;

use App\Jobs\SendNewPollAvailableEmailNotification;
use App\Jobs\SendNewPollAvailablePr0grammNotification;
use App\Jobs\SendPollAcceptedEmailNotification;
use App\Jobs\SendPollAcceptedPr0grammNotification;
use App\Jobs\SendPollAcceptedTelegramNotification;
use App\Jobs\SendPollDeclinedEmailNotification;
use App\Jobs\SendPollDeclinedPr0grammNotification;
use App\Jobs\SendPollPublishedDiscordNotification;
use App\Models\Answer;
use App\Models\Category;
use App\Models\NotificationChannel;
use App\Models\NotificationType;
use App\Models\Question;
use App\Models\User;
use App\Services\TargetGroupService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

abstract class Poll extends Model
{
    protected $guarded = [];

    protected $table = 'polls';

    protected $casts = [
        'published_at' => 'datetime',
        'visible_to_public' => 'boolean',
        'in_review' => 'boolean',
        'approved' => 'boolean',
        'not_anonymous' => 'boolean',
        'target_group' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'poll_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'poll_id');
    }

    public function userParticipated(User $user): bool
    {
        return $this->participants()->where('participant_id', $user->getKey())->exists();
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'participants_2_polls', 'poll_id', 'participant_id')
            ->withTimestamps()
            ->withPivot([
                'rating',
            ]);
    }

    public function getBuilderData(): array
    {
        return $this->questions->map(function (Question $question) {
            $type = $question->questionType;

            return [
                'id' => $question->getKey(),
                'type' => (string) ($type->getKey()),
                'data' => [
                    'question_type_id' => $type->getKey(),
                    'title' => $question->title,
                    'description' => $question->description,
                    'options' => $question->options,
                ],
            ];
        })->toArray();
    }

    public function isInReview(): bool
    {
        return (bool) $this->in_review;
    }

    public function isApproved(): bool
    {
        return (bool) $this->approved;
    }

    public function isVisibleForPublic(): bool
    {
        return $this->isApproved() && ! $this->isInReview() && $this->visible_to_public;
    }

    public function resultsArePublic(): bool
    {
        if ($this->published_at !== null && $this->close_after !== null) {
            return Carbon::make($this->published_at)?->add($this->close_after)->isPast();
        }

        return false;
    }

    public function approve(): void
    {
        $this->update([
            'approved' => true,
            'in_review' => false,
            'visible_to_public' => true,
            'published_at' => now(),
        ]);
        /**
         * @var User $user
         * */
        $user = $this->user;
        $poll = \App\Models\Polls\Poll::find($this->getKey());
        SendPollAcceptedEmailNotification::dispatch($poll, $user);
        SendPollAcceptedPr0grammNotification::dispatch($poll, $user);
        SendPollPublishedDiscordNotification::dispatch($poll);
        SendPollAcceptedTelegramNotification::dispatch($poll);
        $usersForMail = User::whereHas('notificationSettings', function (Builder $query) {
            $query->where('notification_type_id', NotificationType::where('identifier', \App\Enums\NotificationType::NEWPOLLPUBLISHED)->first()->getKey())
                ->where('notification_channel_id', NotificationChannel::where('route', 'mail')->first()->getKey())
                ->where('enabled', true);
        })->get();
        $usersForPr0 = User::whereHas('notificationSettings', function (Builder $query) {
            $query->where('notification_type_id', NotificationType::where('identifier', \App\Enums\NotificationType::NEWPOLLPUBLISHED)->first()->getKey())
                ->where('notification_channel_id', NotificationChannel::where('route', 'pr0gramm')->first()->getKey())
                ->where('enabled', true);
        })->get();
        foreach ($usersForMail as $user) {
            SendNewPollAvailableEmailNotification::dispatch($poll, $user);
        }
        foreach ($usersForMail as $user) {
            SendNewPollAvailablePr0grammNotification::dispatch($poll, $user);
        }
    }

    public function deny(string $reason): void
    {
        $this->update([
            'approved' => false,
            'in_review' => false,
            'visible_to_public' => false,
            'published_at' => null,
            'admin_notes' => $reason,
        ]);
        /**
         * @var User $user
         * */
        $user = $this->user;
        $poll = \App\Models\Polls\Poll::find($this->getKey());
        SendPollDeclinedEmailNotification::dispatch($poll, $user);
        SendPollDeclinedPr0grammNotification::dispatch($poll, $user);
    }

    public function hasEnded(): bool
    {
        return $this->close_after !== null && Carbon::make($this->published_at)?->add($this->close_after)->isPast();
    }

    public function disable(string $reason): void
    {
        $this->update([
            'approved' => false,
            'in_review' => false,
            'visible_to_public' => false,
            'published_at' => null,
            'admin_notes' => $reason,
        ]);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function userIsWithinTargetGroup(User $user): bool
    {
        if (! $this->target_group) {
            return true;
        }

        return TargetGroupService::userIsWithinTargetGroup($this->target_group, $user);
    }
}
