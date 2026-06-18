<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Pad | Laravel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-3xl mx-auto">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">✍️ New Signature</h2>

            <a href="{{ route('signature.index') }}" class="text-sm text-blue-600 hover:underline">
                ← Back to list
            </a>
        </div>

        {{-- The actual canvas + pen settings + submit logic all live in this
             one shared partial so the standalone flow (this page) and the
             multi-signer flow (request-sign.blade.php) don't duplicate the
             same ~190 lines of JS. See partials/_pad.blade.php for details. --}}
        @include('signature.partials._pad', [
            'formAction' => route('signature.store'),
            'showEmailField' => true,
            'submitLabel' => 'Save Signature',
        ])

    </div>

</body>
</html>