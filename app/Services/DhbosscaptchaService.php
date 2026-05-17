<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DhbossCaptchaService
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('app.dhboss.api_key');
        $this->apiUrl = config('app.dhboss.api_url');

        // dd($this->apiKey, $this->apiUrl); // Debug: Check if API key and URL are loaded
        if (empty($this->apiKey)) {
            throw new Exception('DHBOSS API key is not configured.');
        }
    }

    public function solve(string $imageBase64): array
    {
        $response = Http::asForm()->timeout(40)
            ->withHeaders([
                'Referer' => config('app.url'),
                'Content-Type' => 'application/json',
            ])
            ->post($this->apiUrl, [
                'apikeyfill' => $this->apiKey,
                'image_url'   => $imageBase64,
            ]);

        if ($response->failed()) {
            Log::error('DHBOSS API failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            throw new Exception('Captcha solving failed.');
        }

        return $response->json();
    }
}