<?php

namespace App\Service;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class EncryptionService
{
    private $encryptionKey;

    public function __construct(string $encryptionKey)
    {
        $this->encryptionKey = $encryptionKey;
    }

    public function encrypt(string $data): string
    {
        $key = Key::loadFromAsciiSafeString($this->encryptionKey);
        return Crypto::encrypt($data, $key);
    }

    public function decrypt(string $encryptedData): string
    {
        $key = Key::loadFromAsciiSafeString($this->encryptionKey);
        return Crypto::decrypt($encryptedData, $key);
    }
}
