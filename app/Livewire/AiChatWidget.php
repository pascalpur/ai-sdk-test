<?php

namespace App\Livewire;

use App\Ai\Agents\TestAgent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class AiChatWidget extends Component
{
    public bool $isOpen = false;

    public string $newMessage = '';

    public array $messages = [];

    public ?string $conversationId = null;

    public bool $isLoading = false;

    public function mount(): void
    {
        $this->conversationId = session('chat_conversation_id');
        $this->messages = session('chat_messages', []);
    }

    public function toggleChat(): void
    {
        $this->isOpen = !$this->isOpen;
    }

    public function sendMessage(): void
    {
        $message = trim($this->newMessage);

        if (empty($message)) {
            return;
        }

        // Add user message immediately
        $this->messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        // Clear input and show loading state
        $this->newMessage = '';
        $this->isLoading = true;

        // Persist current state
        session(['chat_messages' => $this->messages]);

        // Dispatch event to scroll down
        $this->dispatch('chat-updated');

        // Dispatch event to fetch AI response (will be processed after this render)
        $this->dispatch('fetch-ai-response', message: $message);
    }

    #[On('fetch-ai-response')]
    public function fetchAiResponse(string $message): void
    {
        try {
            $agent = TestAgent::make();

            // Continue existing conversation or start new one
            if ($this->conversationId) {
                $agent->continue($this->conversationId, Auth::user());
            } else {
                $agent->forUser(Auth::user());
            }

            $response = $agent->prompt($message);

            // Store conversation ID for future messages
            if (!$this->conversationId) {
                $this->conversationId = $agent->currentConversation();
                session(['chat_conversation_id' => $this->conversationId]);
            }

            Log::info(print_r($response, true));
            // Parse response to replace pseudonyms with real values
            $parsedResponse = resolve_pseudonyms((string) $response);

            // Add AI response to the list
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $parsedResponse,
            ];
        } catch (\Exception $e) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => 'Entschuldigung, es ist ein Fehler aufgetreten: ' . $e->getMessage(),
            ];
        }

        // Persist messages in session
        session(['chat_messages' => $this->messages]);

        $this->isLoading = false;

        $this->dispatch('chat-updated');
    }

    public function clearChat(): void
    {
        $this->messages = [];
        $this->conversationId = null;
        $this->isLoading = false;
        session()->forget(['chat_conversation_id', 'chat_messages']);
    }

    public function render()
    {
        return view('livewire.ai-chat-widget');
    }
}

