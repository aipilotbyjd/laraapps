<?php

namespace App\Services\Credential;

use Illuminate\Support\Facades\Crypt;

class CredentialEncryption
{
    public function encrypt(array $data): string
    {
        return Crypt::encryptString(json_encode($data));
    }
    
    public function decrypt(string $encrypted): array
    {
        try {
            $decrypted = Crypt::decryptString($encrypted);
            return json_decode($decrypted, true);
        } catch (\Exception $e) {
            throw new \Exception('Failed to decrypt credentials: ' . $e->getMessage());
        }
    }
}