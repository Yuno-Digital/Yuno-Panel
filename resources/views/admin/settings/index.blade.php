<x-admin title="Settings">
    <h3 class="mb-4 text-lg font-medium text-gray-900">{{ __('Settings') }}</h3>

    <div class="bg-white shadow-sm sm:rounded-lg p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <x-input-label for="panel_name" :value="__('Panel name')" />
                    <x-text-input id="panel_name" name="panel_name" type="text" class="mt-1 block w-full"
                                  :value="old('panel_name', $settings['panel_name'])" required />
                    <x-input-error :messages="$errors->get('panel_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="support_url" :value="__('Support URL')" />
                    <x-text-input id="support_url" name="support_url" type="url" class="mt-1 block w-full"
                                  :value="old('support_url', $settings['support_url'])" placeholder="https://discord.gg/..." />
                    <x-input-error :messages="$errors->get('support_url')" class="mt-2" />
                </div>

                <label class="inline-flex items-center">
                    <input type="hidden" name="allow_registration" value="0">
                    <input type="checkbox" name="allow_registration" value="1"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                           @checked(old('allow_registration', $settings['allow_registration']) === '1')>
                    <span class="ms-2 text-sm text-gray-600">{{ __('Allow public registration') }}</span>
                </label>

                <div>
                    <x-primary-button>{{ __('Save settings') }}</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</x-admin>
