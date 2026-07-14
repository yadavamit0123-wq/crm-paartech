<?php

namespace App\Http\Controllers;

use App\Models\PaymentMilestone;
use Illuminate\Http\Request;

class PublicPaymentController extends Controller
{
    public function show(PaymentMilestone $milestone)
    {
        $milestone->load(['plan.customer', 'plan.tenant']);

        if (! in_array($milestone->status, ['approved', 'link_sent', 'paid'])) {
            abort(404, 'Payment link not available.');
        }

        return view('payments.public', compact('milestone'));
    }
}
