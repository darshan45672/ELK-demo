# Docker Build Fixes for Next.js App

## Problem Summary

The Next.js todo app failed to build in Docker with the error:
```
Error: Please add your MongoDB URI to .env.local
```

This occurred because Next.js was trying to connect to MongoDB during the **build phase**, but environment variables weren't available and the database connection wasn't needed for building.

---

## Root Causes

### 1. **Eager MongoDB Connection**
The `lib/mongodb.ts` file threw an error immediately when the module loaded:
```typescript
if (!process.env.MONGODB_URI) {
  throw new Error('Please add your MongoDB URI to .env.local');
}
```

### 2. **Static Prerendering**
Next.js tried to prerender the home page at build time, which triggered:
- Server Actions execution during build
- Database connection attempts
- MongoDB URI validation

### 3. **Inefficient Dockerfile**
The original single-stage Dockerfile:
- Installed all dependencies (dev + prod)
- Used `npm start` which requires the full build
- Didn't optimize for Docker's layer caching

---

## Solutions Applied

### ✅ 1. Lazy MongoDB Connection

**File: `lib/mongodb.ts`**

**Before:**
```typescript
if (!process.env.MONGODB_URI) {
  throw new Error('Please add your MongoDB URI to .env.local');
}
const uri = process.env.MONGODB_URI;
```

**After:**
```typescript
const uri = process.env.MONGODB_URI || '';

export async function getDatabase(): Promise<Db> {
  if (!uri) {
    throw new Error('Please add your MongoDB URI to .env.local or MONGODB_URI environment variable');
  }
  // ... connection logic
}
```

**Why:** Error is thrown only when the database is actually accessed, not at module load.

---

### ✅ 2. Force Dynamic Rendering

**File: `app/page.tsx`**

Added:
```typescript
export const dynamic = 'force-dynamic';
export const revalidate = 0;
```

**Why:** 
- Prevents Next.js from prerendering the page at build time
- Database is only accessed at **runtime** when users make requests
- Build completes without needing MongoDB connection

---

### ✅ 3. Multi-Stage Docker Build

**File: `Dockerfile`**

Implemented a 3-stage build:

#### **Stage 1: Dependencies**
```dockerfile
FROM node:24-alpine AS deps
RUN apk add --no-cache libc6-compat
WORKDIR /app
COPY package*.json ./
RUN npm ci
```

#### **Stage 2: Builder**
```dockerfile
FROM node:24-alpine AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .

# Placeholder env for build (not used at runtime)
ENV MONGODB_URI="mongodb://placeholder:27017/placeholder"
ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1

RUN npm run build
```

#### **Stage 3: Runner**
```dockerfile
FROM node:24-alpine AS runner
WORKDIR /app

ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1

RUN addgroup --system --gid 1001 nodejs
RUN adduser --system --uid 1001 nextjs

# Copy only production files
COPY --from=builder /app/public ./public
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static

RUN mkdir -p /app/logs && chown nextjs:nodejs /app/logs

USER nextjs
EXPOSE 3000

CMD ["node", "server.js"]
```

**Benefits:**
- ✅ Smaller final image (only production dependencies)
- ✅ Better layer caching
- ✅ Runs as non-root user (security)
- ✅ Uses standalone output (faster startup)

---

### ✅ 4. Standalone Output

**File: `next.config.ts`**

```typescript
const nextConfig: NextConfig = {
  output: 'standalone',
};
```

**Why:**
- Generates a minimal Node.js server (`server.js`)
- Includes only necessary dependencies
- Reduces image size by ~40%
- Faster container startup

---

### ✅ 5. Updated Compose Configuration

**File: `podman-compose.yml`**

**Before:**
```yaml
volumes:
  - ./js-app:/app
  - /app/node_modules
  - /app/.next
  - js-logs:/app/logs
command: npm start
```

**After:**
```yaml
volumes:
  - js-logs:/app/logs  # Only logs volume needed
environment:
  - MONGODB_URI=mongodb://mongodb:27017/nextjs-todo
  - LOG_LEVEL=info
  - NODE_ENV=production
# No command needed - uses Dockerfile CMD
```

**Why:**
- Standalone build doesn't need source code volume mounts
- Real environment variables override build placeholders
- Cleaner, production-ready setup

---

## Build & Deployment

### Build the Image
```bash
podman-compose build js-todo-app
```

### Start All Services
```bash
podman-compose up -d
```

### Check Status
```bash
podman ps
podman logs js-todo-app
```

---

## Verification

### ✅ Successful Build Output
```
▲ Next.js 16.1.2 (Turbopack)

  Creating an optimized production build ...
✓ Compiled successfully in 4.3s
  Running TypeScript ...
  Collecting page data using 4 workers ...
  Generating static pages using 4 workers (0/3) ...
✓ Generating static pages using 4 workers (3/3) in 87.2ms

Route (app)
┌ ƒ /
└ ○ /_not-found

ƒ  (Dynamic)  server-rendered on demand
```

### ✅ Container Status
```
NAMES          STATUS
js-todo-app    Up (healthy)
mongodb        Up (healthy)
elasticsearch  Up (healthy)
kafka          Up (healthy)
logstash       Up (healthy)
kibana         Up (healthy)
```

### ✅ App Running
```
▲ Next.js 16.1.2
- Local:    http://localhost:3000
- Network:  http://0.0.0.0:3000

✓ Ready in 50ms
```

---

## Key Takeaways

### 🎓 Next.js Docker Best Practices

1. **Use `output: 'standalone'`** for Docker deployments
2. **Force dynamic rendering** for pages with runtime dependencies
3. **Lazy initialization** for external connections (DB, APIs)
4. **Multi-stage builds** for smaller, more secure images
5. **Non-root users** for production containers

### 🎓 Environment Variables in Next.js

- **Build time:** Variables are bundled into the code
- **Runtime:** Variables are read when requests are made
- Use `dynamic = 'force-dynamic'` to ensure runtime evaluation
- Don't rely on `.env.local` in Docker - use compose environment

### 🎓 Common Pitfalls

❌ **Don't:** Connect to external services during module load  
✅ **Do:** Lazy load connections when actually needed

❌ **Don't:** Use `npm start` in Docker (requires full node_modules)  
✅ **Do:** Use standalone output with `node server.js`

❌ **Don't:** Run containers as root  
✅ **Do:** Create dedicated user (nextjs:1001)

---

## Resources

- [Next.js Docker Documentation](https://nextjs.org/docs/deployment#docker-image)
- [Standalone Output](https://nextjs.org/docs/advanced-features/output-file-tracing)
- [Dynamic Rendering](https://nextjs.org/docs/app/building-your-application/rendering/server-components#dynamic-rendering)
- [Environment Variables](https://nextjs.org/docs/app/building-your-application/configuring/environment-variables)

---

**Status:** ✅ All issues resolved, app running successfully in Docker!
