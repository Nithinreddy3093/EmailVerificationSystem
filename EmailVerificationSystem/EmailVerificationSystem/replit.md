# GitHub Timeline Email Notifier

## Overview

This is a PHP-based email notification system that monitors GitHub's public timeline and sends periodic updates to registered subscribers. The system uses a simple file-based storage approach and implements email verification, subscription management, and automated CRON-based notifications.

## System Architecture

The application follows a server-side architecture built in PHP with PostgreSQL database:

- **Backend**: Pure PHP scripts handling all logic
- **Data Storage**: PostgreSQL database with proper schema
- **Email System**: PHP's built-in `mail()` function with development logging
- **Automation**: CRON job for periodic execution
- **External API**: GitHub timeline endpoint integration
- **Database**: PostgreSQL with tables for registered_emails and verification_codes

## Key Components

### Core Files Structure
```
├── functions.php          # Core business logic functions with database integration
├── cron.php              # Scheduled task handler
├── setup_cron.sh         # CRON job configuration script
├── index.php             # Main subscription interface
├── unsubscribe.php       # Unsubscribe interface
└── main.py               # Flask wrapper for Replit compatibility
```

### Database Schema
```sql
registered_emails:
- id (SERIAL PRIMARY KEY)
- email (VARCHAR(255) UNIQUE)
- registered_at (TIMESTAMP)
- is_active (BOOLEAN)

verification_codes:
- id (SERIAL PRIMARY KEY)
- email (VARCHAR(255))
- code (VARCHAR(6))
- code_type (VARCHAR(20))
- created_at (TIMESTAMP)
- expires_at (TIMESTAMP)
- used (BOOLEAN)
```

### Function Architecture
The system is built around modular functions in `functions.php`:

1. **Email Management**:
   - `generateVerificationCode()`: Creates 6-digit verification codes
   - `registerEmail()`: Handles email subscription storage
   - `unsubscribeEmail()`: Manages email removal from registry

2. **Email Communication**:
   - `sendVerificationEmail()`: Sends verification codes to new subscribers
   - Email content formatted in HTML (not JSON)
   - Includes unsubscribe links in all emails

3. **GitHub Integration**:
   - `fetchGitHubTimeline()`: Retrieves data from GitHub's timeline API
   - `formatGitHubData()`: Converts API response to HTML format
   - `sendGitHubUpdatesToSubscribers()`: Distributes updates to all registered users

## Data Flow

1. **Subscription Process**:
   - User provides email address
   - System generates verification code
   - Verification email sent (with code displayed for development)
   - Upon verification, email stored in PostgreSQL database

2. **Unsubscription Process**:
   - User clicks unsubscribe link
   - System generates confirmation code
   - Confirmation required before removal
   - Email marked as inactive in database (soft delete)

3. **Notification Process** (Every 5 minutes):
   - CRON job executes `cron.php`
   - Script fetches latest GitHub timeline data
   - Data formatted as HTML content
   - Email notifications sent to all active subscribers from database

## External Dependencies

- **GitHub Timeline API**: `https://www.github.com/timeline`
- **PHP Mail Function**: Built-in PHP email functionality
- **CRON System**: Unix/Linux task scheduler

## Deployment Strategy

### CRON Job Setup
- `setup_cron.sh` script automatically configures CRON job
- Job executes `cron.php` every 5 minutes
- No manual CRON configuration required

### Database Configuration
- PostgreSQL database automatically configured via environment variables
- Tables created automatically on first run
- Connection handled via PHP PDO with proper error handling

### Email Configuration
- System relies on server's mail configuration
- SMTP settings handled at server level
- HTML email format for better presentation

## User Preferences

Preferred communication style: Simple, everyday language.

## Changelog

Changelog:
- June 28, 2025: Initial setup with file-based storage
- June 28, 2025: Added PostgreSQL database integration
  - Created database tables for registered_emails and verification_codes
  - Updated all functions to use database instead of text files
  - Implemented soft delete for unsubscriptions
  - Added proper database connection handling with PDO