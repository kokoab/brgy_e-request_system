<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Recognition Certificate</title>
    <style>
        @page {
            margin: 15mm 20mm;
            size: A4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }

        .document-wrapper {
            max-width: 100%;
            margin: 0 auto;
        }

        /* Header Section */
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header .subtitle {
            font-size: 11pt;
            font-weight: normal;
            margin-top: 5px;
        }

        .header .barangay-name {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Certificate Title */
        .certificate-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 30px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Content Section */
        .content {
            margin: 30px 0;
            text-align: justify;
        }

        .content p {
            margin-bottom: 15px;
            line-height: 1.8;
        }

        .content .highlight {
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Details Section */
        .details-section {
            margin: 30px 0;
            padding: 20px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }

        .details-section h3 {
            font-size: 13pt;
            margin-bottom: 15px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 10px;
            padding: 5px 0;
        }

        .detail-label {
            font-weight: bold;
            width: 150px;
            min-width: 150px;
        }

        .detail-value {
            flex: 1;
        }

        /* Footer Section */
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin: 50px auto 10px;
            width: 200px;
        }

        .signature-name {
            font-weight: bold;
            margin-top: 5px;
        }

        .signature-title {
            font-size: 10pt;
            font-style: italic;
            margin-top: 3px;
        }

        /* Reference Number */
        .reference-number {
            margin-top: 20px;
            font-size: 10pt;
            color: #666;
            text-align: right;
        }

        /* Date Section */
        .date-section {
            margin-top: 20px;
            text-align: right;
        }

        /* Print-specific styles */
        @media print {
            .no-print {
                display: none;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
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
            Certificate of Recognition
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
