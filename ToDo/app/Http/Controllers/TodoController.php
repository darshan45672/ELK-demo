<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TodoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::info('Fetching todos list', [
            'user_id' => Auth::id(),
            'filters' => $request->only(['status', 'priority']),
        ]);

        try {
            $query = Todo::query()->forUser(Auth::id());

            // Apply filters
            if ($request->filled('status')) {
                if ($request->status === 'completed') {
                    $query->completed();
                } elseif ($request->status === 'incomplete') {
                    $query->incomplete();
                }
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            $todos = $query->byPriority()->latest()->get();

            Log::info('Todos fetched successfully', [
                'user_id' => Auth::id(),
                'count' => $todos->count(),
            ]);

            return view('todos.index', compact('todos'));
        } catch (\Exception $e) {
            Log::error('Error fetching todos', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to load todos. Please try again.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Creating new todo', [
            'user_id' => Auth::id(),
            'data' => $request->only(['title', 'description', 'priority', 'due_date']),
        ]);

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'priority' => 'required|in:low,medium,high',
                'due_date' => 'nullable|date|after_or_equal:today',
            ]);

            $todo = Auth::user()->todos()->create($validated);

            Log::info('Todo created successfully', [
                'user_id' => Auth::id(),
                'todo_id' => $todo->id,
                'title' => $todo->title,
            ]);

            return back()->with('success', 'Todo created successfully!');
        } catch (ValidationException $e) {
            Log::warning('Todo validation failed', [
                'user_id' => Auth::id(),
                'errors' => $e->errors(),
                'data' => $request->all(),
            ]);

            throw $e;
        } catch (\Exception $e) {
            Log::error('Error creating todo', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to create todo. Please try again.')->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Todo $todo)
    {
        Log::info('Updating todo', [
            'user_id' => Auth::id(),
            'todo_id' => $todo->id,
            'data' => $request->only(['title', 'description', 'priority', 'due_date', 'completed']),
        ]);

        try {
            // Authorization check
            if ($todo->user_id !== Auth::id()) {
                Log::warning('Unauthorized todo update attempt', [
                    'user_id' => Auth::id(),
                    'todo_id' => $todo->id,
                    'owner_id' => $todo->user_id,
                ]);

                abort(403, 'You are not authorized to update this todo.');
            }

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'priority' => 'sometimes|required|in:low,medium,high',
                'due_date' => 'nullable|date',
                'completed' => 'sometimes|boolean',
            ]);

            $oldData = $todo->toArray();
            $todo->update($validated);

            Log::info('Todo updated successfully', [
                'user_id' => Auth::id(),
                'todo_id' => $todo->id,
                'changes' => $todo->getChanges(),
                'old_data' => $oldData,
            ]);

            return back()->with('success', 'Todo updated successfully!');
        } catch (ValidationException $e) {
            Log::warning('Todo update validation failed', [
                'user_id' => Auth::id(),
                'todo_id' => $todo->id,
                'errors' => $e->errors(),
            ]);

            throw $e;
        } catch (\Exception $e) {
            Log::error('Error updating todo', [
                'user_id' => Auth::id(),
                'todo_id' => $todo->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to update todo. Please try again.');
        }
    }

    /**
     * Toggle the completed status of the todo.
     */
    public function toggle(Todo $todo)
    {
        Log::info('Toggling todo completion status', [
            'user_id' => Auth::id(),
            'todo_id' => $todo->id,
            'current_status' => $todo->completed,
        ]);

        try {
            if ($todo->user_id !== Auth::id()) {
                Log::warning('Unauthorized todo toggle attempt', [
                    'user_id' => Auth::id(),
                    'todo_id' => $todo->id,
                    'owner_id' => $todo->user_id,
                ]);

                abort(403);
            }

            $todo->update(['completed' => !$todo->completed]);

            Log::info('Todo completion toggled', [
                'user_id' => Auth::id(),
                'todo_id' => $todo->id,
                'new_status' => $todo->completed,
            ]);

            return back()->with('success', 'Todo status updated!');
        } catch (\Exception $e) {
            Log::error('Error toggling todo', [
                'user_id' => Auth::id(),
                'todo_id' => $todo->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to update status.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Todo $todo)
    {
        Log::info('Deleting todo', [
            'user_id' => Auth::id(),
            'todo_id' => $todo->id,
            'title' => $todo->title,
        ]);

        try {
            if ($todo->user_id !== Auth::id()) {
                Log::warning('Unauthorized todo delete attempt', [
                    'user_id' => Auth::id(),
                    'todo_id' => $todo->id,
                    'owner_id' => $todo->user_id,
                ]);

                abort(403);
            }

            $todoData = $todo->toArray();
            $todo->delete();

            Log::info('Todo deleted successfully', [
                'user_id' => Auth::id(),
                'deleted_todo' => $todoData,
            ]);

            return back()->with('success', 'Todo deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting todo', [
                'user_id' => Auth::id(),
                'todo_id' => $todo->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to delete todo.');
        }
    }
}
