# Implementierung des Tool-Bestätigungssystems

Dises Dokument beschreibt die implementierte Lösung für deterministische Tool-Bestätigungen mittels `laravel/ai` SDK.
Statt Prompts zu nutzen, setzen wir auf strukturierte Events.

## 1. Backend: Confirmable Trait

Dieser Trait wird in Tools verwendet, um die Bestätigung anzufordern. Er gibt ein strukturiertes JSON-Objekt zurück.

**Datei**: `app/Ai/Tools/Concerns/Confirmable.php`

```php
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
```

## 2. Frontend Logic: AiChatWidget

Die Komponente fängt das `ToolResult` Event ab. Wenn `confirmation_required` enthalten ist, wird der Stream gestoppt und die Bestätigungs-UI gerendert.

**Datei**: `app/Livewire/AiChatWidget.php` (relevanter Auszug)

```php
    #[On('fetch-ai-response')]
    public function fetchAiResponse(string $message): void
    {
        // ... (Agnet Setup) ...

        // Use streaming instead of prompt
        $response = $agent->stream($message);

        $isConfirmation = false;
        $confirmationTag = null;
        $suppressStreaming = false;

        foreach ($response as $event) {
            // Intercept ToolResult for deterministic confirmation
            if ($event instanceof \Laravel\Ai\Streaming\Events\ToolResult) {
                // ... (Check for confirmation) ...
                if ($isConfirmation) {
                     // ... Construct Tag ...
                    $this->stream(to: 'streamingContent', content: $confirmationTag);
                    
                    // WICHTIG: Nicht abbrechen (kein break), sondern Stream weiterlaufen lassen,
                    // damit das SDK die Conversation-History speichert!
                    $suppressStreaming = true; 
                }
            }

            if ($event instanceof \Laravel\Ai\Streaming\Events\TextDelta) {
                if (!$suppressStreaming) {
                    $this->stream(to: 'streamingContent', content: $event->delta);
                }
            }
        }

        // ...
        
        if ($isConfirmation && $confirmationTag) {
            $parsedResponse = $confirmationTag;
        } else {
            $parsedResponse = resolve_pseudonyms($response->text ?? '');
        }

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $parsedResponse,
        ];
        
        // ...
    }

    public function confirmTool(string $hash): void
    {
        session()->put('tool_confirmed_' . $hash, true);
        $this->updateMessageState($hash, 'confirmed');
        // ... trigger AI again ...
    }
```

## 3. Frontend View: Blade Template

Das Template parst die speziellen Tags, um die Buttons oder den Status anzuzeigen.

**Datei**: `resources/views/livewire/ai-chat-widget.blade.php` (Auszug)

```blade
@php
    $confirmation = null;
    $resolved = null;

    if ($message['role'] === 'assistant') {
        if (preg_match('/<tool-confirmation-resolved status="([^"]+)" ... \/>/', $message['content'], $matches)) {
            $resolved = [ ... ];
        } elseif (preg_match('/<tool-confirmation ... \/>/', $message['content'], $matches)) {
            $confirmation = [ ... ];
        }
    }
@endphp

@if ($resolved)
    <!-- Status Anzeige (Bestätigt/Abgebrochen) -->
    <div class="space-y-3 opacity-75">
        ...
    </div>
@elseif ($confirmation)
    <!-- Konfirmations-Karte mit Buttons -->
    <div class="space-y-3">
        <p>Soll das Tool <strong>{{ $confirmation['tool'] }}</strong> ausgeführt werden?</p>
        ...
        <button wire:click="cancelTool('{{ $confirmation['hash'] }}')">Abbrechen</button>
        <button wire:click="confirmTool('{{ $confirmation['hash'] }}')">Bestätigen</button>
    </div>
@else
    <!-- Standard Nachricht -->
    <p>{{ $message['content'] }}</p>
@endif
```
