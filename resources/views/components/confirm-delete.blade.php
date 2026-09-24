@props([
    'id' => 'confirmDelete',
    'title' => 'Delete Item',
    'message' => 'Are you sure? This action cannot be undone.',
    'action' => '',      // The form action (optional — can be set via JS)
    'method' => 'DELETE', // HTTP method
])

<div
    id="{{ $id }}"
    x-data="{ open: false, action: '{{ $action }}' }"
    x-init="
        window.addEventListener('open-delete-modal', e => {
            action = e.detail.action;
            open = true;
        });
    "
    x-show="open"
    @keydown.escape.window="open = false"
    @click.self="open = false"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    style="display: none;"
>
    <div
        x-show="open"
        x-transition
        @click.outside="open = false"
        class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl"
    >
        <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
        <p class="mt-2 text-sm text-gray-600">{{ $message }}</p>

        <form :action="action" method="POST" class="mt-6 flex items-center justify-end gap-3">
            @csrf
            @method($method)

            <button type="button" @click="open = false"
                    class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button type="submit"
                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                Yes, Delete
            </button>
        </form>
    </div>
</div>