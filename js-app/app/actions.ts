'use server';

import { revalidatePath } from 'next/cache';
import { getDatabase } from '@/lib/mongodb';
import { logger } from '@/lib/logger';
import { CreateTodoInput, UpdateTodoInput, Todo } from '@/lib/types';
import { ObjectId } from 'mongodb';

const COLLECTION_NAME = 'todos';

export async function getTodos(): Promise<Todo[]> {
  try {
    logger.info('Fetching todos list', { action: 'fetch_todos' });
    
    const db = await getDatabase();
    const todos = await db
      .collection<Todo>(COLLECTION_NAME)
      .find({})
      .sort({ createdAt: -1 })
      .toArray();

    logger.info('Todos fetched successfully', {
      action: 'fetch_todos',
      count: todos.length,
    });

    return JSON.parse(JSON.stringify(todos));
  } catch (error) {
    logger.error('Error fetching todos', {
      action: 'fetch_todos',
      error: error instanceof Error ? error.message : 'Unknown error',
    });
    throw error;
  }
}

export async function createTodo(input: CreateTodoInput) {
  try {
    logger.info('Creating new todo', {
      action: 'create_todo',
      title: input.title,
      priority: input.priority,
    });

    const db = await getDatabase();
    const todo: Omit<Todo, '_id'> = {
      ...input,
      completed: input.completed ?? false,
      createdAt: new Date(),
      updatedAt: new Date(),
    };

    const result = await db.collection<Todo>(COLLECTION_NAME).insertOne(todo as Todo);

    logger.info('Todo created successfully', {
      action: 'create_todo',
      todo_id: result.insertedId.toString(),
      title: input.title,
    });

    revalidatePath('/');
    return { success: true, id: result.insertedId.toString() };
  } catch (error) {
    logger.error('Error creating todo', {
      action: 'create_todo',
      error: error instanceof Error ? error.message : 'Unknown error',
      title: input.title,
    });
    return { success: false, error: 'Failed to create todo' };
  }
}

export async function updateTodo(id: string, input: UpdateTodoInput) {
  try {
    logger.info('Updating todo', {
      action: 'update_todo',
      todo_id: id,
      updates: Object.keys(input),
    });

    const db = await getDatabase();
    const result = await db.collection<Todo>(COLLECTION_NAME).updateOne(
      { _id: new ObjectId(id) },
      {
        $set: {
          ...input,
          updatedAt: new Date(),
        },
      }
    );

    if (result.matchedCount === 0) {
      logger.warn('Todo not found for update', {
        action: 'update_todo',
        todo_id: id,
      });
      return { success: false, error: 'Todo not found' };
    }

    logger.info('Todo updated successfully', {
      action: 'update_todo',
      todo_id: id,
      modified: result.modifiedCount,
    });

    revalidatePath('/');
    return { success: true };
  } catch (error) {
    logger.error('Error updating todo', {
      action: 'update_todo',
      todo_id: id,
      error: error instanceof Error ? error.message : 'Unknown error',
    });
    return { success: false, error: 'Failed to update todo' };
  }
}

export async function toggleTodo(id: string, completed: boolean) {
  try {
    logger.info('Toggling todo completion', {
      action: 'toggle_todo',
      todo_id: id,
      completed,
    });

    const db = await getDatabase();
    const result = await db.collection<Todo>(COLLECTION_NAME).updateOne(
      { _id: new ObjectId(id) },
      {
        $set: {
          completed,
          updatedAt: new Date(),
        },
      }
    );

    if (result.matchedCount === 0) {
      logger.warn('Todo not found for toggle', {
        action: 'toggle_todo',
        todo_id: id,
      });
      return { success: false, error: 'Todo not found' };
    }

    logger.info('Todo completion toggled', {
      action: 'toggle_todo',
      todo_id: id,
      completed,
    });

    revalidatePath('/');
    return { success: true };
  } catch (error) {
    logger.error('Error toggling todo', {
      action: 'toggle_todo',
      todo_id: id,
      error: error instanceof Error ? error.message : 'Unknown error',
    });
    return { success: false, error: 'Failed to toggle todo' };
  }
}

export async function deleteTodo(id: string) {
  try {
    logger.info('Deleting todo', {
      action: 'delete_todo',
      todo_id: id,
    });

    const db = await getDatabase();
    const result = await db.collection<Todo>(COLLECTION_NAME).deleteOne({
      _id: new ObjectId(id),
    });

    if (result.deletedCount === 0) {
      logger.warn('Todo not found for deletion', {
        action: 'delete_todo',
        todo_id: id,
      });
      return { success: false, error: 'Todo not found' };
    }

    logger.info('Todo deleted successfully', {
      action: 'delete_todo',
      todo_id: id,
    });

    revalidatePath('/');
    return { success: true };
  } catch (error) {
    logger.error('Error deleting todo', {
      action: 'delete_todo',
      todo_id: id,
      error: error instanceof Error ? error.message : 'Unknown error',
    });
    return { success: false, error: 'Failed to delete todo' };
  }
}
