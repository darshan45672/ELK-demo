import { getTodos } from './actions';
import { AddTodoForm } from '@/components/add-todo-form';
import { TodoList } from '@/components/todo-list';

// Force dynamic rendering - don't try to build this at build time
export const dynamic = 'force-dynamic';
export const revalidate = 0;

export default async function Home() {
  const todos = await getTodos();

  return (
    <div className="min-h-screen bg-gradient-to-b from-background to-muted/20">
      <div className="container max-w-4xl mx-auto p-6 py-12">
        <div className="space-y-8">
          {/* Header */}
          <div className="text-center space-y-2">
            <h1 className="text-4xl font-bold tracking-tight">Todo App</h1>
            <p className="text-muted-foreground">
              Manage your tasks with MongoDB & Next.js
            </p>
          </div>

          {/* Add Todo Form */}
          <div className="max-w-2xl mx-auto">
            <AddTodoForm />
          </div>

          {/* Todo List */}
          <div className="space-y-4">
            <h2 className="text-2xl font-semibold">Your Todos</h2>
            <TodoList todos={todos} />
          </div>
        </div>
      </div>
    </div>
  );
}
