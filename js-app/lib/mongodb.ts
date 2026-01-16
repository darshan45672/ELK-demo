import { MongoClient, Db } from 'mongodb';
import { logger } from './logger';

const uri = process.env.MONGODB_URI || '';
const options = {
  maxPoolSize: 10,
  minPoolSize: 5,
  maxIdleTimeMS: 30000,
};

let client: MongoClient;
let clientPromise: Promise<MongoClient>;

if (process.env.NODE_ENV === 'development') {
  // In development mode, use a global variable to preserve the connection
  let globalWithMongo = global as typeof globalThis & {
    _mongoClientPromise?: Promise<MongoClient>;
  };

  if (!globalWithMongo._mongoClientPromise) {
    client = new MongoClient(uri, options);
    globalWithMongo._mongoClientPromise = client.connect();
    logger.info('MongoDB connection initialized in development mode');
  }
  clientPromise = globalWithMongo._mongoClientPromise;
} else {
  // In production mode, create a new client for each invocation
  client = new MongoClient(uri, options);
  clientPromise = client.connect();
  logger.info('MongoDB connection initialized in production mode');
}

export async function getDatabase(): Promise<Db> {
  if (!uri) {
    throw new Error('Please add your MongoDB URI to .env.local or MONGODB_URI environment variable');
  }
  
  try {
    const client = await clientPromise;
    return client.db();
  } catch (error) {
    logger.error('Failed to connect to MongoDB', { error });
    throw error;
  }
}

export default clientPromise;
