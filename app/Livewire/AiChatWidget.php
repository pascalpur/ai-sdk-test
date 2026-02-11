<?php

namespace App\Livewire;

use App\Ai\Agents\TestAgent;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class AiChatWidget extends Component
{
    public bool $isOpen = false;

    public string $newMessage = '';

    public array $messages = [];

    public ?string $conversationId = null;

    public bool $isLoading = false;

    public string $streamingContent = '';

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

            // Use streaming instead of prompt
            $response = $agent->stream($message);

            $buffer = '';
            $isBuffering = true;
            $isConfirmation = false;
            $confirmationTag = null;
            $suppressStreaming = false;

            foreach ($response as $event) {
                // Intercept ToolResult for deterministic confirmation
                if ($event instanceof \Laravel\Ai\Streaming\Events\ToolResult) {
                    $result = $event->toolResult->result;

                    // Attempt to decode JSON result
                    if (str_starts_with($result, '{')) {
                        $data = json_decode($result, true);
                        if (json_last_error() === JSON_ERROR_NONE && ($data['confirmation_required'] ?? false)) {
                            $isConfirmation = true;
                            // We found the confirmation.
                            // We suppress all further text streaming effectively for user output
                            // And we manually inject the XML tag for the view to render.
                            $confirmationTag = sprintf(
                                '<tool-confirmation hash="%s" tool="%s" params="%s" />',
                                $data['hash'],
                                $data['tool'],
                                e(json_encode($data['params']))
                            );

                            $this->stream(to: 'streamingContent', content: $confirmationTag);
                            $suppressStreaming = true; // Stop showing output, but CONTINUE the loop to verify SDK saves history!
                        }
                    }
                }

                if ($event instanceof \Laravel\Ai\Streaming\Events\TextDelta) {
                    if (!$suppressStreaming) {
                        // Stream content normally
                        $this->stream(to: 'streamingContent', content: $event->delta);
                    }
                }
            }

            // Store conversation ID for future messages
            if (!$this->conversationId) {
                $this->conversationId = $agent->currentConversation();
                session(['chat_conversation_id' => $this->conversationId]);
            }

            if ($isConfirmation && $confirmationTag) {
                // If confirmed, the message content is the tag itself (so it renders buttons on reload)
                $parsedResponse = $confirmationTag;
            } else {
                // Parse response to replace pseudonyms with real values
                $parsedResponse = resolve_pseudonyms($response->text ?? '');
            }

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
        $this->streamingContent = '';

        $this->dispatch('chat-updated');
    }

    public function clearChat(): void
    {
        $this->messages = [];
        $this->conversationId = null;
        $this->isLoading = false;
        session()->forget(['chat_conversation_id', 'chat_messages']);
    }

    public function confirmTool(string $hash): void
    {
        session()->put('tool_confirmed_' . $hash, true);

        $this->updateMessageState($hash, 'confirmed');

        $message = 'Aktion bestätigt. Bitte fortfahren.';

        // Add user message indicating confirmation
        $this->messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        session(['chat_messages' => $this->messages]);
        $this->dispatch('chat-updated');

        // Trigger AI response with the confirmation message
        $this->dispatch('fetch-ai-response', message: $message);
    }

    public function cancelTool(?string $hash = null): void
    {
        if ($hash) {
            $this->updateMessageState($hash, 'cancelled');
        }

        $message = 'Aktion abgebrochen. Bitte nicht fortfahren.';

        $this->messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        session(['chat_messages' => $this->messages]);
        $this->dispatch('chat-updated');

        // Trigger AI response to acknowledge cancellation
        $this->dispatch('fetch-ai-response', message: $message);
    }

    protected function updateMessageState(string $hash, string $status): void
    {
        foreach ($this->messages as $key => $message) {
            if ($message['role'] === 'assistant' && str_contains($message['content'], 'hash="' . $hash . '"')) {
                $this->messages[$key]['content'] = preg_replace(
                    '/<tool-confirmation (.*?) \/>/',
                    '<tool-confirmation-resolved status="' . $status . '" $1 />',
                    $message['content']
                );
            }
        }
    }

    public function render()
    {
        return view('livewire.ai-chat-widget');
    }
}
