<?php

namespace App\Abstracts;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\User;
use App\Services\TargetGroupService;
use Illuminate\Database\Eloquent\Model;
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

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Question::class, 'poll_id');
    }

    public function answers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Answer::class, 'poll_id');
    }

    public function userParticipated(User $user): bool
    {
        return $this->participants()->where('participant_id', $user->getKey())->exists();
    }

    public function participants(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
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

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
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
