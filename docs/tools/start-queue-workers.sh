#!/bin/bash

# Start queue workers for n8n clone workflow execution
echo "Starting n8n clone queue workers..."

# Start workflow-specific queue worker
echo "Starting workflow queue worker..."
php artisan queue:work --queue=workflows --tries=3 --timeout=300 --memory=512 &
WORKFLOW_PID=$!

# Start default queue worker for other jobs
echo "Starting default queue worker..."
php artisan queue:work --queue=default --tries=3 --timeout=300 --memory=512 &
DEFAULT_PID=$!

echo "Queue workers started with PIDs: $WORKFLOW_PID (workflows), $DEFAULT_PID (default)"

# Create a file with the PIDs for later reference
echo "$WORKFLOW_PID" > storage/workflow_queue.pid
echo "$DEFAULT_PID" > storage/default_queue.pid

echo "Workers are running in the background."
echo "To stop them, run: kill -TERM \$(cat storage/workflow_queue.pid) \$(cat storage/default_queue.pid)"

# Wait for all background processes
wait