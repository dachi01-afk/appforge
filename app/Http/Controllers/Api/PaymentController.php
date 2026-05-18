<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaymentRequest;
use App\Http\Resources\Api\PaymentResource;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Submit payment proof for a specific pending payment.
     */
    public function submitProof(PaymentRequest $request, Payment $payment): PaymentResource
    {
        // Upload payment proof
        if ($request->hasFile('payment_proof')) {
            // Delete old file if exists
            if ($payment->payment_proof) {
                Storage::disk('public')->delete($payment->payment_proof);
            }

            $path = $request->file('payment_proof')->store('payments', 'public');
            $payment->payment_proof = $path;
        }

        $payment->payment_method = $request->input('payment_method');
        $payment->status = 'pending'; // Stays or resets to pending for admin verification
        $payment->save();

        // Send confirmation notification to the client (Fase 6)
        app(NotificationService::class)->send(
            $payment->order->user,
            'Payment Proof Submitted',
            "Your payment proof for invoice '{$payment->invoice_number}' has been submitted and is awaiting verification."
        );

        return new PaymentResource($payment);
    }

    /**
     * Get details of a specific payment invoice.
     */
    public function show(Payment $payment): PaymentResource
    {
        // Ensure client is authorized to see their own payments
        if ($payment->order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return new PaymentResource($payment);
    }
}
