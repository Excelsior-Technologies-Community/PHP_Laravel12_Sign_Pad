<!DOCTYPE html>
<html>
<head>
    <title>Signatures</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-7xl mx-auto py-8 px-4">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">✍️ Signature Management</h1>

        <a href="{{ route('signature.create') }}"
           class="bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-lg shadow">
            + Add Signature
        </a>
    </div>

    <!-- ALERT -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- SEARCH BAR -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="🔍 Search by name or email..."
                class="border border-gray-300 p-2 w-full rounded-lg focus:ring-2 focus:ring-blue-400 outline-none">

            <button class="bg-blue-500 hover:bg-blue-600 text-white px-5 rounded-lg">
                Search
            </button>
        </form>
    </div>

    <!-- TABLE CARD -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">

        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">ID</th>
                    <th class="py-3 px-4 text-left">User</th>
                    <th class="py-3 px-4 text-center">Signature</th>
                    <th class="py-3 px-4 text-center">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($signatures as $signature)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium">#{{ $signature->id }}</td>

                    <td class="px-4 py-3">
                        <div class="font-semibold text-gray-800">
                            {{ $signature->user->name ?? 'N/A' }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $signature->user->email ?? '' }}
                        </div>
                    </td>

                    <td class="px-4 py-3 text-center">
                        <img 
                            src="{{ asset('storage/signatures/'.$signature->filename) }}"
                            onerror="this.src='https://via.placeholder.com/100?text=No+Image'"
                            class="h-14 mx-auto border rounded shadow-sm bg-white p-1">
                    </td>

                    <td class="px-4 py-3 text-center">
                        <form action="{{ route('signature.delete', $signature->id) }}"
                              method="POST"
                              onsubmit="return confirm('Delete this signature?')">
                            @csrf
                            @method('DELETE')

                            <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-1 rounded-lg shadow">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-6 text-gray-500">
                        🚫 No signatures found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <!-- PAGINATION -->
    <div class="mt-6 flex justify-between items-center">
        <p class="text-sm text-gray-600">
            Showing {{ $signatures->firstItem() }} to {{ $signatures->lastItem() }}
            of {{ $signatures->total() }} results
        </p>

        {{ $signatures->links() }}
    </div>

</div>

</body>
</html>