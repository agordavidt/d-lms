<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    @page {
        margin: 0;
        size: A4 landscape;
    }

    body {
        width: 297mm;
        height: 210mm;
        font-family: 'DejaVu Sans', 'Arial', sans-serif;
        background: #ffffff;
        overflow: hidden;
    }

    .certificate {
        width: 297mm;
        height: 210mm;
        position: relative;
        background: #ffffff;
    }

    /* ── Outer border frame ── */
    .border-outer {
        position: absolute;
        inset: 8mm;
        border: 3px solid #1e3a5f;
    }

    .border-inner {
        position: absolute;
        inset: 11mm;
        border: 1px solid #c9a84c;
    }

    /* ── Corner ornaments ── */
    .corner {
        position: absolute;
        width: 18mm;
        height: 18mm;
    }
    .corner-tl { top: 6mm;  left: 6mm;  border-top: 4px solid #c9a84c; border-left: 4px solid #c9a84c; }
    .corner-tr { top: 6mm;  right: 6mm; border-top: 4px solid #c9a84c; border-right: 4px solid #c9a84c; }
    .corner-bl { bottom: 6mm; left: 6mm;  border-bottom: 4px solid #c9a84c; border-left: 4px solid #c9a84c; }
    .corner-br { bottom: 6mm; right: 6mm; border-bottom: 4px solid #c9a84c; border-right: 4px solid #c9a84c; }

    /* ── Top accent bar ── */
    .top-bar {
        position: absolute;
        top: 14mm;
        left: 14mm;
        right: 14mm;
        height: 12mm;
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a9e 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .top-bar-text {
        color: #c9a84c;
        font-size: 9pt;
        font-weight: bold;
        letter-spacing: 4px;
        text-transform: uppercase;
    }

    /* ── Body content ── */
    .body {
        position: absolute;
        top: 30mm;
        left: 16mm;
        right: 16mm;
        bottom: 22mm;
        text-align: center;
    }

    .issuer {
        font-size: 10pt;
        color: #64748b;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 3mm;
    }

    .cert-title {
        font-size: 30pt;
        color: #1e3a5f;
        font-weight: bold;
        letter-spacing: 1px;
        margin-bottom: 2mm;
        line-height: 1.1;
    }

    .cert-subtitle {
        font-size: 10pt;
        color: #94a3b8;
        letter-spacing: 3px;
        text-transform: uppercase;
        margin-bottom: 6mm;
    }

    .presented-to {
        font-size: 9pt;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 2mm;
    }

    .learner-name {
        font-size: 26pt;
        color: #c9a84c;
        font-weight: bold;
        margin-bottom: 5mm;
        line-height: 1.1;
    }

    .completion-text {
        font-size: 10pt;
        color: #475569;
        line-height: 1.7;
        margin-bottom: 3mm;
    }

    .program-name {
        font-size: 15pt;
        color: #1e3a5f;
        font-weight: bold;
        margin-bottom: 6mm;
        line-height: 1.3;
    }

    .score-line {
        font-size: 9pt;
        color: #64748b;
        margin-bottom: 7mm;
    }

    .score-line strong {
        color: #1e3a5f;
    }

    /* ── Divider ── */
    .divider {
        width: 60mm;
        height: 1px;
        background: linear-gradient(90deg, transparent, #c9a84c, transparent);
        margin: 0 auto 7mm;
    }

    /* ── Signatures row ── */
    .signatures {
        display: table;
        width: 100%;
        margin-top: 4mm;
    }

    .sig-cell {
        display: table-cell;
        width: 33.33%;
        text-align: center;
        vertical-align: bottom;
        padding: 0 5mm;
    }

    .sig-line {
        border-top: 1px solid #94a3b8;
        padding-top: 2mm;
    }

    .sig-name {
        font-size: 8pt;
        color: #1e3a5f;
        font-weight: bold;
    }

    .sig-role {
        font-size: 7pt;
        color: #94a3b8;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    /* ── Bottom strip ── */
    .bottom-strip {
        position: absolute;
        bottom: 14mm;
        left: 14mm;
        right: 14mm;
        height: 6mm;
        background: linear-gradient(135deg, #1e3a5f, #2d5a9e);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 4mm;
    }

    .bottom-strip-text {
        font-size: 6pt;
        color: #c9a84c;
        letter-spacing: 1px;
    }

    /* ── Watermark seal ── */
    .seal {
        position: absolute;
        right: 28mm;
        top: 50%;
        margin-top: -18mm;
        width: 36mm;
        height: 36mm;
        border-radius: 50%;
        border: 3px solid #1e3a5f;
        background: radial-gradient(circle, #f0f4ff 0%, #e8efff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.12;
    }

    .seal-inner {
        width: 28mm;
        height: 28mm;
        border-radius: 50%;
        border: 2px solid #1e3a5f;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .seal-text {
        font-size: 6pt;
        color: #1e3a5f;
        font-weight: bold;
        letter-spacing: 1px;
        line-height: 1.4;
        text-transform: uppercase;
    }
</style>
</head>
<body>
<div class="certificate">

    <!-- Border frames -->
    <div class="border-outer"></div>
    <div class="border-inner"></div>

    <!-- Corner ornaments -->
    <div class="corner corner-tl"></div>
    <div class="corner corner-tr"></div>
    <div class="corner corner-bl"></div>
    <div class="corner corner-br"></div>

    <!-- Top bar -->
    <div class="top-bar">
        <span class="top-bar-text">G-Luper Learning Platform</span>
    </div>

    <!-- Watermark seal -->
    <div class="seal">
        <div class="seal-inner">
            <div class="seal-text">G-LUPER<br>OFFICIAL<br>SEAL</div>
        </div>
    </div>

    <!-- Main content -->
    <div class="body">
        <div class="issuer">This is to certify that</div>
        <div class="cert-title">Certificate</div>
        <div class="cert-subtitle">of Completion</div>

        <div class="presented-to">is proudly awarded to</div>
        <div class="learner-name">{{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}</div>

        <div class="completion-text">
            for successfully completing all course requirements of
        </div>
        <div class="program-name">{{ $enrollment->program->name }}</div>

        <div class="divider"></div>

        <div class="score-line">
            Final Examination Score: <strong>{{ number_format($enrollment->final_exam_score, 0) }}%</strong>
            &nbsp;&nbsp;·&nbsp;&nbsp;
            Completed: <strong>{{ $enrollment->completed_at?->format('F j, Y') ?? $enrollment->graduation_approved_at?->format('F j, Y') }}</strong>
            &nbsp;&nbsp;·&nbsp;&nbsp;
            Issued: <strong>{{ $enrollment->certificate_issued_at?->format('F j, Y') }}</strong>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="sig-cell">
                <div class="sig-line">
                    <div class="sig-name">{{ $enrollment->program->mentor?->first_name }} {{ $enrollment->program->mentor?->last_name }}</div>
                    <div class="sig-role">Program Mentor</div>
                </div>
            </div>
            <div class="sig-cell">
                <div style="margin-bottom:2mm;">
                    <span style="font-size:7pt;color:#94a3b8;letter-spacing:1px;text-transform:uppercase;">Certificate No.</span><br>
                    <span style="font-size:8pt;color:#1e3a5f;font-weight:bold;font-family:monospace;">{{ $enrollment->certificate_key }}</span>
                </div>
                <div class="sig-line">
                    <div class="sig-name">G-Luper Platform</div>
                    <div class="sig-role">Issuing Authority</div>
                </div>
            </div>
            <div class="sig-cell">
                <div class="sig-line">
                    <div class="sig-name">{{ $enrollment->approvedBy?->first_name }} {{ $enrollment->approvedBy?->last_name }}</div>
                    <div class="sig-role">Administrator</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom strip -->
    <div class="bottom-strip">
        <span class="bottom-strip-text">G-LUPER · CERTIFICATE OF COMPLETION</span>
        <span class="bottom-strip-text">
            Verify at: {{ config('app.url') }}/certificate/verify/{{ $enrollment->certificate_key }}
        </span>
        <span class="bottom-strip-text">{{ $enrollment->certificate_key }}</span>
    </div>

</div>
</body>
</html>