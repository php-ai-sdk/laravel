<?php

namespace PhpAiSdk\Laravel\Providers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use PhpAiSdk\Laravel\Contracts\Provider;

class GroqProvider implements Provider
{
    protected string $apiKey;
    protected string $baseUrl;
    protected array $config;

    /**
     * Create a new Groq provider instance.
     *
     * @param  array  $config  The provider-specific configuration.
     *                         Requires 'api_key'. Optionally 'base_url'.
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config)
    {
        if (empty($config['api_key'])) {
            throw new \InvalidArgumentException(
                'Groq API key is not configured. Please set it in config/ai.php for the groq provider.'
            );
        }

        $this->apiKey = $config['api_key'];
        $this->baseUrl = $config['base_url'] ?? 'https://api.groq.com/openai/v1/';
        $this->config = $config;
    }

    /**
     * Get the HTTP client instance.
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function client(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
            ->baseUrl($this->baseUrl)
            ->timeout($this->config['timeout'] ?? 30) // Example: make timeout configurable
            ->retry(
                $this->config['retry_times'] ?? 3,
                $this->config['retry_interval_ms'] ?? 100
            ); // Example: make retries configurable
    }

    /**
     * Generate text from a given prompt.
     *
     * @param  string  $prompt
     * @param  array  $options  Must include 'model' (provider-specific model name).
     * @return string
     * @throws \Illuminate\Http\Client\RequestException|\RuntimeException
     */
    public function generateText(string $prompt, array $options = []): string
    {
        if (empty($options['model'])) {
            throw new \InvalidArgumentException(
                'Model name is required in options for GroqProvider.'
            );
        }
        $model = $options['model'];

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            ...$options,
        ];

        // Remove null values from payload to avoid sending them if not set
        $payload = array_filter($payload, fn($value) => !is_null($value));

        try {
            $response = $this->client()->post('chat/completions', $payload);

            $response->throw(); // Throw RequestException on 4xx or 5xx responses

            $data = $response->json();

            $content = Arr::get($data, 'choices.0.message.content');

            if (is_null($content)) {
                // Log the response for debugging if possible
                // Log::error('Groq API response missing content:', ['response_body' => $data]);
                throw new \RuntimeException(
                    'Groq API response is missing the expected text content. Response: ' .
                        json_encode($data)
                );
            }

            return $content;
        } catch (RequestException $e) {
            // You can log $e->response->body() for more details
            // Log::error('Groq API request failed:', ['error' => $e->getMessage(), 'response' => $e->response?->body()]);
            throw new RequestException(
                $e->response,
                'Groq API request failed: ' .
                    $e->getMessage() .
                    ' - Response: ' .
                    $e->response?->body(),
                $e->getCode(),
                $e
            );
        }
    }
}
