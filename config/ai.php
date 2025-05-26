<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the AI providers your application supports.
    | Each provider needs an 'api_key' (if applicable) and a 'driver_class'
    | which is the fully qualified name of the provider's class.
    |
    */
    'providers' => [
        // 'openai' => [
        //     'api_key' => env('OPENAI_API_KEY'),
        //     'driver_class' => \PhpAiSdk\Laravel\Providers\OpenAIProvider::class,
        // ],
        // 'google_ai' => [
        //     'api_key' => env('GOOGLE_AI_API_KEY'),
        //     'driver_class' => \PhpAiSdk\Laravel\Providers\GoogleAiProvider::class, // Assuming you create this
        // ],
        'groq' => [ // Renamed from groq_cloud for consistency, adjust if needed
            'api_key' => env('GROQ_API_KEY'),
            'driver_class' => \PhpAiSdk\Laravel\Providers\GroqProvider::class,
        ],
        // Example for a provider that might not need an API key
        // 'local_llm' => [
        //     'driver_class' => \PhpAiSdk\Laravel\Providers\LocalLlmProvider::class,
        //     'model_path' => '/path/to/your/local/model',
        // ],
    ],
];
