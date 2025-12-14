<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Indigency Certificate</title>
    <style>
        {!! file_get_contents(public_path('css/certificate.css')) !!}
    </style>
</head>

<body>
    <div class="document-wrapper">
        <!-- Header -->
        <div class="header">
            <h1>Republic of the Philippines</h1>
            <div class="barangay-name">BARANGAY [YOUR BARANGAY NAME]</div>
            <div class="subtitle">[City/Municipality], [Province]</div>
        </div>

        <!-- Certificate Title -->
        <div class="certificate-title">
            Certificate of Indigency
        </div>

        <!-- Main Content -->
        <div class="content">
            <p>
                This is to certify that <span class="highlight">{{ $documentRequest->user->name }}</span>,
                a resident of this Barangay, is a person of good moral character and has no pending
                civil or criminal case filed against him/her in this Barangay as of this date.
            </p>

            <p>
                This certification is being issued upon the request of the above-named person for
                <strong>{{ $documentRequest->document_type }}</strong> purposes.
            </p>
        </div>

        <!-- Details Section -->
        <div class="details-section">
            <h3>Personal Information</h3>

            <div class="detail-row">
                <div class="detail-label">Full Name:</div>
                <div class="detail-value">{{ $documentRequest->user->name }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Email Address:</div>
                <div class="detail-value">{{ $documentRequest->user->email }}</div>
            </div>

            @if ($documentRequest->user->phone)
                <div class="detail-row">
                    <div class="detail-label">Phone Number:</div>
                    <div class="detail-value">{{ $documentRequest->user->phone }}</div>
                </div>
            @endif

            @if ($documentRequest->user->birthday)
                <div class="detail-row">
                    <div class="detail-label">Date of Birth:</div>
                    <div class="detail-value">
                        {{ \Carbon\Carbon::parse($documentRequest->user->birthday)->format('F d, Y') }}
                    </div>
                </div>
            @endif

            <div class="detail-row">
                <div class="detail-label">Document Type:</div>
                <div class="detail-value">{{ $documentRequest->document_type }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Request Date:</div>
                <div class="detail-value">
                    {{ $documentRequest->created_at->format('F d, Y') }}
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Approval Date:</div>
                <div class="detail-value">
                    {{ $documentRequest->updated_at->format('F d, Y') }}
                </div>
            </div>
        </div>

        <!-- Additional Notes -->
        @if ($documentRequest->staff_message)
            <div class="content" style="margin-top: 20px;">
                <p><strong>Remarks:</strong> {{ $documentRequest->staff_message }}</p>
            </div>
        @endif

        <!-- Date Section -->
        <div class="date-section">
            <p>Issued this <strong>{{ $documentRequest->updated_at->format('jS') }}</strong> day of
                <strong>{{ $documentRequest->updated_at->format('F Y') }}</strong>.
            </p>
        </div>

        <!-- Footer with Signatures -->
        <div class="footer">
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-name">[Staff Name]</div>
                    <div class="signature-title">Barangay Staff</div>
                </div>

                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-name">[Barangay Captain Name]</div>
                    <div class="signature-title">Barangay Captain</div>
                </div>
            </div>

            <div class="reference-number">
                <p>Reference No:
                    BRGY-{{ str_pad($documentRequest->id, 6, '0', STR_PAD_LEFT) }}-{{ date('Y', strtotime($documentRequest->created_at)) }}
                </p>
            </div>
        </div>
    </div>
</body>

</html>
