<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Signing Certificate - {{ $signatureRequest->title }}</title>
    <style>
        /* Same DomPDF-safe, table/block-only layout as pdf.blade.php. */
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 13px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header h1 {
            margin: 0 0 4px 0;
            font-size: 20px;
            color: #1e3a8a;
        }
        .header .subtitle {
            color: #6b7280;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 6px;
        }
        .status-badge.completed { background-color: #dcfce7; color: #15803d; }
        .status-badge.pending { background-color: #fef9c3; color: #a16207; }

        table.signers {
            width: 100%;
            border-collapse: collapse;
        }
        table.signers td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            vertical-align: top;
        }
        td.order-col {
            width: 28px;
            text-align: center;
            font-weight: bold;
            color: #6b7280;
        }
        td.image-col {
            width: 140px;
            text-align: center;
        }
        td.image-col img {
            max-height: 70px;
        }
        .pending-text {
            color: #9ca3af;
            font-style: italic;
        }
        .signed-text {
            color: #15803d;
            font-size: 11px;
        }
        .footer {
            margin-top: 24px;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Multi-Signer Certificate</h1>
        <div class="subtitle">{{ $signatureRequest->title }}</div>
        <div class="status-badge {{ $signatureRequest->status === 'completed' ? 'completed' : 'pending' }}">
            {{ $signatureRequest->status === 'completed' ? '✔ ALL SIGNERS COMPLETE' : '⏳ IN PROGRESS' }}
        </div>
    </div>

    <table class="signers">
        @foreach($signatureRequest->signatures as $slot)
            <tr>
                <td class="order-col">{{ $slot->signer_order }}</td>

                <td>
                    <strong>{{ $slot->signer_email }}</strong><br>

                    @if($slot->status === 'approved')
                        <span class="signed-text">
                            ✔ Signed {{ $slot->signed_at?->format('d M Y, h:i A') }}
                            @if($slot->ip_address)
                                from {{ $slot->ip_address }}
                            @endif
                        </span>
                    @else
                        <span class="pending-text">Awaiting signature</span>
                    @endif
                </td>

                <td class="image-col">
                    @if($slot->status === 'approved' && $slot->filename)
                        <img src="{{ public_path('storage/signatures/' . $slot->filename) }}">
                    @else
                        <span class="pending-text">—</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    <div class="footer">
        Certificate ID: {{ $signatureRequest->uuid }} &nbsp;|&nbsp;
        Generated {{ now()->format('d M Y, h:i A') }} &nbsp;|&nbsp;
        This certificate reflects the signing status of every signer at the time it was generated.
    </div>

</body>
</html>