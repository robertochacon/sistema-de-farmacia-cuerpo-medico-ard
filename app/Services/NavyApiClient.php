<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NavyApiClient
{
    private readonly ?string $baseUrl;
    private readonly ?string $token;

    public function __construct(?string $baseUrl = null, ?string $token = null)
    {
        $this->baseUrl = $baseUrl ?? config('services.navy.base_url');
        $this->token = $token ?? config('services.navy.token');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->baseUrl) && ! empty($this->token);
    }

    /**
     * @return array<string,string> id => label
     */
    public function searchUsers(string $query, ?string $role = null): array
    {
        if (! $this->isConfigured() || trim($query) === '') {
            return [];
        }

        try {
            $response = Http::withToken($this->token)
                ->acceptJson()
                ->get(rtrim($this->baseUrl, '/').'/users', array_filter([
                    'search' => $query,
                    'role' => $role,
                    'limit' => 20,
                ]));

            if (! $response->ok()) {
                return [];
            }

            $data = $response->json('data') ?? $response->json();
            if (! is_array($data)) {
                return [];
            }

            $results = [];
            foreach ($data as $row) {
                $id = (string) ($row['id'] ?? ($row['uuid'] ?? ''));
                $name = trim(($row['name'] ?? '').' '.($row['last_name'] ?? ''));
                if ($id !== '' && $name !== '') {
                    $results[$id] = $name;
                }
            }
            return $results;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getUserName(string $userId): ?string
    {
        if (! $this->isConfigured() || trim($userId) === '') {
            return null;
        }

        try {
            $response = Http::withToken($this->token)
                ->acceptJson()
                ->get(rtrim($this->baseUrl, '/').'/users/'.urlencode($userId));

            if (! $response->ok()) {
                return null;
            }

            $row = $response->json('data') ?? $response->json();
            if (! is_array($row)) {
                return null;
            }

            $name = trim(($row['name'] ?? '').' '.($row['last_name'] ?? ''));
            return $name !== '' ? $name : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
