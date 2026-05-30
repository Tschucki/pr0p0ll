<?php

declare(strict_types=1);

use App\Jobs\ResolvePr0grammPostItemId;
use App\Models\Abstracts\Poll;
use App\Services\Pr0grammBotService;
use App\Services\ResultPostPublisher;
use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;

beforeEach(function () {
    Bus::fake();
});

it('resolves the newest upload item id and hands it to the publisher', function () {
    $bot = $this->mock(Pr0grammBotService::class, function (MockInterface $mock) {
        $mock->shouldReceive('ensureLoggedIn')->once();
        $mock->shouldReceive('findRecentUploadItemId')->once()->with(1700)->andReturn(4242);
    });
    $publisher = $this->mock(ResultPostPublisher::class, function (MockInterface $mock) {
        $mock->shouldReceive('publish')->once()->with(Mockery::type(Poll::class), 4242);
    });

    $poll = makeClosedPoll();

    (new ResolvePr0grammPostItemId($poll, 1700))->handle($bot, $publisher);
});

it('re-dispatches itself with the next attempt and the same upload timestamp when not yet listed', function () {
    $bot = $this->mock(Pr0grammBotService::class, function (MockInterface $mock) {
        $mock->shouldReceive('ensureLoggedIn')->once();
        $mock->shouldReceive('findRecentUploadItemId')->once()->andReturnNull();
    });
    $publisher = $this->mock(ResultPostPublisher::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('publish');
    });

    $poll = makeClosedPoll();

    (new ResolvePr0grammPostItemId($poll, 1700, attempt: 1))->handle($bot, $publisher);

    Bus::assertDispatched(ResolvePr0grammPostItemId::class, function (ResolvePr0grammPostItemId $job) {
        return (new ReflectionProperty($job, 'attempt'))->getValue($job) === 2
            && (new ReflectionProperty($job, 'uploadedAfter'))->getValue($job) === 1700;
    });
});

it('fails after the maximum number of attempts without re-dispatching', function () {
    $bot = $this->mock(Pr0grammBotService::class, function (MockInterface $mock) {
        $mock->shouldReceive('ensureLoggedIn')->once();
        $mock->shouldReceive('findRecentUploadItemId')->once()->andReturnNull();
    });
    $publisher = $this->mock(ResultPostPublisher::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('publish');
    });

    $poll = makeClosedPoll();

    (new ResolvePr0grammPostItemId($poll, 1700, attempt: ResolvePr0grammPostItemId::MAX_ATTEMPTS))->handle($bot, $publisher);

    Bus::assertNotDispatched(ResolvePr0grammPostItemId::class);
});

it('skips work when the post url has already been resolved', function () {
    $bot = $this->mock(Pr0grammBotService::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('ensureLoggedIn');
        $mock->shouldNotReceive('findRecentUploadItemId');
    });
    $publisher = $this->mock(ResultPostPublisher::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('publish');
    });

    $poll = makeClosedPoll();
    $poll->update(['original_content_link' => 'https://pr0gramm.com/new/1']);

    (new ResolvePr0grammPostItemId($poll, 1700))->handle($bot, $publisher);

    expect($poll->fresh()->original_content_link)->toBe('https://pr0gramm.com/new/1');
});
