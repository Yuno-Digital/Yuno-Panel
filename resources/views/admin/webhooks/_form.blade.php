@csrf
<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $webhook->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="url" :value="__('Payload URL')" />
        <x-text-input id="url" name="url" type="url" class="mt-1 block w-full font-mono text-sm"
                      :value="old('url', $webhook->url)" placeholder="https://example.com/webhook" required />
        <x-input-error :messages="$errors->get('url')" class="mt-2" />
    </div>

    <div>
        <x-input-label :value="__('Events')" />
        <p class="mt-1 mb-2 text-xs text-gray-500 dark:text-gray-400">{{ __('The panel POSTs a signed JSON payload to the URL for the selected events.') }}</p>
        <div class="space-y-2">
            @foreach (\App\Models\Webhook::EVENTS as $key => $label)
                <label class="flex items-start gap-2">
                    <input type="checkbox" name="events[]" value="{{ $key }}"
                           class="mt-0.5 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-indigo-600 dark:checked:bg-indigo-500 focus:ring-indigo-500"
                           @checked(in_array($key, old('events', $webhook->events ?? []), true))>
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        <span class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ $key }}</span>
                        — {{ __($label) }}
                    </span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('events')" class="mt-2" />
    </div>

    <label class="inline-flex items-center">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1"
               class="rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-indigo-600 dark:checked:bg-indigo-500 focus:ring-indigo-500"
               @checked(old('is_active', $webhook->is_active))>
        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Active') }}</span>
    </label>

    @if ($webhook->exists)
        <div>
            <x-input-label :value="__('Signing secret')" />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Sent as the') }} <code>X-Yuno-Signature: sha256=…</code> {{ __('header (HMAC of the body).') }}</p>
            <code class="mt-1 block font-mono text-xs break-all text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-900 rounded p-2">{{ $webhook->secret }}</code>
        </div>
    @endif

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save') }}</x-primary-button>
        <a href="{{ route('admin.webhooks.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
    </div>
</div>
