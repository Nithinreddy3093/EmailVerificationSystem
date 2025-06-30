#!/bin/bash
# setup_cron.sh - Must configure the CRON job

# Get the absolute path of the current directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CRON_PHP_PATH="$SCRIPT_DIR/cron.php"

echo "Setting up CRON job for GitHub timeline updates..."

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed or not in PATH"
    exit 1
fi

# Check if cron.php exists
if [ ! -f "$CRON_PHP_PATH" ]; then
    echo "Error: cron.php not found at $CRON_PHP_PATH"
    exit 1
fi

# Create the cron job entry
CRON_ENTRY="*/5 * * * * /usr/bin/php $CRON_PHP_PATH >> $SCRIPT_DIR/cron.log 2>&1"

# Check if the cron job already exists
if crontab -l 2>/dev/null | grep -q "$CRON_PHP_PATH"; then
    echo "CRON job already exists for this script"
else
    # Add the new cron job
    (crontab -l 2>/dev/null; echo "$CRON_ENTRY") | crontab -
    
    if [ $? -eq 0 ]; then
        echo "CRON job successfully added!"
        echo "The job will run every 5 minutes: $CRON_ENTRY"
    else
        echo "Error: Failed to add CRON job"
        exit 1
    fi
fi

# Display current crontab
echo ""
echo "Current crontab entries:"
crontab -l 2>/dev/null || echo "No crontab entries found"

# Test the cron.php script
echo ""
echo "Testing cron.php script..."
php "$CRON_PHP_PATH"

echo ""
echo "Setup complete! The CRON job will automatically fetch GitHub timeline updates every 5 minutes."
echo "Logs will be written to: $SCRIPT_DIR/cron.log"
