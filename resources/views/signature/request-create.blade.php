<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Signing Request | Laravel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-2xl mx-auto">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">👥 New Multi-signer Request</h2>
            <a href="{{ route('signature.request.index') }}" class="text-sm text-blue-600 hover:underline">
                ← Back to requests
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">

            @if ($errors->any())
                <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('signature.request.store') }}">
                @csrf

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Document / request title</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        placeholder="e.g. Vendor Agreement - Acme Corp"
                        class="border border-gray-300 p-2 w-full rounded-lg focus:ring-2 focus:ring-blue-400 outline-none">
                </div>

                <div class="mb-3 flex items-center justify-between">
                    <label class="block text-sm font-medium text-gray-700">
                        Signers <span class="text-gray-400">(they will sign in this order, top to bottom)</span>
                    </label>
                    <button type="button" id="addSignerBtn"
                        class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-lg border">
                        + Add signer
                    </button>
                </div>

                <div id="signerRows" class="space-y-3 mb-6">
                    {{-- Two starter rows. JS below clones this same markup
                         shape and renumbers the name[] attributes whenever a
                         row is added or removed. --}}
                    <div class="signer-row flex gap-3 items-start">
                        <span class="signer-order mt-3 w-6 text-center text-sm text-gray-400 font-medium">1</span>
                        <input type="text" class="signer-name border border-gray-300 p-2 flex-1 rounded-lg" placeholder="Name (optional)">
                        <input type="email" class="signer-email border border-gray-300 p-2 flex-1 rounded-lg" placeholder="Email (required)" required>
                        <button type="button" class="removeSignerBtn text-red-500 hover:text-red-700 mt-2 px-2" title="Remove">✕</button>
                    </div>
                    <div class="signer-row flex gap-3 items-start">
                        <span class="signer-order mt-3 w-6 text-center text-sm text-gray-400 font-medium">2</span>
                        <input type="text" class="signer-name border border-gray-300 p-2 flex-1 rounded-lg" placeholder="Name (optional)">
                        <input type="email" class="signer-email border border-gray-300 p-2 flex-1 rounded-lg" placeholder="Email (required)" required>
                        <button type="button" class="removeSignerBtn text-red-500 hover:text-red-700 mt-2 px-2" title="Remove">✕</button>
                    </div>
                </div>

                {{-- The actual signers[i][name]/signers[i][email] hidden
                     inputs get (re)generated right before submit, in
                     buildHiddenInputs() below, so the indices are always a
                     clean 0..N-1 no matter how many rows were added or
                     removed in between. --}}
                <div id="hiddenInputs"></div>

                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Create Request
                </button>
            </form>
        </div>
    </div>

    <script>
    (function () {
        const rowsContainer = document.getElementById('signerRows');
        const addBtn = document.getElementById('addSignerBtn');
        const form = document.querySelector('form');
        const hiddenInputsContainer = document.getElementById('hiddenInputs');

        function renumberRows() {
            rowsContainer.querySelectorAll('.signer-row').forEach((row, index) => {
                row.querySelector('.signer-order').textContent = index + 1;
            });
        }

        function addRow() {
            const template = rowsContainer.querySelector('.signer-row');
            const newRow = template.cloneNode(true);
            newRow.querySelectorAll('input').forEach((input) => (input.value = ''));
            rowsContainer.appendChild(newRow);
            renumberRows();
        }

        addBtn.addEventListener('click', addRow);

        rowsContainer.addEventListener('click', (e) => {
            if (!e.target.classList.contains('removeSignerBtn')) return;

            // Always keep at least one signer row.
            if (rowsContainer.querySelectorAll('.signer-row').length <= 1) return;

            e.target.closest('.signer-row').remove();
            renumberRows();
        });

        // Convert the visible name/email inputs into the indexed
        // signers[i][name] / signers[i][email] fields Laravel's validator
        // expects, right before the form actually submits.
        form.addEventListener('submit', () => {
            hiddenInputsContainer.innerHTML = '';

            rowsContainer.querySelectorAll('.signer-row').forEach((row, index) => {
                const name = row.querySelector('.signer-name').value;
                const email = row.querySelector('.signer-email').value;

                const nameInput = document.createElement('input');
                nameInput.type = 'hidden';
                nameInput.name = `signers[${index}][name]`;
                nameInput.value = name;

                const emailInput = document.createElement('input');
                emailInput.type = 'hidden';
                emailInput.name = `signers[${index}][email]`;
                emailInput.value = email;

                hiddenInputsContainer.append(nameInput, emailInput);
            });
        });
    })();
    </script>

</body>
</html>