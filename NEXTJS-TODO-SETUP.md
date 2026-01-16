# Next.js Todo App with MongoDB & ELK Stack

A full-stack Todo application built with Next.js 15, MongoDB, shadcn/ui, and integrated with ELK stack for comprehensive logging.

---

## 🚀 Features

- ✅ **Next.js 15** with App Router and Server Actions
- ✅ **MongoDB** for data persistence
- ✅ **shadcn/ui** components with Tailwind CSS
- ✅ **Winston Logger** with structured JSON logging for ELK
- ✅ **CRUD Operations**: Create, Read, Update, Delete todos
- ✅ **Priority Levels**: Low, Medium, High
- ✅ **Real-time Updates** with Server Actions
- ✅ **Responsive Design** with dark mode support

---

## 📋 Prerequisites

- Node.js 20+
- MongoDB running (via Docker or local)
- Docker/Podman (for containerized deployment)

---

## 🛠️ Local Development Setup

### 1. Install Dependencies

```bash
cd js-app
npm install
```

### 2. Configure Environment Variables

Create `.env.local`:

```env
MONGODB_URI=mongodb://localhost:27017/nextjs-todo
LOG_LEVEL=info
NODE_ENV=development
```

### 3. Start MongoDB Locally

```bash
# Using Docker
docker run -d -p 27017:27017 --name mongodb mongo:7

# Or using Podman
podman run -d -p 27017:27017 --name mongodb mongo:7
```

### 4. Run Development Server

```bash
npm run dev
```

Visit: **http://localhost:3000**

---

## 🐳 Docker Deployment

The app is already configured in the ELK stack's `podman-compose.yml`.

### Start All Services

```bash
cd /path/to/ELK-demo
podman-compose up -d
```

Services will be available at:
- **Next.js App**: http://localhost:3000
- **MongoDB**: localhost:27017
- **Elasticsearch**: http://localhost:9200
- **Kibana**: http://localhost:5601

### Check Logs

```bash
# View app logs
podman logs -f js-todo-app

# View MongoDB logs
podman logs -f mongodb

# Check all running containers
podman ps
```

---

## 📁 Project Structure

```
js-app/
├── app/
│   ├── actions.ts          # Server Actions (CRUD operations)
│   ├── globals.css         # Global styles
│   ├── layout.tsx          # Root layout
│   └── page.tsx            # Home page
├── components/
│   ├── ui/                 # shadcn/ui components
│   ├── add-todo-form.tsx   # Todo creation form
│   ├── todo-item.tsx       # Individual todo item
│   └── todo-list.tsx       # Todo list container
├── lib/
│   ├── mongodb.ts          # MongoDB connection
│   ├── logger.ts           # Winston logger setup
│   ├── types.ts            # TypeScript types
│   └── utils.ts            # Utility functions
├── logs/
│   └── elk.log             # Application logs (JSON)
├── Dockerfile              # Docker build config
└── package.json            # Dependencies
```

---

## 🔍 How It Works

### Server Actions

The app uses Next.js Server Actions for all data operations:

```typescript
// app/actions.ts
'use server';

export async function createTodo(input: CreateTodoInput) {
  // Logs to elk.log
  logger.info('Creating new todo', { action: 'create_todo', title: input.title });
  
  const db = await getDatabase();
  const result = await db.collection('todos').insertOne(todo);
  
  revalidatePath('/');
  return { success: true };
}
```

### MongoDB Integration

Connection pooling with singleton pattern:

```typescript
// lib/mongodb.ts
const client = new MongoClient(uri, {
  maxPoolSize: 10,
  minPoolSize: 5,
});

export async function getDatabase(): Promise<Db> {
  const client = await clientPromise;
  return client.db();
}
```

### Structured Logging

All actions are logged in JSON format for ELK ingestion:

```json
{
  "timestamp": "2026-01-16T11:19:18.992453+00:00",
  "level": "INFO",
  "level_name": "INFO",
  "channel": "nextjs",
  "message": "Creating new todo",
  "context": {
    "action": "create_todo",
    "title": "Buy groceries",
    "priority": "medium"
  }
}
```

---

## 🎨 shadcn/ui Components Used

- **Button**: Action buttons
- **Card**: Todo item containers
- **Input**: Form inputs
- **Checkbox**: Todo completion toggle
- **Label**: Form labels

### Adding More Components

```bash
npx shadcn@latest add [component-name]
```

Example:
```bash
npx shadcn@latest add dialog toast select
```

---

## 📊 Logging & Monitoring

### View Logs Locally

```bash
# Real-time log viewing
tail -f logs/elk.log

# Pretty print JSON logs
tail -f logs/elk.log | jq
```

### Log Levels

- `info`: Normal operations (default)
- `warn`: Warnings (e.g., validation failures)
- `error`: Errors (e.g., database connection issues)
- `debug`: Detailed debugging info

### Configure Log Level

```env
LOG_LEVEL=debug  # Change to: info, warn, error, debug
```

### ELK Stack Integration

Logs are automatically collected by:
1. **Filebeat** → Reads `/app/logs/elk.log`
2. **Kafka** → Queues log messages
3. **Logstash** → Processes and enriches logs
4. **Elasticsearch** → Stores and indexes logs
5. **Kibana** → Visualizes logs

**View in Kibana**: http://localhost:5601

---

## 🧪 Testing the App

### Create a Todo

1. Fill in the title (required)
2. Add description (optional)
3. Select priority level
4. Click "Add Todo"

### Update a Todo

1. Click the edit icon (pencil)
2. Modify fields
3. Click "Save" or "Cancel"

### Toggle Completion

Click the checkbox to mark as complete/incomplete

### Delete a Todo

Click the trash icon and confirm

---

## 🔧 Environment Variables

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `MONGODB_URI` | MongoDB connection string | `mongodb://localhost:27017/nextjs-todo` | Yes |
| `LOG_LEVEL` | Logging verbosity | `info` | No |
| `NODE_ENV` | Environment mode | `development` | No |

---

## 📝 API Routes (Server Actions)

All operations use Server Actions (no REST API needed):

| Action | Function | Description |
|--------|----------|-------------|
| **GET** | `getTodos()` | Fetch all todos |
| **POST** | `createTodo(input)` | Create new todo |
| **PATCH** | `updateTodo(id, input)` | Update existing todo |
| **PATCH** | `toggleTodo(id, completed)` | Toggle completion status |
| **DELETE** | `deleteTodo(id)` | Delete todo |

---

## 🐛 Troubleshooting

### MongoDB Connection Error

```bash
# Error: MongoServerError: Authentication failed
# Solution: Check MONGODB_URI and ensure MongoDB is running

# Check if MongoDB is running
podman ps | grep mongodb

# Restart MongoDB
podman restart mongodb
```

### Build Errors

```bash
# Clear Next.js cache
rm -rf .next

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install

# Rebuild
npm run build
```

### Logs Not Appearing

```bash
# Check logs directory exists
ls -la logs/

# Check file permissions
chmod 777 logs/

# Test logging
node -e "require('./lib/logger').logger.info('Test log')"
```

### Port Already in Use

```bash
# Kill process on port 3000
lsof -ti:3000 | xargs kill -9

# Or use different port
PORT=3001 npm run dev
```

---

## 🚀 Production Deployment

### Build for Production

```bash
npm run build
npm start
```

### Environment Configuration

```env
NODE_ENV=production
MONGODB_URI=mongodb://production-host:27017/todos
LOG_LEVEL=warn
```

### Optimization Tips

1. **Enable SWC Minification**: Already configured in `next.config.ts`
2. **Use CDN**: For static assets
3. **Connection Pooling**: Configured with 10 max connections
4. **Log Rotation**: Winston handles this automatically

---

## 📚 Learn More

### Next.js Resources
- [Next.js Documentation](https://nextjs.org/docs)
- [Server Actions](https://nextjs.org/docs/app/building-your-application/data-fetching/server-actions-and-mutations)
- [App Router](https://nextjs.org/docs/app)

### MongoDB Resources
- [MongoDB Node.js Driver](https://www.mongodb.com/docs/drivers/node/current/)
- [MongoDB Best Practices](https://www.mongodb.com/docs/manual/administration/production-notes/)

### shadcn/ui Resources
- [shadcn/ui Documentation](https://ui.shadcn.com)
- [Component Library](https://ui.shadcn.com/docs/components)

### Winston Logging
- [Winston Documentation](https://github.com/winstonjs/winston)
- [Log Levels](https://github.com/winstonjs/winston#logging-levels)

---

## 🤝 Contributing

1. Create a new branch
2. Make your changes
3. Test thoroughly
4. Submit a pull request

---

## 📄 License

MIT License - See LICENSE file for details

---

## ✨ What's Next?

- [ ] Add user authentication
- [ ] Implement categories/tags
- [ ] Add due dates and reminders
- [ ] Search and filter functionality
- [ ] Export todos to PDF/CSV
- [ ] Mobile app with React Native
- [ ] Collaborative todos with WebSockets

---

**Happy Coding! 🎉**

Access your app at: http://localhost:3000
