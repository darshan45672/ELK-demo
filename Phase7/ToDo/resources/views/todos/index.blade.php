@extends('layouts.app')

@section('title', 'My Todos')

@section('content')
<div class="max-w-4xl mx-auto px-4">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900">My Todos</h2>
        <p class="text-gray-600 mt-1">Manage your tasks efficiently</p>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Create Todo Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Add New Todo</h3>
        <form method="POST" action="{{ route('todos.store') }}">
            @csrf
            <div class="grid gap-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input 
                        type="text" 
                        name="title" 
                        id="title" 
                        value="{{ old('title') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror"
                        required
                    >
                    @error('title')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea 
                        name="description" 
                        id="description" 
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority *</label>
                        <select 
                            name="priority" 
                            id="priority"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>

                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <input 
                            type="date" 
                            name="due_date" 
                            id="due_date" 
                            value="{{ old('due_date') }}"
                            min="{{ date('Y-m-d') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Add Todo
                </button>
            </div>
        </form>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('todos.index') }}" class="flex gap-4">
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="incomplete" {{ request('status') == 'incomplete' ? 'selected' : '' }}>Incomplete</option>
            </select>

            <select name="priority" class="px-3 py-2 border border-gray-300 rounded-lg" onchange="this.form.submit()">
                <option value="">All Priorities</option>
                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
            </select>

            @if(request('status') || request('priority'))
                <a href="{{ route('todos.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-900">Clear Filters</a>
            @endif
        </form>
    </div>

    <!-- Todos List -->
    <div class="space-y-3">
        @forelse($todos as $todo)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start gap-4">
                    <!-- Toggle Complete -->
                    <form method="POST" action="{{ route('todos.toggle', $todo) }}" class="mt-1">
                        @csrf
                        <button 
                            type="submit"
                            class="w-5 h-5 rounded border-2 {{ $todo->completed ? 'bg-green-500 border-green-500' : 'border-gray-300' }} flex items-center justify-center hover:border-green-500 transition-colors"
                        >
                            @if($todo->completed)
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        </button>
                    </form>

                    <!-- Todo Content -->
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900 {{ $todo->completed ? 'line-through text-gray-500' : '' }}">
                            {{ $todo->title }}
                        </h4>
                        @if($todo->description)
                            <p class="text-gray-600 text-sm mt-1 {{ $todo->completed ? 'line-through' : '' }}">
                                {{ $todo->description }}
                            </p>
                        @endif
                        <div class="flex gap-3 mt-2 text-xs text-gray-500">
                            <span class="px-2 py-1 rounded-full {{ 
                                $todo->priority == 'high' ? 'bg-red-100 text-red-700' : 
                                ($todo->priority == 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') 
                            }}">
                                {{ ucfirst($todo->priority) }}
                            </span>
                            @if($todo->due_date)
                                <span class="px-2 py-1 rounded-full bg-gray-100">
                                    Due: {{ $todo->due_date->format('M d, Y') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('todos.destroy', $todo) }}" onsubmit="return confirm('Are you sure you want to delete this todo?')">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit"
                                class="text-red-600 hover:text-red-800 p-2"
                                title="Delete"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 p-12 text-center">
                <p class="text-gray-500">No todos found. Create your first todo above!</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
