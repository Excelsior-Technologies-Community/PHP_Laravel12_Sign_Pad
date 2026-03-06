<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Signatures</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-10">

    <div class="w-full max-w-6xl bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-3xl font-bold text-gray-800 text-center mb-6">All Saved Signatures</h2>

        @if(session('success'))
            <p class="text-green-600 text-center mb-4 font-medium">{{ session('success') }}</p>
        @endif

        <div class="flex justify-center mb-6">
            <a href="{{ route('signature.create') }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                Add New Signature
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-gray-700 font-medium border-b">ID</th>
                        <th class="py-3 px-4 text-gray-700 font-medium border-b">User</th>
                        <th class="py-3 px-4 text-gray-700 font-medium border-b">Filename</th>
                        <th class="py-3 px-4 text-gray-700 font-medium border-b">Preview</th>
                        <th class="py-3 px-4 text-gray-700 font-medium border-b">Created At</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($signatures as $signature)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-2 px-4 text-center">{{ $signature->id }}</td>
                        <td class="py-2 px-4 text-center">{{ $signature->user->name ?? 'N/A' }}</td>
                        <td class="py-2 px-4 text-center">{{ $signature->filename }}</td>
                        <td class="py-2 px-4 text-center">
                            <img src="{{ asset('storage/signatures/' . $signature->filename) }}" alt="Signature" class="mx-auto h-24 object-contain border rounded-md shadow-sm">
                        </td>
                        <td class="py-2 px-4 text-center">{{ $signature->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-gray-500 italic">No signatures found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>