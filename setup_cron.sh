#!/bin/bash

# Setup cron job for Laravel email checking
# This script will add the necessary cron job to run Laravel's scheduler

echo "Setting up cron job for email checking..."

# Get the current directory
CURRENT_DIR=$(pwd)

# Create the cron job entry
CRON_JOB="*/5 * * * * cd $CURRENT_DIR && php artisan schedule:run >> /dev/null 2>&1"

# Check if the cron job already exists
if crontab -l 2>/dev/null | grep -q "schedule:run"; then
    echo "Cron job already exists. Updating..."
    # Remove existing schedule:run entries
    crontab -l 2>/dev/null | grep -v "schedule:run" | crontab -
fi

# Add the new cron job
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

echo "Cron job added successfully!"
echo "The email checking service will now run every 5 minutes."
echo ""
echo "To view current cron jobs: crontab -l"
echo "To remove cron jobs: crontab -r"
echo ""
echo "To test the email check manually: php artisan emails:check" 