<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    // Set to true when ready to use real Flutterwave
    private $useRealPayment = false;
    
    /**
     * Initiate payment for a program enrollment
     */
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'cohort_id' => 'required|exists:cohorts,id',
            'payment_plan' => 'required|in:one-time,installment',
        ]);

        try {
            $user = auth()->user();
            $program = Program::findOrFail($request->program_id);

            // Check if already enrolled
            $existingEnrollment = Enrollment::where('user_id', $user->id)
                ->where('program_id', $program->id)
                ->where('cohort_id', $request->cohort_id)
                ->first();

            if ($existingEnrollment) {
                return back()->with([
                    'message' => 'You are already enrolled in this program!',
                    'alert-type' => 'warning'
                ]);
            }

            // Calculate payment amount
            $amount = $program->price;
            $discount = 0;
            
            if ($request->payment_plan === 'one-time') {
                // 10% discount for one-time payment
                $discount = ($amount * $program->discount_percentage) / 100;
            } else {
                // First installment (50%)
                $amount = $amount / 2;
            }

            $finalAmount = $amount - $discount;

            // Create enrollment (pending until payment)
            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'program_id' => $program->id,
                'cohort_id' => $request->cohort_id,
                'status' => 'pending',
                'enrolled_at' => now(),
            ]);

            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'enrollment_id' => $enrollment->id,
                'program_id' => $program->id,
                'reference' => 'REF-' . strtoupper(Str::random(10)),
                'amount' => $amount,
                'discount_amount' => $discount,
                'final_amount' => $finalAmount,
                'payment_plan' => $request->payment_plan,
                'installment_number' => $request->payment_plan === 'installment' ? 1 : null,
                'remaining_balance' => $request->payment_plan === 'installment' ? ($program->price / 2) : 0,
                'installment_status' => $request->payment_plan === 'installment' ? 'partial' : null,
                'status' => 'pending',
                'metadata' => [
                    'program_name' => $program->name,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                ]
            ]);

            if ($this->useRealPayment) {
                return $this->initiateFlutterwavePayment($payment, $user);
            } else {
                return $this->initiateSimulatedPayment($payment);
            }

        } catch (\Exception $e) {
            return back()->with([
                'message' => 'Payment initiation failed: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * Simulated payment (for testing)
     */
    private function initiateSimulatedPayment($payment)
    {
        return view('payments.simulate', compact('payment'));
    }

    /**
     * Real Flutterwave payment integration
     */
    private function initiateFlutterwavePayment($payment, $user)
    {
        $curl = curl_init();

        $data = [
            "tx_ref" => $payment->reference,
            "amount" => $payment->final_amount,
            "currency" => "NGN",
            "redirect_url" => route('payment.callback'),
            "payment_options" => "card,banktransfer,ussd",
            "customer" => [
                "email" => $user->email,
                "phonenumber" => $user->phone ?? '',
                "name" => $user->name
            ],
            "customizations" => [
                "title" => "G-Luper Program Enrollment",
                "description" => "Payment for " . $payment->metadata['program_name'],
                "logo" => asset('images/logo.png')
            ]
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.flutterwave.com/v3/payments",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . config('services.flutterwave.secret_key'),
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new \Exception("Payment gateway error: " . $err);
        }

        $result = json_decode($response);

        if ($result->status === 'success') {
            return redirect($result->data->link);
        } else {
            throw new \Exception("Payment initialization failed");
        }
    }

    /**
     * Handle payment callback
     */
    public function callback(Request $request)
    {
        if ($this->useRealPayment) {
            return $this->handleFlutterwaveCallback($request);
        } else {
            return $this->handleSimulatedCallback($request);
        }
    }

    /**
     * Handle simulated payment callback
     */
    private function handleSimulatedCallback(Request $request)
    {
        try {
            $payment = Payment::where('reference', $request->reference)->firstOrFail();

            DB::beginTransaction();

            // Update payment status
            $payment->update([
                'status' => 'successful',
                'paid_at' => now(),
                'payment_method' => 'simulation',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'simulated' => true,
                    'completed_at' => now()->toDateTimeString()
                ])
            ]);

            // Update enrollment
            $enrollment = $payment->enrollment;
            $enrollment->update(['status' => 'active']);

            // Increment cohort enrollment count
            $enrollment->cohort->incrementEnrollment();

            // Update installment status
            if ($payment->payment_plan === 'installment') {
                $payment->update([
                    'installment_status' => 'partial'
                ]);
            } else {
                $payment->update([
                    'remaining_balance' => 0
                ]);
            }

            // Log activity
            AuditLog::log('payment_completed', auth()->user(), [
                'description' => 'Payment completed for ' . $payment->program->name,
                'model_type' => Payment::class,
                'model_id' => $payment->id,
                'amount' => $payment->final_amount
            ]);

            DB::commit();

            return redirect()->route('learner.dashboard')->with([
                'message' => 'Payment successful! You are now enrolled in ' . $payment->program->name,
                'alert-type' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('learner.dashboard')->with([
                'message' => 'Payment verification failed: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * Handle real Flutterwave callback
     */
    private function handleFlutterwaveCallback(Request $request)
    {
        if ($request->status === 'successful') {
            $transactionId = $request->transaction_id;
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.flutterwave.com/v3/transactions/{$transactionId}/verify",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer " . config('services.flutterwave.secret_key'),
                    "Content-Type: application/json"
                ],
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($response);

            if ($result->status === 'success' && $result->data->status === 'successful') {
                $payment = Payment::where('reference', $result->data->tx_ref)->firstOrFail();

                DB::beginTransaction();

                try {
                    $payment->update([
                        'status' => 'successful',
                        'paid_at' => now(),
                        'payment_method' => $result->data->payment_type,
                        'flutterwave_response' => json_encode($result->data),
                        'metadata' => array_merge($payment->metadata ?? [], [
                            'transaction_id' => $transactionId,
                            'flutterwave_id' => $result->data->id
                        ])
                    ]);

                    $enrollment = $payment->enrollment;
                    $enrollment->update(['status' => 'active']);
                    $enrollment->cohort->incrementEnrollment();

                    if ($payment->payment_plan === 'installment') {
                        $payment->update(['installment_status' => 'partial']);
                    } else {
                        $payment->update(['remaining_balance' => 0]);
                    }

                    AuditLog::log('payment_completed', auth()->user(), [
                        'description' => 'Payment completed via Flutterwave',
                        'model_type' => Payment::class,
                        'model_id' => $payment->id,
                        'amount' => $payment->final_amount
                    ]);

                    DB::commit();

                    return redirect()->route('learner.dashboard')->with([
                        'message' => 'Payment successful! You are now enrolled.',
                        'alert-type' => 'success'
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }
        }

        return redirect()->route('learner.dashboard')->with([
            'message' => 'Payment verification failed',
            'alert-type' => 'error'
        ]);
    }

    /**
     * Pay second installment
     */
    public function payInstallment(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
        ]);

        try {
            $enrollment = Enrollment::findOrFail($request->enrollment_id);
            
            if ($enrollment->user_id !== auth()->id()) {
                abort(403);
            }

            $firstPayment = $enrollment->payments()
                ->where('installment_number', 1)
                ->where('status', 'successful')
                ->firstOrFail();

            // Check if second installment already paid
            $secondPayment = $enrollment->payments()
                ->where('installment_number', 2)
                ->first();

            if ($secondPayment) {
                return back()->with([
                    'message' => 'Second installment already paid!',
                    'alert-type' => 'info'
                ]);
            }

            $remainingAmount = $firstPayment->remaining_balance;

            // Create second installment payment
            $payment = Payment::create([
                'user_id' => auth()->id(),
                'enrollment_id' => $enrollment->id,
                'program_id' => $enrollment->program_id,
                'reference' => 'REF-' . strtoupper(Str::random(10)),
                'amount' => $remainingAmount,
                'discount_amount' => 0,
                'final_amount' => $remainingAmount,
                'payment_plan' => 'installment',
                'installment_number' => 2,
                'remaining_balance' => 0,
                'status' => 'pending',
                'metadata' => [
                    'program_name' => $enrollment->program->name,
                    'user_name' => auth()->user()->name,
                    'user_email' => auth()->user()->email,
                ]
            ]);

            if ($this->useRealPayment) {
                return $this->initiateFlutterwavePayment($payment, auth()->user());
            } else {
                return $this->initiateSimulatedPayment($payment);
            }

        } catch (\Exception $e) {
            return back()->with([
                'message' => 'Failed to initiate installment payment: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }
}