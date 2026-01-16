import { Todo } from '@/lib/types';
import { TodoItem } from './todo-item';

interface TodoListProps {
  todos: Todo[];
}

export function TodoList({ todos }: TodoListProps) {
  if (todos.length === 0) {
    return (
      <div className="text-center py-12 text-muted-foreground">
        <p className="text-lg font-medium">No todos yet</p>
        <p className="text-sm">Create your first todo to get started!</p>
      </div>
    );
  }

  const completedCount = todos.filter((t) => t.completed).length;
  const totalCount = todos.length;

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between text-sm text-muted-foreground">
        <span>
          {completedCount} of {totalCount} completed
        </span>
        <span>{totalCount - completedCount} remaining</span>
      </div>
      <div className="space-y-3">
        {todos.map((todo) => (
          <TodoItem key={todo._id!.toString()} todo={todo} />
        ))}
      </div>
    </div>
  );
}
