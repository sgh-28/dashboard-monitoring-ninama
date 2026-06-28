ninama-dashboard - Laravel Project Template (AdminLTE + CallMeBot + Google Calendar)
================================================================================
This is a lightweight template containing the app files, services, controllers,
migrations and views for the "Ninama Dashboard" project.

IMPORTANT:
- This ZIP does NOT include the full Laravel framework (vendor/). After extracting
  into a directory, run:
    composer install
  or create a fresh Laravel project and copy these files into it.

Quick start:
1. Create a fresh Laravel project (recommended):
     composer create-project laravel/laravel ninama-dashboard
   or use your existing Laravel project.

2. Copy the contents of this template into the Laravel project root (merge files).

3. Install dependencies:
     composer require google/apiclient
     composer require guzzlehttp/guzzle

4. Add credentials.json (Google OAuth) in project root (same level as composer.json).
   See README section "Google Calendar setup" below.

5. Configure .env:
   - Set APP_NAME, APP_URL, DB_CONNECTION and database credentials
   - Set MAIL_ configuration for email notifications
   - Add CALLMEBOT_APIKEY (if required) and CALLMEBOT_PHONE_TO

6. Run migrations:
     php artisan migrate

7. Generate app key and serve:
     php artisan key:generate
     php artisan serve

8. Test integration route:
     http://localhost:8000/test-integrasi

Files included:
- app/Models/Project.php
- app/Http/Controllers/ProjectController.php
- app/Http/Controllers/TestIntegrationController.php
- app/Services/GoogleCalendarService.php
- app/Services/WhatsAppService.php
- app/Mail/DeadlineMail.php
- app/Console/Commands/SendDeadlineReminder.php
- database/migrations/2025_01_01_000000_create_projects_table.php
- routes/web.php
- resources/views/layouts/adminlte.blade.php
- resources/views/dashboard.blade.php
- resources/views/emails/deadline.blade.php
- README (this file)

Google Calendar setup:
- Create a project in Google Cloud Console, enable Calendar API.
- Create OAuth Client ID (Web application) and download credentials.json.
- Put credentials.json in project root.
- Visit /test-integrasi to authorize (first run will prompt Google login).

WhatsApp (CallMeBot) usage:
- CallMeBot requires an API key available from their site.
- Place API key in .env as CALLMEBOT_APIKEY and a default target phone CALLMEBOT_PHONE_TO.

Scheduler:
- The included artisan command SendDeadlineReminder can be scheduled inside
  app/Console/Kernel.php -> schedule() to run daily:
     $schedule->command('ninama:send-reminders')->daily();

If you want, I can also generate a full Laravel project on your machine step-by-step.
