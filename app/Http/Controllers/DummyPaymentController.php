<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Saas\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DummyPaymentController extends Controller
{
    public function show(Payment $payment): View
    {
        $this->authorizeTenant($payment->tenant_id);

        return view('billing.payment', [
            'payment' => $payment->load('subscription.plan'),
        ]);
    }

    public function process(Request $request, Payment $payment, SubscriptionService $subscriptions): RedirectResponse
    {
        $this->authorizeTenant($payment->tenant_id);

        $payment = $subscriptions->completePayment($payment);

        return redirect()->route('billing.payment.confirmation', $payment)
            ->with('success', 'Payment processed successfully!');
    }

    public function confirmation(Payment $payment): View
    {
        $this->authorizeTenant($payment->tenant_id);

        return view('billing.payment-confirmation', [
            'payment' => $payment->load('subscription.plan'),
        ]);
    }

    protected function authorizeTenant(string $tenantId): void
    {
        if (auth()->user()?->tenant_id !== $tenantId) {
            abort(403);
        }
    }
}
