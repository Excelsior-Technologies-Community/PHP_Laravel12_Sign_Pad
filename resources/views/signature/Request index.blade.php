<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-signer Requests | Laravel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-7xl mx-auto py-8 px-4">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">👥 Multi-signer Requests</h1>
            <a href="{{ route('signature.index') }}" class="text-sm text-blue-600 hover:underline">
                ← Back to standalone signatures
            </a>
        </div>

        <a href="{{ route('signature.request.create') }}"
           class="bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-lg shadow">
            + New Request
        </a>
    </div>

    <!-- ALERT -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- TABLE CARD -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">

        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">Title</th>
                    <th class="py-3 px-4 text-center">Signers</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-left">Created</th>
                    <th class="py-3 px-4 text-center">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($requests as $signatureRequest)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $signatureRequest->title }}</td>

                    <td class="px-4 py-3 text-center">{{ $signatureRequest->signatures_count }}</td>

                    <td class="px-4 py-3 text-center">
                        @if($signatureRequest->status === 'completed')
                            <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">
                                ✅ Completed
                            </span>
                        @else
                            <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-3 py-1 rounded-full">
                                ⏳ Pending
                            </span>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-gray-500">{{ $signatureRequest->created_at->format('d M Y, h:i A') }}</td>

                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('signature.request.show', $signatureRequest->uuid) }}"
                           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1 rounded-lg shadow">
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-6 text-gray-500">
                        🚫 No signing requests yet
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <!-- PAGINATION -->
    <div class="mt-6 flex justify-between items-center">
        <p class="text-sm text-gray-600">
            Showing {{ $requests->firstItem() ?? 0 }} to {{ $requests->lastItem() ?? 0 }}
            of {{ $requests->total() }} results
        </p>

        {{ $requests->links() }}
    </div>

</div>

</body>
</html>