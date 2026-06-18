<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SignatureRequest extends Model
{
    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function (SignatureRequest $request) {
            if (empty($request->uuid)) {
                $request->uuid = (string) Str::uuid();
            }
        });
    }

    public function signatures()
    {
        return $this->hasMany(Signature::class, 'request_id')
            ->orderBy('signer_order');
    }

    public function refreshStatus(): void
    {
        $isCompleted = $this->signatures()->where('status', '!=', 'approved')->doesntExist();
        $this->update(['status' => $isCompleted ? 'completed' : 'pending']);
    }
}