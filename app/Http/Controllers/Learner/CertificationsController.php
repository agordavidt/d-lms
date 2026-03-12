<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;

class CertificationsController extends Controller
{
    /**
     * Learner certifications — graduated + certificate approved enrollments only.
     */
    public function index()
    {
        try {
            $user = auth()->user();

            $certifications = Enrollment::where('user_id', $user->id)
                ->where('graduation_status', 'graduated')
                ->whereNotNull('certificate_key')
                ->with(['program', 'cohort'])
                ->latest('updated_at')
                ->get();

            return view('learner.certifications', compact('certifications'));

        } catch (\Exception $e) {
            return back()->with([
                'message'    => 'Unable to load certifications.',
                'alert-type' => 'error',
            ]);
        }
    }
}