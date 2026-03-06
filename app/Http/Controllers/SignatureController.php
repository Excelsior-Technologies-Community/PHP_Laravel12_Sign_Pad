<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Signature;
use Illuminate\Support\Str;

class SignatureController extends Controller
{

    // ✅ New method: index to list all signatures
    public function index()
    {
        // Get all signatures with related user
        $signatures = Signature::with('user')->latest()->get();

        return view('signature.index', compact('signatures'));
    }
    public function create()
    {
        return view('signature.create');
    }

   public function store(Request $request)
{
    $request->validate([
        'signature' => 'required'
    ]);

    $user = User::first();

    if (! $user) {
        return back()->with('error', 'No user found to attach signature');
    }

    // Get the base64 string
    $signatureData = $request->signature;

    // Remove base64 prefix if exists
    if (strpos($signatureData, 'base64,') !== false) {
        $signatureData = explode('base64,', $signatureData)[1];
    }

    // Decode base64
    $signatureBinary = base64_decode($signatureData);

    if ($signatureBinary === false) {
        return back()->with('error', 'Invalid signature data');
    }

    // Generate unique filename
    $fileName = 'signature_' . Str::uuid() . '.png';

    // Save in storage/app/public/signatures
    \Illuminate\Support\Facades\Storage::disk('public')->put('signatures/' . $fileName, $signatureBinary);

    // Save record in database
    $user->signature()->create([
        'filename' => $fileName
    ]);

    return redirect()->route('signature.index')->with('success', 'Signature saved successfully');
}

}