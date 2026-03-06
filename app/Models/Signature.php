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