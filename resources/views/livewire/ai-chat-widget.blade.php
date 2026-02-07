<div class="fixed bottom-6 right-6 z-50"
    x-data="{ scrollToBottom() { $nextTick(() => { const container = document.getElementById('chat-messages'); if (container) container.scrollTop = container.scrollHeight; }) } }"
    x-on:chat-updated.window="scrollToBottom()">
    {{-- Chat Toggle Button --}}
    <button wire:click="toggleChat"
        class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg transition-all duration-300 hover:scale-110 hover:shadow-xl"
        aria-label="Chat öffnen">
        @if ($isOpen)
            <flux:icon.x-mark class="h-6 w-6" />
        @else
            <flux:icon.chat-bubble-left-right class="h-6 w-6" />
        @endif
    </button>

    {{-- Chat Panel --}}
    @if ($isOpen)
        <div class="absolute bottom-20 right-0 flex w-96 flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900"
            style="height: 500px;" x-init="scrollToBottom()">
            {{-- Header --}}
            <div
                class="flex items-center justify-between border-b border-zinc-200 bg-gradient-to-r from-indigo-500 to-purple-600 px-4 py-3 dark:border-zinc-700">
                <div class="flex items-center gap-2">
                    <flux:icon.sparkles class="h-5 w-5 text-white" />
                    <span class="font-semibold text-white">AI Assistant</span>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="clearChat"
                        class="rounded-lg p-1.5 text-white/80 transition-colors hover:bg-white/20 hover:text-white"
                        title="Chat löschen">
                        <flux:icon.trash class="h-4 w-4" />
                    </button>
                    <button wire:click="toggleChat"
                        class="rounded-lg p-1.5 text-white/80 transition-colors hover:bg-white/20 hover:text-white"
                        title="Schließen">
                        <flux:icon.x-mark class="h-4 w-4" />
                    </button>
                </div>
            </div>

            {{-- Messages Container --}}
            <div id="chat-messages" class="flex-1 space-y-4 overflow-y-auto p-4" wire:ignore.self>
                @forelse ($messages as $message)
                    <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div
                            class="max-w-[80%] rounded-2xl px-4 py-2 {{ $message['role'] === 'user' ? 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white' : 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200' }}">
                            <p class="whitespace-pre-wrap text-sm">{{ $message['content'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="flex h-full flex-col items-center justify-center text-center">
                        <flux:icon.chat-bubble-left-right class="mb-3 h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Starte eine Unterhaltung!
                        </p>
                        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                            Ich bin dein AI-Assistent.
                        </p>
                    </div>
                @endforelse

                {{-- Loading indicator --}}
                @if ($isLoading)
                    <div class="flex justify-start">
                        <div class="rounded-2xl bg-zinc-100 px-4 py-3 dark:bg-zinc-800">
                            <div class="flex items-center gap-1">
                                <div class="h-2 w-2 animate-bounce rounded-full bg-zinc-400" style="animation-delay: 0ms;">
                                </div>
                                <div class="h-2 w-2 animate-bounce rounded-full bg-zinc-400" style="animation-delay: 150ms;">
                                </div>
                                <div class="h-2 w-2 animate-bounce rounded-full bg-zinc-400" style="animation-delay: 300ms;">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Input Area --}}
            <div class="border-t border-zinc-200 p-4 dark:border-zinc-700">
                <form wire:submit="sendMessage" class="flex gap-2">
                    <input type="text" wire:model="newMessage" placeholder="Nachricht eingeben..."
                        class="flex-1 rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
                        {{ $isLoading ? 'disabled' : '' }} />
                    <button type="submit"
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white transition-all hover:scale-105 hover:shadow-lg disabled:opacity-50 disabled:hover:scale-100"
                        {{ $isLoading ? 'disabled' : '' }}>
                        <flux:icon.paper-airplane class="h-5 w-5" />
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>