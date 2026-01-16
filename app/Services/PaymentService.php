<?php

namespace App\Services;

use App\Mail\AccountStatusChanged;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $baseUrl;
    protected $secretKey;
    protected $publicKey;

    public function __construct()
    {
        $this->baseUrl = config('services.flutterwave.url', 'https://api.flutterwave.com/v3');
        $this->secretKey = config('services.flutterwave.secret_key');
        $this->publicKey = config('services.flutterwave.public_key');
    }

    /**
     * Initialize payment
     */
    public function initializePayment(array $data)
    {
        $user = User::findOrFail($data['user_id']);
        $program = Program::findOrFail($data['program_id']);

        // Calculate amounts
        $amount = $data['payment_plan'] === 'one-time' 
            ? $program->discounted_price 
            : $program->installment_amount;

        $discountAmount = $data['payment_plan'] === 'one-time'
            ? $program->price - $program->discounted_price
            : 0;

        // Generate unique reference
        $reference = 'GLU-' . time() . '-' . $user->id;

        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'enrollment_id' => $data['enrollment_id'] ?? null,
            'reference' => $reference,
            'amount' => $program->price,
            'discount_amount' => $discountAmount,
            'final_amount' => $amount,
            'payment_plan' => $data['payment_plan'],
            'installment_number' => $data['installment_number'] ?? null,
            'remaining_balance' => $data['payment_plan'] === 'installment' 
                ? $program->installment_amount 
                : 0,
            'installment_status' => $data['payment_plan'] === 'installment' ? 'partial' : null,
            'status' => 'pending',
            'metadata' => [
                'cohort_id' => $data['cohort_id'] ?? null,
            ],
        ]);

        // Prepare Flutterwave payload
        $payload = [
            'tx_ref' => $reference,
            'amount' => $amount,
            'currency' => 'NGN',
            'redirect_url' => route('payment.callback'),
            'payment_options' => 'card,banktransfer,ussd',
            'customer' => [
                'email' => $user->email,
                'phonenumber' => $user->phone,
                'name' => $user->name,
            ],
            'customizations' => [
                'title' => 'G-Luper Learning',
                'description' => 'Payment for ' . $program->name,
                'logo' => asset('images/logo.png'),
            ],
            'meta' => [
                'user_id' => $user->id,
                'program_id' => $program->id,
                'payment_id' => $payment->id,
                'payment_plan' => $data['payment_plan'],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payments', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success') {
                    return [
                        'success' => true,
                        'payment_link' => $data['data']['link'],
                        'payment_id' => $payment->id,
                    ];
                }
            }

            Log::error('Flutterwave initialization failed', [
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to initialize payment. Please try again.',
            ];

        } catch (\Exception $e) {
            Log::error('Payment initialization error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred. Please try again.',
            ];
        }
    }

    /**
     * Verify payment
     */
    public function verifyPayment($transactionId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
            ])->get($this->baseUrl . '/transactions/' . $transactionId . '/verify');

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'success' && $data['data']['status'] === 'successful') {
                    return [
                        'success' => true,
                        'data' => $data['data'],
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Payment verification failed.',
            ];

        } catch (\Exception $e) {
            Log::error('Payment verification error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred during verification.',
            ];
        }
    }

    /**
     * Handle successful payment
     */
    public function handleSuccessfulPayment($transactionData)
    {
        $reference = $transactionData['tx_ref'];
        $payment = Payment::where('reference', $reference)->first();

        if (!$payment) {
            Log::error('Payment not found for reference: ' . $reference);
            return false;
        }

        // Update payment
        $payment->update([
            'status' => 'successful',
            'payment_method' => $transactionData['payment_type'] ?? 'card',
            'flutterwave_response' => json_encode($transactionData),
            'paid_at' => now(),
        ]);

        // Create or update enrollment
        $enrollment = $this->createOrUpdateEnrollment($payment);

        // Send payment success email
        // Mail::to($payment->user->email)->send(new PaymentSuccess($payment));

        return $enrollment;
    }

    /**
     * Create or update enrollment
     */
    protected function createOrUpdateEnrollment(Payment $payment)
    {
        $cohortId = $payment->metadata['cohort_id'] ?? null;

        if ($payment->enrollment_id) {
            $enrollment = Enrollment::find($payment->enrollment_id);
            $enrollment->update(['status' => 'active']);
        } else {
            $enrollment = Enrollment::create([
                'user_id' => $payment->user_id,
                'program_id' => $payment->program_id,
                'cohort_id' => $cohortId,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);

            $payment->update(['enrollment_id' => $enrollment->id]);

            // Increment cohort enrollment count
            if ($cohortId) {
                $enrollment->cohort->incrementEnrollment();
            }
        }

        return $enrollment;
    }
}