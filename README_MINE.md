# SmartCV — project overview

SmartCV is a Laravel career workspace. It helps a job seeker prepare resumes, compare a resume with a job, track applications, practise interviews, plan skill growth, and publish selected portfolio work.

## Main user journey

1. Create an account, verify email, and complete onboarding.
2. Upload a resume or build one from scratch.
3. Review the resume or compare it with a job description using optional AI guidance.
4. Save opportunities in the job tracker and prepare a cover letter.
5. Practise interview answers, track skills, and review career goals.
6. Publish only the portfolio projects and resume the user chooses to share.

## Technology

- PHP 8.2+ and Laravel 12 for server-side application logic.
- Blade templates for the pages and Tailwind CSS/Vite for the interface.
- MySQL for a normal deployment; SQLite is configured for automated tests.
- Groq for optional AI actions and Arbeitnow for public job listings.
- Laravel private storage for user uploads.

## Where to look

- `routes/web.php`: public pages and signed-in workflows.
- `app/Http/Controllers`: request validation, ownership checks, and page actions.
- `app/Services`: AI requests and resume text parsing.
- `app/Models` and `database/migrations`: stored project data and schema changes.
- `resources/views`: user interface pages.
- `tests/Feature`: end-to-end-like Laravel request tests for core workflows.

## Run locally

Follow the complete setup steps in [README.md](README.md). In short: install PHP and Node dependencies, configure a local `.env`, create the database, run migrations, build the frontend, and start Laravel.

```bash
composer install
npm ci
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

AI tools need a private Groq key. The live job board needs internet access. Other workspace tools can be explored without those optional integrations.

## Privacy and limitations

Private workspace routes require a verified account. User-owned records and private downloads are checked against the signed-in account. Public portfolios show only content the owner has published. AI guidance is optional and is not an official ATS score or a promise of employment.

The current resume parser reads text-based PDF, DOCX, and TXT files. It does not perform OCR on image-only PDFs. Email reminder and weekly review choices are stored preferences; those career reminder messages are not currently sent or scheduled.

## Review checklist

Run `php artisan test` and `npm run build`, then walk through account setup, resume import, AI consent, job tracking, portfolio privacy, personal-data export, and account deletion. See the deployment and staging checklist in [README.md](README.md).
