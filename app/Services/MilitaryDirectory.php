<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MilitaryDirectory
{
    private ?string $ardBaseUrl;
    private ?string $navyBaseUrl;
    private ?string $navyToken;

    public function __construct()
    {
        $this->ardBaseUrl = config('services.ard.base_url');
        $this->navyBaseUrl = config('services.navy.base_url');
        $this->navyToken = config('services.navy.token');
    }

    /**
     * @return array<string,string>
     */
    public function search(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        // Prefer ARD when query looks like an identification (digits)
        if ($this->isArdConfigured() && ctype_digit(str_replace(['-', ' '], '', $query))) {
            $cedula = preg_replace('/[^0-9]/', '', $query);
            $person = $this->ardGetPerson($cedula);
            if (is_array($person)) {
                $id = $cedula;
                $name = $this->formatArdName($person) ?: ($person['nombre'] ?? $person['name'] ?? null);
                if ($name) {
                    return [$id => $name];
                }
            }
        }

        // Fallback to Navy directory if configured
        if ($this->isNavyConfigured()) {
            try {
                $resp = Http::withToken($this->navyToken)
                    ->acceptJson()
                    ->get(rtrim($this->navyBaseUrl, '/').'/users', [
                        'search' => $query,
                        'role' => 'military',
                        'limit' => 20,
                    ]);
                if ($resp->ok()) {
                    $data = $resp->json('data') ?? $resp->json();
                    if (is_array($data)) {
                        $out = [];
                        foreach ($data as $row) {
                            $id = (string) ($row['id'] ?? ($row['uuid'] ?? ''));
                            $name = trim(($row['name'] ?? '').' '.($row['last_name'] ?? ''));
                            if ($id !== '' && $name !== '') {
                                $out[$id] = $name;
                            }
                        }
                        return $out;
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return [];
    }

    public function getDisplayName(string $id): ?string
    {
        $id = trim($id);
        if ($id === '') {
            return null;
        }

        if ($this->isArdConfigured() && ctype_digit(str_replace(['-', ' '], '', $id))) {
            $cedula = preg_replace('/[^0-9]/', '', $id);
            $person = $this->ardGetPerson($cedula);
            if (is_array($person)) {
                $name = $this->formatArdName($person) ?: ($person['nombre'] ?? $person['name'] ?? null);
                return $name ?: null;
            }
        }

        if ($this->isNavyConfigured()) {
            try {
                $resp = Http::withToken($this->navyToken)
                    ->acceptJson()
                    ->get(rtrim($this->navyBaseUrl, '/').'/users/'.urlencode($id));
                if ($resp->ok()) {
                    $row = $resp->json('data') ?? $resp->json();
                    if (is_array($row)) {
                        $name = trim(($row['name'] ?? '').' '.($row['last_name'] ?? ''));
                        return $name !== '' ? $name : null;
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return null;
    }

    private function isArdConfigured(): bool
    {
        return !empty($this->ardBaseUrl);
    }

    private function isNavyConfigured(): bool
    {
        return !empty($this->navyBaseUrl) && !empty($this->navyToken);
    }

    private function ardGetPerson(string $cedula): array|string|null
    {
        if ($cedula === '') {
            return null;
        }
        try {
            $resp = Http::baseUrl(rtrim($this->ardBaseUrl, '/'))
                ->acceptJson()
                ->get('', [
                    'status' => 'A',
                    'cedula' => $cedula,
                ]);
            if (!$resp->ok()) {
                return null;
            }
            return $resp->json();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function formatArdName(array $row): ?string
    {
        $parts = [
            $row['nombres'] ?? $row['nombre'] ?? $row['name'] ?? null,
            $row['apellidos'] ?? $row['apellido'] ?? $row['last_name'] ?? null,
        ];
        $name = trim(implode(' ', array_filter($parts)));
        return $name !== '' ? $name : null;
    }
}
