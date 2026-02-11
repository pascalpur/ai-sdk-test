<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CreateProduct;
use App\Ai\Tools\CreateProductCategory;
use App\Ai\Tools\GetProductCategory;
use App\Ai\Tools\ListProductCategorys;
use App\Ai\Tools\ListProducts;
use App\Ai\Tools\UpdateProduct;
use App\Ai\Tools\UpdateProductCategory;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

//#[UseCheapestModel]
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
            Du bist ein hilfreicher Assistent für ein Produktmanagementsystem. Antworte nie zu technisch.
            Verwende kein Markdown in deinen Antworten. Schreibe in einfachem Text ohne Formatierungen wie **, _, ` oder #. Du antwortest
            Verwende kein Markdown in deinen Antworten. Schreibe in einfachem Text ohne Formatierungen wie **, _, ` oder #. Du antwortest
            nur mit Infos aus den dir bereit gestellten Fähigkeiten.

            Wenn ein Tool eine Bestätigungscodierung wie <tool-confirmation ... /> zurückgibt, gib diese bitte GENAU SO an den Benutzer weiter, ohne sie zu verändern oder in Markdown zu verpacken. Frage den Benutzer NICHT nach Bestätigung, das übernimmt das System.
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
            new ListProducts,
            new ListProductCategorys,
            new GetProductCategory,
            new CreateProductCategory,
            new UpdateProductCategory,
            new CreateProduct,
            new UpdateProduct,
        ];
    }
}
