<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'program', 'enrollment.cohort']);

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by payment plan
        if ($request->payment_plan) {
            $query->where('payment_plan', $request->payment_plan);
        }

        // Filter by program
        if ($request->program_id) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('transaction_id', 'like', '%' . $request->search . '%')
                  ->orWhere('reference', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $payments = $query->latest()->paginate(20);
        $programs = Program::all();

        // Statistics
        $stats = [
            'total_revenue' => Payment::successful()->sum('final_amount'),
            'pending_amount' => Payment::pending()->sum('final_amount'),
            'total_payments' => Payment::successful()->count(),
            'failed_payments' => Payment::where('status', 'failed')->count(),
            'installment_pending' => Payment::where('installment_status', 'partial')->sum('remaining_balance'),
        ];

        // Monthly revenue (last 6 months)
        $monthlyRevenue = Payment::successful()
            ->where('paid_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(paid_at, "%Y-%m") as month'),
                DB::raw('SUM(final_amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.payments.index', compact('payments', 'programs', 'stats', 'monthlyRevenue'));
    }

    public function show($id)
    {
        $payment = Payment::with(['user', 'program', 'enrollment.cohort'])->findOrFail($id);
        
        // Get related payments (other installments)
        $relatedPayments = [];
        if ($payment->enrollment_id) {
            $relatedPayments = Payment::where('enrollment_id', $payment->enrollment_id)
                ->where('id', '!=', $payment->id)
                ->get();
        }

        return view('admin.payments.show', compact('payment', 'relatedPayments'));
    }

    public function export(Request $request)
    {
        $query = Payment::with(['user', 'program', 'enrollment.cohort']);

        // Apply same filters as index
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->payment_plan) {
            $query->where('payment_plan', $request->payment_plan);
        }
        if ($request->program_id) {
            $query->where('program_id', $request->program_id);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->latest()->get();

        $filename = 'payments_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Transaction ID',
                'Reference',
                'Student Name',
                'Student Email',
                'Program',
                'Cohort',
                'Amount',
                'Discount',
                'Final Amount',
                'Payment Plan',
                'Installment',
                'Status',
                'Payment Method',
                'Date',
                'Paid At'
            ]);

            // Data
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->transaction_id,
                    $payment->reference,
                    $payment->user->name,
                    $payment->user->email,
                    $payment->program->name,
                    $payment->enrollment?->cohort?->name ?? 'N/A',
                    number_format($payment->amount, 2),
                    number_format($payment->discount_amount, 2),
                    number_format($payment->final_amount, 2),
                    ucfirst($payment->payment_plan),
                    $payment->installment_number ?? 'N/A',
                    ucfirst($payment->status),
                    $payment->payment_method ?? 'N/A',
                    $payment->created_at->format('Y-m-d H:i'),
                    $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}