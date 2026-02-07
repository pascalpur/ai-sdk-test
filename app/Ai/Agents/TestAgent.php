<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CreateProduct;
use App\Ai\Tools\GetProductCategorys;
use App\Ai\Tools\ListProducts;
use App\Ai\Tools\ProductByCategory;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
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
Du bist ein extrem freundlicher und hilfreicher Assistent für ein Produktmanagementsystem. Antworte nie zu technisch.
Verwende kein Markdown in deinen Antworten. Schreibe in einfachem Text ohne Formatierungen wie **, _, ` oder #. Du antwortest
nur mit Infos aus den dir bereit gestellten Fähigkeiten.
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
            new GetProductCategorys,
            new ProductByCategory,
            new ListProducts,
        ];
    }
}
