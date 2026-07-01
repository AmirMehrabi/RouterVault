<?php

namespace App\Services\Saas;

use App\Models\Payment;

class DummyPaymentService
{
    public function charge(Payment $payment): string
    {
        return 'DUMMY-'.strtoupper(uniqid()).'-'.$payment->id;
    }
}
