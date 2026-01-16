import winston from 'winston';
import path from 'path';
import fs from 'fs';

// Ensure logs directory exists
const logsDir = path.join(process.cwd(), 'logs');
if (!fs.existsSync(logsDir)) {
  fs.mkdirSync(logsDir, { recursive: true });
}

// Custom JSON format for ELK stack
const elkFormat = winston.format.printf(({ timestamp, level, message, ...metadata }) => {
  const log: Record<string, any> = {
    timestamp,
    level: level.toUpperCase(),
    level_name: level.toUpperCase(),
    channel: 'nextjs',
    message,
  };

  // Add context if metadata exists
  if (Object.keys(metadata).length > 0) {
    log.context = metadata;
  }

  return JSON.stringify(log);
});

export const logger = winston.createLogger({
  level: process.env.LOG_LEVEL || 'info',
  format: winston.format.combine(
    winston.format.timestamp({ format: 'YYYY-MM-DDTHH:mm:ss.SSSZ' }),
    elkFormat
  ),
  transports: [
    // Write to elk.log for ELK stack ingestion
    new winston.transports.File({
      filename: path.join(logsDir, 'elk.log'),
      maxsize: 10485760, // 10MB
      maxFiles: 5,
    }),
    // Also write to console in development
    ...(process.env.NODE_ENV === 'development'
      ? [
          new winston.transports.Console({
            format: winston.format.combine(
              winston.format.colorize(),
              winston.format.simple()
            ),
          }),
        ]
      : []),
  ],
});

// Helper function to log with user context
export function logWithContext(
  level: 'info' | 'warn' | 'error' | 'debug',
  message: string,
  context?: Record<string, any>
) {
  logger.log(level, message, {
    ...context,
    timestamp: new Date().toISOString(),
  });
}
