'use client';

import { useState } from 'react';
import { Todo } from '@/lib/types';
import { toggleTodo, deleteTodo, updateTodo } from '@/app/actions';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Trash2, Edit2, Check, X } from 'lucide-react';

interface TodoItemProps {
  todo: Todo;
}

export function TodoItem({ todo }: TodoItemProps) {
  const [isEditing, setIsEditing] = useState(false);
  const [title, setTitle] = useState(todo.title);
  const [description, setDescription] = useState(todo.description || '');
  const [priority, setPriority] = useState<'low' | 'medium' | 'high'>(todo.priority);

  async function handleToggle() {
    await toggleTodo(todo._id!.toString(), !todo.completed);
  }

  async function handleDelete() {
    if (confirm('Are you sure you want to delete this todo?')) {
      await deleteTodo(todo._id!.toString());
    }
  }

  async function handleUpdate() {
    await updateTodo(todo._id!.toString(), {
      title: title.trim(),
      description: description.trim() || undefined,
      priority,
    });
    setIsEditing(false);
  }

  function handleCancel() {
    setTitle(todo.title);
    setDescription(todo.description || '');
    setPriority(todo.priority);
    setIsEditing(false);
  }

  const priorityColors = {
    low: 'text-green-600 dark:text-green-400',
    medium: 'text-yellow-600 dark:text-yellow-400',
    high: 'text-red-600 dark:text-red-400',
  };

  return (
    <Card className={todo.completed ? 'opacity-60' : ''}>
      <CardContent className="p-4">
        {isEditing ? (
          <div className="space-y-3">
            <Input
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder="Title"
            />
            <Input
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              placeholder="Description"
            />
            <select
              value={priority}
              onChange={(e) => setPriority(e.target.value as 'low' | 'medium' | 'high')}
              className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
            >
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
            <div className="flex gap-2">
              <Button onClick={handleUpdate} size="sm" variant="default">
                <Check className="h-4 w-4 mr-1" />
                Save
              </Button>
              <Button onClick={handleCancel} size="sm" variant="outline">
                <X className="h-4 w-4 mr-1" />
                Cancel
              </Button>
            </div>
          </div>
        ) : (
          <div className="flex items-start gap-3">
            <Checkbox
              checked={todo.completed}
              onCheckedChange={handleToggle}
              className="mt-1"
            />
            <div className="flex-1 space-y-1">
              <h3
                className={`font-medium ${
                  todo.completed ? 'line-through text-muted-foreground' : ''
                }`}
              >
                {todo.title}
              </h3>
              {todo.description && (
                <p
                  className={`text-sm ${
                    todo.completed ? 'line-through text-muted-foreground' : 'text-muted-foreground'
                  }`}
                >
                  {todo.description}
                </p>
              )}
              <div className="flex items-center gap-2 text-xs">
                <span className={`font-semibold ${priorityColors[todo.priority]}`}>
                  {todo.priority.toUpperCase()}
                </span>
                <span className="text-muted-foreground">
                  {new Date(todo.createdAt).toLocaleDateString()}
                </span>
              </div>
            </div>
            <div className="flex gap-2">
              <Button
                onClick={() => setIsEditing(true)}
                size="icon"
                variant="ghost"
                className="h-8 w-8"
              >
                <Edit2 className="h-4 w-4" />
              </Button>
              <Button
                onClick={handleDelete}
                size="icon"
                variant="ghost"
                className="h-8 w-8 text-destructive hover:text-destructive"
              >
                <Trash2 className="h-4 w-4" />
              </Button>
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
