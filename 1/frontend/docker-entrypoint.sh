#!/bin/sh

# Install dependencies if node_modules is empty or missing zone.js
if [ ! -d "node_modules" ] || [ ! -d "node_modules/zone.js" ]; then
  echo "Installing npm dependencies..."
  npm install
fi

# Execute the main command
exec "$@"

