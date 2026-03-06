<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Pad | Laravel</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Signature Pad -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

    <style>
        #signature-pad {
            border: 2px dashed #4A5568; /* Tailwind gray-700 */
            border-radius: 8px;
            width: 100%;
            max-width: 500px;
            height: 250px;
            background-color: #F7FAFC; /* Tailwind gray-50 */
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-2xl bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Sign Here</h2>

        @if(session('success'))
            <p class="text-green-600 text-center mb-4">{{ session('success') }}</p>
        @endif

        @if(session('error'))
            <p class="text-red-600 text-center mb-4">{{ session('error') }}</p>
        @endif

        <form method="POST" action="{{ route('signature.store') }}" class="flex flex-col items-center">
            @csrf

            <!-- Signature Pad Canvas -->
            <canvas id="signature-pad" class="mb-4"></canvas>

            <!-- Hidden input to store signature -->
            <input type="hidden" name="signature" id="signature">

            <div class="flex gap-4 mt-2">
                <button type="button" id="clear" class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg font-medium transition">
                    Clear
                </button>

                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                    Save Signature
                </button>
            </div>
        </form>
    </div>

    <script>
        const canvas = document.getElementById('signature-pad');
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(247, 250, 252)' // Tailwind gray-50
        });

        // Submit signature
        document.querySelector("form").addEventListener("submit", function(e){
            if(!signaturePad.isEmpty()){
                const data = signaturePad.toDataURL();
                document.getElementById("signature").value = data;
            } else {
                alert("Please provide a signature before submitting!");
                e.preventDefault();
            }
        });

        // Clear signature
        document.getElementById("clear").addEventListener("click", function(){
            signaturePad.clear();
        });
    </script>
</body>
</html>