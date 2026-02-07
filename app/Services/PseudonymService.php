<?php

namespace App\Services;

use App\Models\Pseudonym;
use Illuminate\Support\Str;

class PseudonymService
{
    /**
     * The pattern to match pseudonyms in text.
     */
    protected const PATTERN = '/<!([a-f0-9\-]{36})!>/';

    /**
     * Generate a pseudonym for the given value and store it in the database.
     */
    public function pseudonymize(string $value): string
    {
        $uuid = Str::uuid()->toString();

        Pseudonym::create([
            'pseudonym' => $uuid,
            'real_value' => $value,
        ]);

        return "<!{$uuid}!>";
    }

    /**
     * Resolve a single pseudonym to its real value.
     *
     * @param  string  $pseudonym  The pseudonym with or without delimiters
     */
    public function resolve(string $pseudonym): ?string
    {
        // Remove delimiters if present
        $uuid = preg_replace('/^<!|!>$/', '', $pseudonym);

        $record = Pseudonym::where('pseudonym', $uuid)->first();

        return $record?->real_value;
    }

    /**
     * Parse a text response and replace all pseudonyms with their real values.
     */
    public function parseResponse(string $text): string
    {
        return preg_replace_callback(self::PATTERN, function ($matches) {
            $uuid = $matches[1];
            $record = Pseudonym::where('pseudonym', $uuid)->first();

            // If no record found, keep the original pseudonym
            return $record?->real_value ?? $matches[0];
        }, $text);
    }

    /**
     * Delete all expired pseudonyms (older than 30 minutes).
     *
     * @return int The number of deleted records
     */
    public function cleanup(): int
    {
        return Pseudonym::expired()->delete();
    }
}
