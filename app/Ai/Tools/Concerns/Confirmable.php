<?php

namespace App\Ai\Tools\Concerns;

use Illuminate\Support\Facades\Session;
use Laravel\Ai\Tools\Request;

trait Confirmable
{
    /**
     * Check if the tool execution is confirmed.
     */
    protected function checkConfirmation(Request $request): ?string
    {
        $hash = md5(static::class . serialize($request->all()));
        $sessionKey = 'tool_confirmed_' . $hash;

        if (Session::has($sessionKey)) {
            // Clear the confirmation after use so it can't be replayed indefinitely
            Session::forget($sessionKey);
            return null; // Confirmed, proceed
        }

        // Return a special structured string that the frontend can parse
        // We include the hash so the frontend knows what to confirm
        // We also include the tool name and params for display (optional)
        return sprintf(
            '<tool-confirmation hash="%s" tool="%s" params="%s" />',
            $hash,
            class_basename($this),
            e(json_encode($request->all()))
        );
    }
}
