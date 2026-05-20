<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class DeltaSuryaApiService
{
    protected string $baseUrl = 'https://recruitment.rsdeltasurya.com/api/v1';

    /**
     * Get API Token and cache it
     */
    public function getToken(string $email, string $password): ?string
    {
        $cacheKey = 'delta_surya_api_token_' . md5($email);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::post("{$this->baseUrl}/auth", [
                'email' => $email,
                'password' => $password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['access_token']) && isset($data['expires_in'])) {
                    // Store in cache for expires_in minus 5 minutes (300 seconds) safety margin
                    $ttl = max(0, $data['expires_in'] - 300);
                    
                    if ($ttl > 0) {
                        Cache::put($cacheKey, $data['access_token'], $ttl);
                    }

                    return $data['access_token'];
                }
            }

            Log::error('DeltaSurya API Token Error: Invalid response format', ['response' => $response->body()]);
            return null;

        } catch (Exception $e) {
            Log::error('DeltaSurya API Token Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get insurances list and cache it for 1 hour
     */
    public function getInsurances(): array
    {
        $cacheKey = 'delta_surya_insurances';

        return Cache::remember($cacheKey, 3600, function () {
            try {
                // Here we need to grab the token. Assuming an environment/config based account or passing from session
                // For this example, I'll assume standard credentials or you would inject them.
                // Since email/password isn't passed here, let's assume it's fetched via config or context.
                // As instructed, we just need to use the Bearer Token from getToken().
                // I will add a helper method to resolve the token or assume it's available.
                $token = $this->resolveToken();
                
                if (!$token) {
                    throw new Exception('API token is missing.');
                }

                $response = Http::withToken($token)->get("{$this->baseUrl}/insurances");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('DeltaSurya API getInsurances Error', ['response' => $response->body()]);
                // throw exception to prevent caching empty array
                throw new Exception('Failed to get insurances');

            } catch (Exception $e) {
                Log::error('DeltaSurya API getInsurances Exception: ' . $e->getMessage());
                // throw exception to prevent caching empty array so it fails in Cache::remember but handles gracefully in controller if possible 
                // Actually, Cache::remember won't cache if we throw, but we want the controller to handle it.
                // Let's just return [] but we will use a different caching method if we want to avoid caching empty.
                // For now, let's just clear the cache on failure by forgetting it right before returning.
                Cache::forget('delta_surya_insurances');
                return [];
            }
        });
    }

    /**
     * Get procedures list and cache it for 1 hour
     */
    public function getProcedures(): array
    {
        $cacheKey = 'delta_surya_procedures';

        return Cache::remember($cacheKey, 3600, function () {
            try {
                $token = $this->resolveToken();
                
                if (!$token) {
                    throw new Exception('API token is missing.');
                }

                $response = Http::withToken($token)->get("{$this->baseUrl}/procedures");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('DeltaSurya API getProcedures Error', ['response' => $response->body()]);
                Cache::forget('delta_surya_procedures');
                return [];

            } catch (Exception $e) {
                Log::error('DeltaSurya API getProcedures Exception: ' . $e->getMessage());
                Cache::forget('delta_surya_procedures');
                return [];
            }
        });
    }

    /**
     * Get procedure price by ID and cache it for 1 hour
     */
    public function getProcedurePrice(string $procedureId): ?array
    {
        $cacheKey = 'delta_surya_procedure_price_' . $procedureId;

        return Cache::remember($cacheKey, 3600, function () use ($procedureId) {
            try {
                $token = $this->resolveToken();
                
                if (!$token) {
                    throw new Exception('API token is missing.');
                }

                $response = Http::withToken($token)->get("{$this->baseUrl}/procedures/{$procedureId}/prices");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error("DeltaSurya API getProcedurePrice Error for {$procedureId}", ['response' => $response->body()]);
                return null;

            } catch (Exception $e) {
                Log::error("DeltaSurya API getProcedurePrice Exception for {$procedureId}: " . $e->getMessage());
                return null;
            }
        });
    }
    
    /**
     * A helper method to resolve the token for subsequent requests.
     * You should modify this to use your application's actual API credentials.
     */
    protected function resolveToken(): ?string
    {
        // Replace with actual config values
        $email = config('services.deltasurya.email', 'test@example.com');
        $password = config('services.deltasurya.password', 'password');
        
        return $this->getToken($email, $password);
    }
}
