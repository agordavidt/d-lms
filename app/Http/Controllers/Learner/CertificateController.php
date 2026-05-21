<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Requires: composer require barryvdh/laravel-dompdf
 */
class CertificateController extends Controller
{
    public function index()
    {
        $certifications = Enrollment::where('user_id', auth()->id())
            ->where('graduation_status', 'graduated')
            ->whereNotNull('certificate_key')
            ->with(['program', 'cohort'])
            ->latest('graduation_approved_at')
            ->get();

        return view('learner.certifications', compact('certifications'));
    }

    public function download(string $key)
    {
        $enrollment = Enrollment::where('certificate_key', $key)
            ->where('graduation_status', 'graduated')
            ->with(['user', 'program', 'approvedBy'])
            ->firstOrFail();

        $user = auth()->user();
        if ($enrollment->user_id !== $user->id && !in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }

        $pdf = Pdf::loadView('learner.certificate', compact('enrollment'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'dpi'                  => 150,
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
            ]);

        $filename = 'certificate-' . strtolower(str_replace(' ', '-', $enrollment->program->name)) . '.pdf';

        return $pdf->download($filename);
    }
}