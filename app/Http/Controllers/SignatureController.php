<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Signature;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SignatureController extends Controller
{
    // ✅ LIST + SEARCH + PAGINATION
    public function index(Request $request)
    {
        $search = $request->search;

        $signatures = Signature::with('user')
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

    // ✅ STORE
    public function store(Request $request)
    {
        $request->validate([
            'signature' => 'required'
        ]);

        $user = User::first();

        if (! $user) {
            return back()->with('error', 'No user found');
        }

        $signatureData = $request->signature;

        if (strpos($signatureData, 'base64,') !== false) {
            $signatureData = explode('base64,', $signatureData)[1];
        }

        $signatureBinary = base64_decode($signatureData);

        if ($signatureBinary === false) {
            return back()->with('error', 'Invalid signature');
        }

        $fileName = 'signature_' . Str::uuid() . '.png';

        Storage::disk('public')->put('signatures/' . $fileName, $signatureBinary);

        $user->signature()->create([
            'filename' => $fileName
        ]);

        return redirect()->route('signature.index')->with('success', 'Signature saved successfully');
    }

    // ✅ DELETE FUNCTION
    public function destroy($id)
    {
        $signature = Signature::findOrFail($id);

        // delete file
        Storage::disk('public')->delete('signatures/' . $signature->filename);

        $signature->delete();

        return back()->with('success', 'Signature deleted successfully');
    }
}