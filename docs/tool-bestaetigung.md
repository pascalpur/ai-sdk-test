# Implementierung des Tool-Bestätigungssystems

Dieses Dokument beschreibt die technische Umsetzung des Bestätigungssystems, das verhindert, dass der AI-Agent kritische Aktionen (wie das Erstellen von Produkten) ohne explizite Benutzerzustimmung ausführt.

## Funktionsweise

Das System basiert auf einem **Session-basierten Freigabemechanismus** kombiniert mit **Frontend-Interception**.

1.  **Agent will Aktion ausführen**: Der Agent ruft ein Tool auf (z.B. `CreateProduct`).
2.  **Tool prüft Freigabe**: Das Tool prüft, ob für diese spezifische Anfrage (Hash der Parameter) bereits eine Freigabe in der Session existiert.
3.  **Abfang & Rückgabe**:
    *   **Nicht freigegeben**: Das Tool bricht ab und gibt einen speziellen XML-Tag zurück: `<tool-confirmation ... />`.
    *   **Freigegeben**: Das Tool führt die Aktion aus.
4.  **Frontend-Anzeige**: Der Chat-Widget erkennt den XML-Tag und rendert statt des Textes eine UI mit "Bestätigen" / "Abbrechen" Buttons.
5.  **Benutzer-Interaktion**:
    *   **Bestätigen**: Ein Session-Key wird gesetzt und der Agent wird erneut getriggert. Er ruft das Tool mit denselben Parametern auf -> diesmal erfolgreich, da Session-Key existiert.
    *   **Abbrechen**: Eine Abbruch-Nachricht wird gesendet.

## Implementierte Komponenten

### 1. `Confirmable` Trait
**Pfad**: `app/Ai/Tools/Concerns/Confirmable.php`
Dies ist das Herzstück. Tools können diesen Trait verwenden, um die Bestätigungslogik einzubinden.

```php
trait Confirmable {
    protected function checkConfirmation(Request $request): ?string {
        // Erstellt eindeutigen Hash aus Tool-Klasse + Parametern
        $hash = md5(static::class . serialize($request->all()));
        
        // Wenn in Session bestätigt -> Null zurückgeben (alles ok)
        if (Session::has('tool_confirmed_' . $hash)) {
            Session::forget($sessionKey); // Einmal-Token verbrauchen
            return null;
        }

        // Sonst: Strukturierte Anforderung zurückgeben
        return sprintf('<tool-confirmation hash="%s" ... />', ...);
    }
}
```

### 2. Livewire Komponente (`AiChatWidget`)
**Pfad**: `app/Livewire/AiChatWidget.php`
Verwaltet die Interaktion im Frontend.

*   `confirmTool($hash)`: Setzt den Session-Key (`tool_confirmed_...`), aktualisiert die UI auf "Bestätigt" und triggert den Agent erneut.
*   `cancelTool($hash)`: Aktualisiert die UI auf "Abgebrochen" und informiert den Agent.
*   `updateMessageState(...)`: Hilfsmethode, die den XML-Tag im Chatverlauf von `<tool-confirmation>` zu `<tool-confirmation-resolved>` ändert, um den Status (Grüner Haken / Rotes X) anzuzeigen.

### 3. Blade Template
**Pfad**: `resources/views/livewire/ai-chat-widget.blade.php`
Enthält die Logik zum Parsen und Anzeigen der Tags.

*   Parsen von `<tool-confirmation ... />`: Zeigt Buttons.
*   Parsen von `<tool-confirmation-resolved ... />`: Zeigt Status (Bestätigt/Abgebrochen) ohne Buttons.

### 4. Agent Instruktion (`TestAgent`)
**Pfad**: `app/Ai/Agents/TestAgent.php`
Der Agent wurde angewiesen, die `<tool-confirmation>` Tags unverändert durchzureichen, damit das Frontend sie verarbeiten kann.

## Erweiterbarkeit

Um ein weiteres Tool (z.B. `DeleteProduct`) abzusichern, müssen Sie nur:
1.  Den Trait einbinden: `use Concerns\Confirmable;`
2.  Am Anfang der `handle`-Methode prüfen:
    ```php
    if ($confirmation = $this->checkConfirmation($request)) {
        return $confirmation;
    }
    ```
