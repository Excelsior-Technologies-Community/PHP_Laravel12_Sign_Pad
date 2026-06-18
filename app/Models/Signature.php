<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Signature extends Model
{
    protected $guarded = [];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (Signature $signature) {
            if (empty($signature->uuid)) {
                $signature->uuid = (string) Str::uuid();
            }
        });
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'model_id');
    }

    public function request()
    {
        return $this->belongsTo(SignatureRequest::class, 'request_id');
    }

    public function isTurnToSign(): bool
    {
        if (!$this->request_id) {
            return true;
        }

        return !$this->request
            ->signatures()
            ->where('signer_order', '<', $this->signer_order)
            ->where('status', '!=', 'approved')
            ->exists();
    }
}