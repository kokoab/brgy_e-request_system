FROM node:22

# Create app directory
WORKDIR /usr/src/app

# Node image already has a user with UID 1000, so we'll use that
# Check if node user exists, if not create it
RUN if ! id -u node > /dev/null 2>&1; then \
      groupadd -r node && useradd -r -g node -u 1000 node; \
    fi

# Copy package files first for caching
COPY package*.json ./

# Install global Angular CLI
RUN npm install -g @angular/cli

# Install project dependencies
RUN npm install

# Copy entrypoint script
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

# Copy the rest of your angular project
COPY . .

# Change ownership to node user (which has UID 1000)
RUN chown -R node:node /usr/src/app

# Switch to non-root user
USER node

# Expose Angular dev server port
EXPOSE 4200

# Use entrypoint to ensure npm install runs if needed
ENTRYPOINT ["/docker-entrypoint.sh"]

# Run Angular dev server at runtime
CMD ["ng", "serve", "--host", "0.0.0.0", "--poll", "1000"]
