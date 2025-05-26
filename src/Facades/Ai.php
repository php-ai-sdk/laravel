<?php

namespace PhpAiSdk\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use BadMethodCallException;

/**
 * @method static string generateText(string $prompt, array $options = [])
 * @method static \PhpAiSdk\Laravel\Contracts\Provider driver(string|null $driver = null)
 *
 * Dynamically you want to be able to do:
 *   Ai::groq('model-name')
 *   Ai::openai('model-name')
 */
class Ai extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \PhpAiSdk\Laravel\AiManager::class;
    }

    /**
     * Handle dynamic static calls to Ai::{$provider}('model-name')
     *
     * @param  string  $method     The provider key in camelCase or snake_case
     * @param  array   $arguments  [0] => the model name
     * @return array               ['provider'=>'xxx','name'=>'yyy']
     *
     * @throws BadMethodCallException    if the provider isn’t configured
     * @throws InvalidArgumentException  if no model name was given
     */
    public static function __callStatic($method, $arguments)
    {
        // normalize: "googleAi" → "google_ai", "groq" → "groq", etc.
        $providerKey = Str::snake($method);

        $providers = Config::get('ai.providers', []);

        if (array_key_exists($providerKey, $providers)) {
            $modelName = $arguments[0] ?? null;

            if (! $modelName || ! is_string($modelName)) {
                throw new InvalidArgumentException(
                    "Model name must be provided when calling Ai::{$method}('model-name')."
                );
            }

            return ['provider' => $providerKey, 'name' => $modelName];
        }

        // otherwise defer to the normal facade for generateText(), driver(), etc.
        return parent::__callStatic($method, $arguments);
    }
}
