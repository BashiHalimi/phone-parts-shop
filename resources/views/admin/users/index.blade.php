@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')

    <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.users.index') }}"
              class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div class="md:col-span-2">
                <input type="text" name="search" value="{{ $search ?? '' }}"
                       placeholder="Search name, email, or phone..."
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div>
                <select name="role_id"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected((string)$roleId === (string)$role->id)>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All Status</option>
                    <option value="active"   @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="md:col-span-4 flex items-center justify-between gap-3">
                <div class="flex gap-2">
                    <button type="submit"
                            class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                       class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
                <a href="{{ route('admin.users.create') }}"
                   class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    + Add User
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">#</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Email</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Phone</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Role</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $user->id }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-800">{{ $user->name }}</span>
                                @if($user->id === auth()->id())
                                    <span class="ml-2 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-medium uppercase text-blue-700">You</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->phone ?: '—' }}</td>
                            <td class="px-4 py-3">
                                @if($user->role)
                                    @php
                                        $roleColors = [
                                            'admin'    => 'bg-purple-100 text-purple-700',
                                            'staff'    => 'bg-indigo-100 text-indigo-700',
                                            'customer' => 'bg-gray-100 text-gray-700',
                                        ];
                                    @endphp
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $roleColors[$user->role->name] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst($user->role->name) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($user->status === 'active')
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Active</span>
                                @else
                                    <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-700">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</a>

                                @if($user->id !== auth()->id())
                                    <button type="button"
                                            class="ml-3 text-sm font-medium text-red-600 hover:text-red-800"
                                            onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                        Delete
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-800">Delete User</h3>
            <p class="mt-2 text-sm text-gray-600">
                Delete <strong id="deleteUserName" class="text-gray-800"></strong>?
                This action cannot be undone. Users with activity history cannot be deleted — deactivate them instead.
            </p>
            <form id="deleteForm" method="POST" action="" class="mt-6 flex items-center justify-end gap-3">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteModal()"
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

    <script>
        function openDeleteModal(id, name) {
            document.getElementById('deleteForm').action = '/admin/users/' + id;
            document.getElementById('deleteUserName').textContent = name;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }
        document.getElementById('deleteModal')?.addEventListener('click', function (e) {
            if (e.target === this) closeDeleteModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDeleteModal();
        });
    </script>

@endsection