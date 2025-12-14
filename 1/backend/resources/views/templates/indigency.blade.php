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
        <!-- Header with Logos -->
        <div class="header">
            <div class="logo-section">
                @php
                    $logoPath = str_replace('\\', '/', base_path('public/images/palo-logo.png'));
                @endphp
                <img src="{{ $logoPath }}" alt="Palo Seal" class="logo-left">
                <div class="header-text">
                    <h1>Republic of the Philippines</h1>
                    <div class="province">Province of Leyte</div>
                    <div class="municipality">Municipality of Palo</div>
                    <div class="barangay-name">BARANGAY SAN MIGUEL</div>
                </div>

            </div>
        </div>

        <!-- Certificate Title -->
        <div class="certificate-title">
            CERTIFICATE OF INDIGENCY
        </div>

        <!-- Main Content -->
        <div class="content">
            <p class="to-whom">TO WHOM IT MAY CONCERN:</p>

            <p class="certification-text">
                THIS IS TO CERTIFY that <strong>{{ strtoupper($documentRequest->user->name) }}</strong>,
                @if ($documentRequest->user->birthday)
                    born on {{ \Carbon\Carbon::parse($documentRequest->user->birthday)->format('F d, Y') }},
                @endif
                a bona fide resident of this barangay, is qualified as indigent and eligible for
                assistance programs of the barangay, municipality, and other government agencies.
            </p>

            <p class="certification-text">
                This CERTIFICATION is issued upon request of the above-named person for any legal purpose
                it may serve him/her best, given this <strong>{{ $documentRequest->updated_at->format('jS') }}</strong>
                day of
                <strong>{{ $documentRequest->updated_at->format('F Y') }}</strong>.
            </p>

            @if ($documentRequest->staff_message)
                <p class="certification-text">
                    <strong>Remarks:</strong> {{ $documentRequest->staff_message }}
                </p>
            @endif
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box-right">
                <div class="signature-line"></div>
                <div class="signature-name">[Punong Barangay Name]</div>
                <div class="signature-title">Punong Barangay</div>
                <div class="seal-disclaimer">(Not valid without the Barangay Dry Seal)</div>
            </div>
        </div>

        <!-- Reference Number -->
        <div class="reference-number">
            <p>Reference No:
                BRGY-{{ str_pad($documentRequest->id, 6, '0', STR_PAD_LEFT) }}-{{ date('Y', strtotime($documentRequest->created_at)) }}
            </p>
        </div>
    </div>
</body>

</html>
