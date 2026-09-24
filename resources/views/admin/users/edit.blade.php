@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')

    <div class="mx-auto max-w-2xl rounded-lg bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            @if($user->id === auth()->id())
                <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 p-3 text-sm text-blue-800">
                    You are editing <strong>your own</strong> account. For safety you cannot change your own role or deactivate yourself.
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <div class="md:col-span-2">
                    <label for="name" class="mb-1 block text-sm font-medium text-gray-700">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required autofocus
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="role_id" class="mb-1 block text-sm font-medium text-gray-700">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select name="role_id" id="role_id" required
                            @disabled($user->id === auth()->id())
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:bg-gray-100">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    @if($user->id === auth()->id())
                        {{-- Disabled selects don't submit, so re-submit the current value --}}
                        <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                    @endif
                    @error('role_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-gray-700">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" id="status" required
                            @disabled($user->id === auth()->id())
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:bg-gray-100">
                        <option value="active" @selected(old('status', $user->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $user->status) === 'inactive')>Inactive</option>
                    </select>
                    @if($user->id === auth()->id())
                        <input type="hidden" name="status" value="{{ $user->status }}">
                    @endif
                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-gray-700">
                        New Password
                    </label>
                    <input type="password" name="password" id="password"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Leave empty to keep the current password.</p>
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">
                        Confirm New Password
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

            </div>

            <div class="mt-6 flex items-center justify-between">
                <div class="text-xs text-gray-500">
                    Created: {{ $user->created_at->format('Y-m-d H:i') }}
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.users.index') }}"
                       class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Update User
                    </button>
                </div>
            </div>
        </form>
    </div>

@endsection