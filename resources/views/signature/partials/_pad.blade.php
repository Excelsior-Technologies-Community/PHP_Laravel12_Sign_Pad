{{--
    File: resources/views/signature/partials/_pad.blade.php

    Reusable signing widget, included by both create.blade.php (standalone
    flow) and request-sign.blade.php (multi-signer flow).

    Expected variables:
    - $formAction      (required) where the signature gets POSTed
    - $hiddenFields    (optional array) extra hidden inputs, e.g. ['foo' => 'bar']
    - $showEmailField  (optional bool, default true)
    - $defaultEmail    (optional string)
    - $submitLabel     (optional string, default 'Save Signature')
--}}
@php
    $hiddenFields ??= [];
    $showEmailField ??= true;
    $defaultEmail ??= '';
    $submitLabel ??= 'Save Signature';
@endphp

<div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-4">
            {{ $errors->first() }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form id="signature-form" method="POST" action="{{ $formAction }}">
        @csrf
        @foreach ($hiddenFields as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <input type="hidden" name="signature" id="signature-data">

        @if ($showEmailField)
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Signer email</label>
                <input type="email" name="signer_email" value="{{ old('signer_email', $defaultEmail) }}" required
                    class="border border-gray-300 p-2 w-full rounded-lg focus:ring-2 focus:ring-blue-400 outline-none">
            </div>
        @else
            <p class="text-sm text-gray-500 mb-4">Signing as <span class="font-medium text-gray-700">{{ $defaultEmail }}</span></p>
        @endif

        <!-- PEN SETTINGS -->
        <div class="flex flex-wrap items-end gap-6 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Color</label>
                <input type="color" id="penColor" value="#000000" class="w-12 h-10 cursor-pointer border rounded">
            </div>

            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">
                    Line thickness <span id="widthLabel" class="text-gray-400">(1.0 – 3.0)</span>
                </label>
                <div class="flex gap-2">
                    <input type="range" id="minWidth" min="0.3" max="4" step="0.1" value="1" class="w-full" title="Thin end">
                    <input type="range" id="maxWidth" min="0.5" max="8" step="0.1" value="3" class="w-full" title="Thick end">
                </div>
            </div>

            <div class="flex-1 min-w-[160px]">
                <!--
                    Real hardware pressure (Apple Pencil / Surface Pen) isn't
                    something signature_pad reads directly -- there's no
                    pressure sensor on a mouse or trackpad anyway. Instead it
                    fakes a "pressure" feel from how FAST you move: slow
                    strokes come out thicker, fast strokes thinner. This
                    slider controls how strong that effect is.
                -->
                <label class="block text-xs font-medium text-gray-500 mb-1">Smoothness</label>
                <input type="range" id="smoothness" min="0.1" max="0.9" step="0.05" value="0.7" class="w-full">
            </div>

            <button type="button" id="fullscreenToggle"
                class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg border whitespace-nowrap">
                ⤢ Bigger view
            </button>
        </div>

        <!-- CANVAS -->
        <div id="pad-wrapper" class="border-4 border-dashed border-gray-400 rounded-lg relative">
            <canvas id="signature-pad" class="w-full block touch-none" style="height: 420px;"></canvas>
            <p class="absolute bottom-2 left-1/2 -translate-x-1/2 text-xs text-gray-400 pointer-events-none">
                Sign here — mouse, trackpad, or touch
            </p>
        </div>

        <div class="mt-4 flex gap-4">
            <button type="button" id="clear" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                Clear
            </button>
            <button type="submit" id="save" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                {{ $submitLabel }}
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
(function () {
    const canvas = document.getElementById('signature-pad');

    const signaturePad = new SignaturePad(canvas, {
        minWidth: 1,
        maxWidth: 3,
        velocityFilterWeight: 0.7,
        penColor: 'black',
    });

    // -----------------------------------------------------------------
    // THE FIX for "the ink lands somewhere other than where I actually
    // draw": a canvas has one size in CSS pixels (offsetWidth/Height) and
    // a SEPARATE internal pixel buffer (canvas.width/height). On any screen
    // with devicePixelRatio != 1 -- basically every laptop/phone screen
    // sold in the last several years -- those two have to be kept in sync
    // or strokes get mapped to the wrong spot. We also preserve whatever
    // is currently drawn across a resize instead of just wiping it out.
    // -----------------------------------------------------------------
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const data = signaturePad.toData();

        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);

        signaturePad.clear(); // required by signature_pad right after a manual resize
        if (data && data.length) {
            signaturePad.fromData(data);
        }
    }

    // Wait for layout to actually settle (avoids a race with the Tailwind
    // CDN script still injecting styles) before measuring the canvas, then
    // keep it correct on every resize/orientation change after that.
    window.addEventListener('load', resizeCanvas);
    window.addEventListener('resize', resizeCanvas);
    if (document.readyState === 'complete') resizeCanvas();

    // ---------------- Pen controls ----------------
    document.getElementById('penColor').addEventListener('input', (e) => {
        signaturePad.penColor = e.target.value;
    });

    const minWidthInput = document.getElementById('minWidth');
    const maxWidthInput = document.getElementById('maxWidth');
    const widthLabel = document.getElementById('widthLabel');

    function applyWidths() {
        let min = parseFloat(minWidthInput.value);
        let max = parseFloat(maxWidthInput.value);
        if (min > max) { // keep them sane no matter which slider moved last
            [min, max] = [max, min];
        }
        signaturePad.minWidth = min;
        signaturePad.maxWidth = max;
        widthLabel.textContent = `(${min.toFixed(1)} – ${max.toFixed(1)})`;
    }
    minWidthInput.addEventListener('input', applyWidths);
    maxWidthInput.addEventListener('input', applyWidths);

    document.getElementById('smoothness').addEventListener('input', (e) => {
        signaturePad.velocityFilterWeight = parseFloat(e.target.value);
    });

    document.getElementById('clear').addEventListener('click', () => signaturePad.clear());

    // ---------------- Bigger view (good for trackpad signing) ----------------
    let isExpanded = false;
    const fullscreenBtn = document.getElementById('fullscreenToggle');
    fullscreenBtn.addEventListener('click', () => {
        isExpanded = !isExpanded;
        canvas.style.height = isExpanded ? '70vh' : '420px';
        fullscreenBtn.textContent = isExpanded ? '⤡ Normal view' : '⤢ Bigger view';
        resizeCanvas();
    });

    // ---------------- Submit ----------------
    document.getElementById('signature-form').addEventListener('submit', (e) => {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert('Please sign before saving.');
            return;
        }
        document.getElementById('signature-data').value = signaturePad.toDataURL('image/png');
        document.getElementById('save').disabled = true;
        document.getElementById('save').textContent = 'Saving…';
    });
})();
</script>