<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ArmadaApi
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) (config('services.armada.base_url') ?? 'https://armada.mide.gob.do/api'), '/');
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '';
    }

    /**
     * @return array<mixed>|null
     */
    public function getPerson(string $cedula): ?array
    {
        $digits = preg_replace('/\D+/', '', (string) $cedula);
        if ($digits === '' || strlen($digits) !== 11) {
            return null;
        }
        try {
            $url = $this->baseUrl.'/Personals/militarCheck/'.urlencode($digits);
            $resp = Http::acceptJson()->get($url);
            if (! $resp->ok()) {
                return null;
            }
            $json = $resp->json();
            return is_array($json) ? $json : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function formatName(array $row): ?string
    {
        // Try common keys
        $first = $row['nombres'] ?? $row['nombre'] ?? $row['name'] ?? $row['first_name'] ?? null;
        $last = $row['apellidos'] ?? $row['apellido'] ?? $row['last_name'] ?? null;
        if (! $first && isset($row['data']) && is_array($row['data'])) {
            $data = $row['data'];
            $first = $first ?: ($data['nombres'] ?? $data['nombre'] ?? $data['name'] ?? $data['first_name'] ?? null);
            $last = $last ?: ($data['apellidos'] ?? $data['apellido'] ?? $data['last_name'] ?? null);
        }
        $name = trim(implode(' ', array_filter([$first, $last])));
        return $name !== '' ? $name : null;
    }
}
