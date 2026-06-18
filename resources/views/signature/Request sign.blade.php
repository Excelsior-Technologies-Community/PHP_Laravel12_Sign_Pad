<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $signatureRequest->title }} | Sign</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-3xl mx-auto">

        <div class="mb-4">
            <h2 class="text-2xl font-bold text-gray-800">{{ $signatureRequest->title }}</h2>
            <p class="text-sm text-gray-500">
                Signer {{ $slot->signer_order }} of {{ $signatureRequest->signatures->count() }}
            </p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- ALL SIGNERS, with this one highlighted -->
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach($signatureRequest->signatures as $other)
                <span class="text-xs px-3 py-1 rounded-full font-medium
                    {{ $other->id === $slot->id ? 'ring-2 ring-blue-400 ' : '' }}
                    {{ $other->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $other->signer_order }}. {{ $other->signer_email }}
                    {{ $other->status === 'approved' ? '✅' : '⏳' }}
                </span>
            @endforeach
        </div>

        @if($slot->status === 'approved')
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <p class="text-lg text-green-700 font-medium mb-1">✅ You've already signed this document.</p>
                <p class="text-sm text-gray-500">
                    Signed on {{ $slot->signed_at?->format('d M Y, h:i A') }}.
                </p>
            </div>
        @elseif(!$isMyTurn)
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <p class="text-lg text-gray-700 font-medium mb-1">⏳ It's not your turn yet.</p>
                <p class="text-sm text-gray-500">
                    An earlier signer still needs to sign first. This page will work as soon as it's your turn --
                    feel free to check back, or just refresh later.
                </p>
            </div>
        @else
            {{-- Same shared canvas/pen-settings/submit widget used by the
                 standalone create.blade.php page. Here the email is fixed
                 (this signer was already invited by email) so we hide that
                 field and just show who's signing. --}}
            @include('signature.partials._pad', [
                'formAction' => route('signature.request.sign.store', $slot->uuid),
                'showEmailField' => false,
                'defaultEmail' => $slot->signer_email,
                'submitLabel' => 'Submit Signature',
            ])
        @endif

    </div>

</body>
</html>