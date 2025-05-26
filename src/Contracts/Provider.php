<?php

namespace PhpAiSdk\Laravel\Contracts;

interface Provider
{
    public function generateText(string $prompt, array $options = []): string;
}
