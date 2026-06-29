@csrf
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $egg->name)" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="author" :value="__('Author')" />
            <x-text-input id="author" name="author" type="text" class="mt-1 block w-full"
                          :value="old('author', $egg->author)" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('author')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="docker_image" :value="__('Docker image')" />
        <x-text-input id="docker_image" name="docker_image" type="text" class="mt-1 block w-full font-mono text-sm"
                      :value="old('docker_image', $egg->docker_image)" placeholder="ghcr.io/pelican-eggs/yolks:java_21" required />
        <x-input-error :messages="$errors->get('docker_image')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="startup" :value="__('Startup command')" />
        <textarea id="startup" name="startup" rows="3"
                  class="mt-1 block w-full font-mono text-sm border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                  placeholder="java -Xms128M -Xmx@{{SERVER_MEMORY}}M -jar server.jar" required>{{ old('startup', $egg->startup) }}</textarea>
        <x-input-error :messages="$errors->get('startup')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $egg->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save') }}</x-primary-button>
        <a href="{{ route('admin.eggs.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
    </div>
</div>
