FROM node:22-alpine

# Create non-root user
RUN addgroup -S appgroup && adduser -S appuser -G appgroup

WORKDIR /home/node/app

# Copy package files for better caching
COPY package.json package-lock.json ./

RUN npm ci

# Copy application code
COPY . .

# Change ownership
RUN chown -R appuser:appgroup /home/node/app

USER appuser

EXPOSE 5173

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
