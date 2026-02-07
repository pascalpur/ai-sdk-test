<?php

use App\Services\PseudonymService;

if (!function_exists('pseudonymize')) {
    /**
     * Generate a pseudonym for the given value.
     *
     * @param  string  $value  The real value to pseudonymize
     * @return string The pseudonym in format <!uuid!>
     */
    function pseudonymize(string $value): string
    {
        return app(PseudonymService::class)->pseudonymize($value);
    }
}

if (!function_exists('resolve_pseudonyms')) {
    /**
     * Parse text and replace all pseudonyms with their real values.
     *
     * @param  string  $text  The text containing pseudonyms
     * @return string The text with pseudonyms replaced by real values
     */
    function resolve_pseudonyms(string $text): string
    {
        return app(PseudonymService::class)->parseResponse($text);
    }
}
