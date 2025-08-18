<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ARD
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.ard.base_url'), '/');
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '';
    }

    /**
     * @return array<mixed>|null
     */
    public function getPerson(string $identification): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }
        $id = preg_replace('/[^0-9]/', '', (string) $identification);
        if ($id === '') {
            return null;
        }
        try {
            $response = Http::baseUrl($this->baseUrl)
                ->acceptJson()
                ->get('', [
                    'status' => 'A',
                    'cedula' => $id,
                ]);
            if (! $response->ok()) {
                return null;
            }
            $data = $response->json();
            return is_array($data) ? $data : null;
        } catch (\Throwable $e) {
            Log::error('ARD error: '.$e->getMessage());
            return null;
        }
    }

    public function formatName(array $row): ?string
    {
        $first = $row['nombres'] ?? $row['nombre'] ?? $row['name'] ?? null;
        $last = $row['apellidos'] ?? $row['apellido'] ?? $row['last_name'] ?? null;
        $name = trim(implode(' ', array_filter([$first, $last])));
        return $name !== '' ? $name : null;
    }
}
