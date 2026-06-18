<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Signature;
use App\Models\SignatureRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class SignatureController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $signatures = Signature::with('user')
            ->whereNull('request_id')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            })
            ->latest()
            ->paginate(5);

        return view('signature.index', compact('signatures'));
    }

    public function create()
    {
        return view('signature.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'signature' => 'required',
            'signer_email' => 'required|email',
        ]);

        $user = User::where('email', $request->signer_email)->first();

        if (!$user) {
            return back()->with('error', 'Signer not found')->withInput();
        }

        $fileName = $this->saveSignatureImage($request->signature);

        if (!$fileName) {
            return back()->with('error', 'Signature invalid')->withInput();
        }

        Signature::create([
            'model_type' => User::class,
            'model_id' => $user->id,
            'filename' => $fileName,
            'signer_email' => $request->signer_email,
            'ip_address' => $request->ip(),
            'status' => 'approved',
            'signer_order' => 1,
            'certified' => true,
            'signed_at' => now(),
        ]);

        return redirect()->route('signature.index')->with('success', 'Signature saved successfully');
    }

    public function exportPdf($id)
    {
        $signature = Signature::with('user')->findOrFail($id);
        $pdf = Pdf::loadView('signature.pdf', compact('signature'));
        return $pdf->download('Certificate_' . $signature->uuid . '.pdf');
    }

    public function destroy($id)
    {
        $signature = Signature::findOrFail($id);
        if ($signature->filename) {
            Storage::disk('public')->delete('signatures/' . $signature->filename);
        }
        $signature->delete();
        return back()->with('success', 'Signature deleted successfully');
    }

    public function requestIndex()
    {
        $requests = SignatureRequest::withCount('signatures')
            ->latest()
            ->paginate(10);

        return view('signature.request-index', compact('requests'));
    }

    public function createRequest()
    {
        return view('signature.request-create');
    }

    public function storeRequest(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'signers' => 'required|array|min:1',
            'signers.*.email' => 'required|email',
            'signers.*.name' => 'nullable|string|max:255',
        ]);

        $signatureRequest = SignatureRequest::create([
            'title' => $validated['title'],
            'created_by' => $request->user()->id ?? null,
            'status' => 'pending',
            'uuid' => (string) Str::uuid(),
        ]);

        foreach (array_values($validated['signers']) as $index => $signer) {
            $signatureRequest->signatures()->create([
                'model_type' => User::class,
                'model_id' => User::where('email', $signer['email'])->value('id'),
                'signer_email' => $signer['email'],
                'signer_order' => $index + 1,
                'status' => 'pending',
                'certified' => false,
                'uuid' => (string) Str::uuid(),
            ]);
        }

        return redirect()
            ->route('signature.request.show', $signatureRequest->uuid)
            ->with('success', 'Request created successfully.');
    }

    public function showRequest($uuid)
    {
        $signatureRequest = SignatureRequest::where('uuid', $uuid)
            ->with('signatures')
            ->firstOrFail();

        return view('signature.request-status', compact('signatureRequest'));
    }

    public function showRequestSignPage($signatureUuid)
    {
        $slot = Signature::where('uuid', $signatureUuid)
            ->with('request.signatures')
            ->firstOrFail();

        return view('signature.request-sign', [
            'slot' => $slot,
            'signatureRequest' => $slot->request,
            'isMyTurn' => $slot->status === 'pending' && $slot->isTurnToSign(),
        ]);
    }

    public function storeRequestSignature(Request $request, $signatureUuid)
    {
        $request->validate(['signature' => 'required']);

        $slot = Signature::where('uuid', $signatureUuid)->with('request')->firstOrFail();

        if ($slot->status !== 'pending') {
            return back()->with('error', 'Already signed.');
        }

        $fileName = $this->saveSignatureImage($request->signature);

        if (!$fileName) {
            return back()->with('error', 'Invalid signature.');
        }

        $slot->update([
            'filename' => $fileName,
            'ip_address' => $request->ip(),
            'status' => 'approved',
            'certified' => true,
            'signed_at' => now(),
        ]);

        $slot->request->refreshStatus();

        return redirect()
            ->route('signature.request.sign', $slot->uuid)
            ->with('success', 'Signature recorded.');
    }

    public function exportRequestPdf($uuid)
    {
        $signatureRequest = SignatureRequest::where('uuid', $uuid)
            ->with('signatures')
            ->firstOrFail();

        $pdf = Pdf::loadView('signature.request-pdf', compact('signatureRequest'));
        return $pdf->download('Certificate_' . $signatureRequest->uuid . '.pdf');
    }

  private function saveSignatureImage($dataUrl): ?string
{
    if (empty($dataUrl)) {
        return null;
    }

    if (is_array($dataUrl)) {
        $dataUrl = $dataUrl[0] ?? null;
    }

    if (!$dataUrl || !str_contains($dataUrl, 'base64,')) {
        return null;
    }

    $parts = explode('base64,', $dataUrl);

    if (!isset($parts[1])) {
        return null;
    }

    $base64Data = trim($parts[1]);

    $binary = base64_decode($base64Data, true);

    if ($binary === false) {
        return null;
    }

    $fileName = 'signature_' . Str::uuid() . '.png';

    Storage::disk('public')->put(
        'signatures/' . $fileName,
        $binary
    );

    return $fileName;
}
}