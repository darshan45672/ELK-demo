import { ObjectId } from 'mongodb';

export interface Todo {
  _id?: ObjectId;
  title: string;
  description?: string;
  completed: boolean;
  priority: 'low' | 'medium' | 'high';
  createdAt: Date;
  updatedAt: Date;
}

export type CreateTodoInput = Omit<Todo, '_id' | 'createdAt' | 'updatedAt' | 'completed'> & {
  completed?: boolean;
};

export type UpdateTodoInput = Partial<Omit<Todo, '_id' | 'createdAt'>>;
