<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $signatureRequest->title }} | Laravel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-3xl mx-auto">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">{{ $signatureRequest->title }}</h2>
            <a href="{{ route('signature.request.index') }}" class="text-sm text-blue-600 hover:underline">
                ← Back to requests
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">

            <div class="flex items-center justify-between mb-6">
                <span class="text-sm text-gray-500">
                    Created {{ $signatureRequest->created_at->format('d M Y, h:i A') }}
                </span>

                @if($signatureRequest->status === 'completed')
                    <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">
                        ✅ Completed
                    </span>
                @else
                    <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-3 py-1 rounded-full">
                        ⏳ Pending
                    </span>
                @endif
            </div>

            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Signers</h3>

            <div class="space-y-3 mb-6">
                @foreach($signatureRequest->signatures as $slot)
                    <div class="border rounded-lg p-4 flex items-center justify-between gap-4">
                        <div>
                            <div class="font-medium text-gray-800">
                                {{ $slot->signer_order }}. {{ $slot->signer_email }}
                            </div>

                            @if($slot->status === 'approved')
                                <div class="text-xs text-green-600 mt-1">
                                    ✅ Signed {{ $slot->signed_at?->format('d M Y, h:i A') }}
                                    @if($slot->ip_address)
                                        from {{ $slot->ip_address }}
                                    @endif
                                </div>
                            @else
                                <div class="text-xs text-gray-400 mt-1">⏳ Waiting to sign</div>
                            @endif
                        </div>

                        @if($slot->status !== 'approved')
                            <button type="button"
                                class="copyLinkBtn text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg border whitespace-nowrap"
                                data-link="{{ route('signature.request.sign', $slot->uuid) }}">
                                📋 Copy signing link
                            </button>
                        @else
                            <img src="{{ asset('storage/signatures/'.$slot->filename) }}"
                                 class="h-12 border rounded shadow-sm bg-white p-1">
                        @endif
                    </div>
                @endforeach
            </div>

            <a href="{{ route('signature.request.pdf', $signatureRequest->uuid) }}"
               class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                📄 Download certificate (PDF)
            </a>
        </div>
    </div>

    <script>
    document.querySelectorAll('.copyLinkBtn').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const link = btn.dataset.link;
            const originalText = btn.textContent;

            try {
                await navigator.clipboard.writeText(link);
                btn.textContent = '✅ Copied!';
            } catch (err) {
                // Clipboard API can fail on non-HTTPS/local setups -- fall
                // back to just showing the link so it can be copied by hand.
                prompt('Copy this signing link:', link);
            }

            setTimeout(() => (btn.textContent = originalText), 1500);
        });
    });
    </script>

</body>
</html>