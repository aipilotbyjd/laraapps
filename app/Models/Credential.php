<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Credential\CredentialEncryption;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Credential extends Model
{
    protected $fillable = [
        'name', 'type', 'encrypted_data', 
        'encryption_key', 'user_id', 'last_used_at'
    ];
    
    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    protected $hidden = ['encrypted_data', 'encryption_key'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setDataAttribute($value)
    {
        $encryptor = new CredentialEncryption();
        $this->attributes['encrypted_data'] = $encryptor->encrypt($value);
    }

    public function getDataAttribute()
    {
        $encryptor = new CredentialEncryption();
        return $encryptor->decrypt($this->encrypted_data);
    }

    public function markAsUsed()
    {
        $this->update(['last_used_at' => now()]);
    }
}