<?php

namespace App\Http\Controllers;

use App\Models\Cohort;
use App\Models\Program;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function initiatePayment(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'cohort_id' => 'required|exists:cohorts,id',
            'payment_plan' => 'required|in:one-time,installment',
        ]);

        try {
            $result = $this->paymentService->initializePayment([
                'user_id' => auth()->id(),
                'program_id' => $request->program_id,
                'cohort_id' => $request->cohort_id,
                'payment_plan' => $request->payment_plan,
                'installment_number' => $request->payment_plan === 'installment' ? 1 : null,
            ]);

            if ($result['success']) {
                return redirect($result['payment_link']);
            }

            return back()->with([
                'message' => $result['message'],
                'alert-type' => 'error'
            ]);

        } catch (\Exception $e) {
            return back()->with([
                'message' => 'Payment initiation failed. Please try again.',
                'alert-type' => 'error'
            ]);
        }
    }

    public function callback(Request $request)
    {
        $status = $request->status;
        $transactionId = $request->transaction_id;
        $txRef = $request->tx_ref;

        if ($status === 'successful') {
            $verification = $this->paymentService->verifyPayment($transactionId);

            if ($verification['success']) {
                $enrollment = $this->paymentService->handleSuccessfulPayment($verification['data']);

                return redirect()->route('learner.dashboard')->with([
                    'message' => 'Payment successful! You have been enrolled.',
                    'alert-type' => 'success'
                ]);
            }
        }

        return redirect()->route('learner.dashboard')->with([
            'message' => 'Payment was not successful. Please try again.',
            'alert-type' => 'error'
        ]);
    }

    public function payInstallment(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
        ]);

        $enrollment = auth()->user()->enrollments()->findOrFail($request->enrollment_id);

        if ($enrollment->isFullyPaid()) {
            return back()->with([
                'message' => 'This enrollment is already fully paid.',
                'alert-type' => 'info'
            ]);
        }

        try {
            $result = $this->paymentService->initializePayment([
                'user_id' => auth()->id(),
                'program_id' => $enrollment->program_id,
                'cohort_id' => $enrollment->cohort_id,
                'enrollment_id' => $enrollment->id,
                'payment_plan' => 'installment',
                'installment_number' => 2,
            ]);

            if ($result['success']) {
                return redirect($result['payment_link']);
            }

            return back()->with([
                'message' => $result['message'],
                'alert-type' => 'error'
            ]);

        } catch (\Exception $e) {
            return back()->with([
                'message' => 'Payment initiation failed. Please try again.',
                'alert-type' => 'error'
            ]);
        }
    }
}