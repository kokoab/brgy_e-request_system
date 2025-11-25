FROM node:22

# Create app directory
WORKDIR /usr/src/app

# Copy package files first for caching
COPY package*.json ./

# Install global Angular CLI
RUN npm install -g @angular/cli

# Install project dependencies
RUN npm install

# Copy the rest of your angular project
COPY . .

# Expose Angular dev server port
EXPOSE 4200

# Run Angular dev server at runtime (NOT during build)
CMD ["ng", "serve", "--host", "0.0.0.0", "--poll", "1000"]
