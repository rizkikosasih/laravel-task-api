<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected $errors;

    /**
     * @param string $message Pesan error umum untuk user (Misal: "Aksi ditolak").
     * @param array|string|null $errors Detail teknis atau pesan spesifik (Misal: ["status" => "Task done tidak bisa diubah"]).
     * @param int $code HTTP Status code (Default: 422 Unprocessable Entity).
     */
    public function __construct(string $message, array|string|null $errors = null, int $code = 422)
    {
        // Masukkan message dan code ke parent Exception bawaan PHP
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    /**
     * Ambil detail error tambahan.
     * * @return array|string|null
     */
    public function getErrors(): array|string|null
    {
        return $this->errors;
    }
}
