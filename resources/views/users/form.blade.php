@extends('layouts.app')

@section('title', ($user ? 'Edit User' : 'Create User') . ' - Base CRM')
@section('page-title', $user ? 'Edit User' : 'Create User')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="POST" action="{{ $user ? route('users.update', $user) : route('users.store') }}">
                @csrf
                @if($user)
                    @method('PUT')
                @endif

                @if($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name"
                           value="{{ old('name', $user ? $user->name : '') }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email"
                           value="{{ old('email', $user ? $user->email : '') }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Phone -->
                <div class="mb-6">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number
                    </label>
                    <input type="text" 
                           name="phone" 
                           id="phone"
                           value="{{ old('phone', $user ? $user->phone : '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password @if(!$user)<span class="text-red-500">*</span>@else<span class="text-gray-400">(leave blank to keep current)</span>@endif
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password"
                           @if(!$user) required @endif
                           minlength="8"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Role -->
                <div class="mb-6">
                    <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select name="role_id" 
                            id="role_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" 
                                    {{ old('role_id', $user ? $user->role_id : '') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Manager -->
                <div class="mb-6">
                    <label for="manager_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Manager (Optional)
                    </label>
                    <select name="manager_id" 
                            id="manager_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">No Manager</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" 
                                    {{ old('manager_id', $user ? $user->manager_id : '') == $manager->id ? 'selected' : '' }}>
                                {{ $manager->name }} ({{ $manager->role->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $user ? $user->is_active : true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Active User</span>
                    </label>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('users.index') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200 font-medium">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 font-medium">
                        {{ $user ? 'Update User' : 'Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

