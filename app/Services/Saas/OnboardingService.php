<?php

namespace App\Services\Saas;

use App\Enums\OnboardingStep;
use App\Models\Tenant;

class OnboardingService
{
    public function currentStep(Tenant $tenant): OnboardingStep
    {
        if ($tenant->onboarding_completed) {
            return OnboardingStep::Complete;
        }

        return $tenant->onboarding_step ?? OnboardingStep::Plan;
    }

    public function advanceTo(Tenant $tenant, OnboardingStep $step): void
    {
        $tenant->update(['onboarding_step' => $step]);
    }

    public function complete(Tenant $tenant): void
    {
        $tenant->update([
            'onboarding_completed' => true,
            'onboarding_step' => OnboardingStep::Complete,
            'onboarding_completed_at' => now(),
        ]);
    }

    public function canView(Tenant $tenant, OnboardingStep $requestedStep): bool
    {
        $currentStep = $this->currentStep($tenant);

        if ($currentStep === OnboardingStep::Complete) {
            return false;
        }

        return $requestedStep->number() <= $currentStep->number();
    }
}
