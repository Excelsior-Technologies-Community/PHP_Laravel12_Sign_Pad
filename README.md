# PHP_Laravel12_Sign_Pad

## Introduction

PHP_Laravel12_Sign_Pad is a Laravel 12 project that demonstrates how to capture, store, and manage digital signatures in a web application. Users can draw signatures on a canvas, submit them, and have them securely saved in both the database and the storage system.

This project uses the Creagia Laravel Sign Pad package to handle:

- Signature capture via a responsive signature pad

- Automatic image generation and storage

- Database management with polymorphic relationships

Digital signatures are widely used in:

- Online contract signing and approvals

- Delivery confirmation systems

- Employee attendance and timesheets

- Legal document verification

This project provides a clean and practical implementation of these features using Laravel's MVC architecture.

---

## Project Overview

The project includes the following core features:

1) Signature Capture: Users can draw a digital signature using a responsive canvas-based signature pad.

2) Signature Storage: The drawn signature is converted into an image, stored in the Laravel storage system, and tracked in the database.

3) Database Management: Uses polymorphic relationships to attach signatures to models (e.g., users).

4) User Interface: Clean and modern interface using Tailwind CSS for both creating and viewing signatures.

5) Signature Management:

- List all saved signatures

- Preview signature images

- Add new signatures through a user-friendly form

6) Package Integration: Leverages Creagia Laravel Sign Pad for robust handling of signatures, including validation, storage, and model relationships.

---

## Technology Stack

| Technology               | Description                               |
| ------------------------ | ----------------------------------------- |
| PHP                      | Backend programming language              |
| Laravel 12               | PHP framework                             |
| MySQL                    | Database                                  |
| Signature Pad JS         | JavaScript library for drawing signatures |
| Creagia Laravel Sign Pad | Laravel package for handling signatures   |

---

## Project Setup

## Step 1 — Create Laravel 12 Project

Run the following command to create the project.

```bash
composer create-project laravel/laravel PHP_Laravel12_Sign_Pad "12.*"
```
Move into the project directory.

```bash
cd PHP_Laravel12_Sign_Pad
```
---

## Step 2 — Configure Database

Open the .env file and update the database configuration.

Example:

```.env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel12_sign_pad
DB_USERNAME=root
DB_PASSWORD=
```

Run Migration Command:

```bash
php artisan migrate
```

---

## Step 3 — Install Laravel Sign Pad Package

Install the official package.

```bash
composer require creagia/laravel-sign-pad
```

This package provides:

- Signature storage

- Image generation

- Database management

- Model relationships

---

## Step 4 — Publish Package Configuration

Publish the configuration file.

```bash
php artisan vendor:publish --tag=sign-pad-config
```

This will create:

```bash
config/sign-pad.php
```

---

## Step 5 — Publish Migration

Publish the package migration.

```bash
php artisan vendor:publish --tag=sign-pad-migrations
```

This creates the migration file:

```bash
database/migrations/create_signatures_table.php
```

---

## Step 6 — Update Migration Structure

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->string('uuid')->nullable(); // Make nullable
            $table->string('filename');
            $table->string('document_filename')->nullable();
            $table->boolean('certified')->default(false);
            $table->json('from_ips')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('signatures');
    }
};
```

---

## Step 7 — Run Migration

Run the migrations.

```bash
php artisan migrate
```
This creates the signatures table in the database.

---

## Step 8 — Model

### Signature.php

```bash
php artisan make:model Signature
```

Open:

app/Models/Signature.php


```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Signature extends Model
{
    protected $guarded = []; // Allow mass assignment for all fields

    /**
     * Automatically generate UUID when creating a record
     */
    protected static function booted()
    {
        static::creating(function ($signature) {
            if (empty($signature->uuid)) {
                $signature->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Polymorphic relation to any model
     */
    public function model()
    {
        return $this->morphTo();
    }

        // A signature belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class, 'model_id'); // Assuming model_id stores user id
    }
}
```

### Update User.php

Open:

app/Models/User.php

```php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Creagia\LaravelSignPad\Concerns\RequiresSignature;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, RequiresSignature;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

        /**
     * MorphOne relation to Signature
     */
    public function signature()
    {
        return $this->morphOne(Signature::class, 'model');
    }
}

```

---

## Step 9 — Create Controller

Create the controller.

```bash
php artisan make:controller SignatureController
```

Controller location:

app/Http/Controllers/SignatureController.php

Controller code:

```php
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
```

---

## Step 10 — Create Blade Files

Create folder:

resources/views/signature

### create.blade.php

Create file:

resources/views/signature/create.blade.php

View code:

```html
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
```

### index.blade.php

Create file:

resources/views/signature/index.blade.php

View code:

```html
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
```

---

## Step 11 — Define Routes

Open:

routes/web.php

Add routes:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignatureController;


// New route for index page
Route::get('/signature', [SignatureController::class, 'index'])->name('signature.index');

Route::get('/signature/create',[SignatureController::class,'create'])->name('signature.create');

Route::post('/signature',[SignatureController::class,'store'])->name('signature.store');

Route::get('/', function () {
    return view('welcome');
});
```

---

## Step 12 — Create Storage Link

Laravel stores signatures inside storage.

Run:

```bash
php artisan storage:link
```

This links:

```
storage/app/public
```

to

```
public/storage
```

---

## Step 13: Create User Using Tinker

Run this in tinker or seed a user:

```bash
php artisan tinker
```

Then:

```
User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password')
]);
```

Exit tinker. Now you have at least one user.

---

## Step 14 — Run the Project

Start the Laravel development server.

```bash
php artisan serve
```
Open the application:

```bash
http://127.0.0.1:8000/signature
```

---

## Output

<img width="1919" height="1028" alt="Screenshot 2026-03-06 150107" src="https://github.com/user-attachments/assets/fc06be25-7a3f-47b1-9705-d75d2ef3bf66" />

<img width="1918" height="1030" alt="Screenshot 2026-03-06 150217" src="https://github.com/user-attachments/assets/7e43877e-1cc2-4616-b106-e3feb8106fe0" />

---

## Project Structure

```
PHP_Laravel12_Sign_Pad
│
├── app
│   ├── Http
│   │   └── Controllers
│   │       └── SignatureController.php
│   │
│   └── Models
│       ├── User.php
│       └── Signature.php
│
├── config
│   └── sign-pad.php
│
├── database
│   └── migrations
│       └── create_signatures_table.php
│
├── public
│   └── storage → symbolic link to ../storage/app/public
│
├── resources
│   └── views
│       └── signature
│           ├── create.blade.php
│           └── index.blade.php
│
├── routes
│   └── web.php
│
├── storage
│   └── app
│       └── public
│           └── signatures      <-- all signature images saved here
│
└── .env          <-- Laravel environment configuration file
```

---

Your PHP_Laravel12_Sign_Pad Project is now ready!

 

