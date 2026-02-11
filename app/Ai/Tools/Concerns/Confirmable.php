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
        $params = $request->all();
        ksort($params);
        $hash = md5(static::class . serialize($params));
        $sessionKey = 'tool_confirmed_' . $hash;



        if (Session::has($sessionKey)) {
            // Clear the confirmation after use so it can't be replayed indefinitely
            Session::forget($sessionKey);
            return null; // Confirmed, proceed
        }

        // Return a structured JSON string that the frontend can intercept via ToolResult event
        return json_encode([
            'confirmation_required' => true,
            'hash' => $hash,
            'tool' => class_basename($this),
            'params' => $request->all(),
            'instruction' => 'SYSTEM: STOP. User approval required.',
        ]);
    }
}
