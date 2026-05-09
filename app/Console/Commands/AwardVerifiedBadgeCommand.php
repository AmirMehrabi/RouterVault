<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('award:verified {phone}')]
#[Description('Award the verified badge to a user by phone number')]
class AwardVerifiedBadgeCommand extends Command
{
    public function __construct(protected BadgeService $badgeService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $phone = (string) $this->argument('phone');
        $normalizedPhone = $this->badgeService->normalizePhone($phone);

        $user = User::query()
            ->whereNotNull('phone')
            ->get()
            ->first(function (User $candidate) use ($normalizedPhone): bool {
                return $this->badgeService->normalizePhone((string) $candidate->phone) === $normalizedPhone;
            });

        if (! $user) {
            $this->error('User not found for the provided phone number.');

            return self::FAILURE;
        }

        $award = $this->badgeService->awardBadge($user, 'verified', [
            'source' => 'cli',
            'phone' => $phone,
        ]);

        if (! $award) {
            $this->error('Unable to award the verified badge to this user.');

            return self::FAILURE;
        }

        if ($user->hasBadge('verified')) {
            $this->info("Verified badge awarded to {$user->name}.");
        }

        return self::SUCCESS;
    }
}
