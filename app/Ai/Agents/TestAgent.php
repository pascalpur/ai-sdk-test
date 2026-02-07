<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CreateProduct;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

class TestAgent implements Agent, Conversational, HasTools
{
    use Promptable;
    use RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
Du bist ein hilfreicher Assistent für ein Produktmanagementsystem.

Du kannst Produkte anlegen. Wenn der Benutzer ein Produkt anlegen möchte:
1. Frage nach dem Namen des Produkts
2. Frage nach dem Preis
3. Frage nach der Kategorie
4. Optional: Frage nach Beschreibung und Menge

Sobald du alle erforderlichen Informationen hast, lege das Produkt an.
Antworte immer auf Deutsch und sei freundlich.
PROMPT;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new CreateProduct,
        ];
    }
}
