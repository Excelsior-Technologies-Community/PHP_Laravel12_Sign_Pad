<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Signature Certificate</title>
    <style>
        /*
            DomPDF doesn't support flexbox or CSS grid, so this whole
            document is laid out with plain tables/blocks instead -- that's
            intentional, not an oversight.
        */
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 13px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            color: #1e3a8a;
        }
        .badge {
            display: inline-block;
            background-color: #dcfce7;
            color: #15803d;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 6px;
        }
        table.details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        table.details td {
            padding: 8px 6px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        table.details td.label {
            width: 160px;
            color: #6b7280;
            font-weight: bold;
        }
        .signature-box {
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 16px;
            text-align: center;
        }
        .signature-box img {
            max-height: 120px;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Digital Signature Certificate</h1>
        @if($signature->isCertified())
            <div class="badge">✔ CERTIFIED</div>
        @endif
    </div>

    <table class="details">
        <tr>
            <td class="label">Signer name</td>
            <td>{{ $signature->user->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Signer email</td>
            <td>{{ $signature->signer_email ?? ($signature->user->email ?? 'N/A') }}</td>
        </tr>
        <tr>
            <td class="label">Signed at</td>
            <td>{{ $signature->signed_at?->format('d M Y, h:i A') ?? $signature->created_at->format('d M Y, h:i A') }}</td>
        </tr>
        <tr>
            <td class="label">IP address</td>
            <td>{{ $signature->ip_address ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Certificate ID</td>
            <td>{{ $signature->uuid }}</td>
        </tr>
    </table>

    <div class="signature-box">
        @if($signature->filename)
            {{-- public_path(), not asset(), because DomPDF resolves images
                 against the local filesystem -- it has no idea what your
                 app's URL is and can't fetch over HTTP during rendering. --}}
            <img src="{{ public_path('storage/signatures/' . $signature->filename) }}">
        @else
            <p style="color:#9ca3af;">No signature image on file.</p>
        @endif
    </div>

    <div class="footer">
        This certificate was generated automatically and confirms the signature above was captured electronically
        on the date and from the IP address shown.
    </div>

</body>
</html>