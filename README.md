# SmartCV

SmartCV is a Laravel career workspace. It helps a user prepare a resume, understand how it fits a role, discover opportunities, track applications, practise interviews, build skills, and present selected work in a portfolio.

It is designed as one connected career workflow:

```text
Prepare (resume, skills, portfolio)
        ↓
Discover (live jobs and job matching)
        ↓
Apply (cover letters, applications, documents)
        ↓
Practise (interviews and learning paths)
        ↓
Track (goals, reminders, analytics)
```

## The problem SmartCV solves

Job seekers often keep their resume, job links, interview notes, skill plans, and portfolio projects in separate documents and websites. SmartCV gives each signed-in user one private workspace for those connected activities. It combines practical career tools with optional AI guidance while keeping files and personal data tied to the user’s account.

## Major features

### Resume and profile

- Upload PDF, DOCX, and TXT resumes and extract readable text.
- Create resumes from scratch, save versions, choose a primary resume, and download or export documents.
- Store profile details, target role, experience level, and career direction.
- Run optional Groq-powered resume reviews and job-description matching.
- Show an explainable SmartCV guidance score, not an official employer ATS score or hiring guarantee.

### Jobs and applications

- Browse technology openings from the public Arbeitnow Job Board API.
- Filter live openings, save private searches, and match a selected job against a selected resume.
- Save opportunities to a private Job Tracker.
- Track saved, applied, interviewing, offer, rejected, closed, and withdrawn applications.
- Store follow-up dates, priorities, recruiter contacts, private attachments, and notes.

### Preparation and growth

- Create targeted cover letters linked to a resume or job.
- Create interview sessions, save written answers, record a private audio/video response, and receive structured feedback.
- Track skills, certificates, evidence, milestones, and learning progress.
- Generate learning paths and career-goal guidance from the user’s saved context.
- Maintain career goals, reminders, notifications, and analytics.

### Portfolio and documents

- Add portfolio projects with descriptions, skills, images, repository links, and public/private visibility.
- Publish a controlled public portfolio at a chosen slug.
- Keep resumes, document-vault files, recruiter attachments, certificates, and recordings on private storage by default.
- Export personal data and application reports.

### Platform management

- Registration, login, password reset, email verification, onboarding, profile settings, and account deletion.
- An administrator area for trusted team members to manage support requests and announcements.
- In-app notifications and privacy preferences.

## Technology stack

| Area | Technology |
| --- | --- |
| Backend | PHP 8.2+ and Laravel 12 |
| Frontend | Blade templates, Tailwind CSS, Vite |
| Database | MySQL in normal development/deployment; SQLite for tests |
| Authentication | Laravel sessions and email verification |
| Files | Laravel Storage on the private `local` disk |
| AI | Groq Chat Completions API, configured server-side |
| Live jobs | Arbeitnow public Job Board API |
| Testing | PHPUnit and Laravel feature tests |

## Laravel architecture

SmartCV uses Laravel and Blade rather than a separate frontend application. This keeps deployment and college-viva explanation straightforward.

- **Routes** in `routes/web.php` define public, guest, authenticated, and verified-user areas.
- **Controllers** validate requests, verify ownership, and return Blade responses.
- **Models** represent users, resumes, versions, AI analyses, applications, interviews, skills, goals, portfolio projects, learning paths, and documents.
- **Services** hold reusable external logic. `GroqAiService` validates structured AI responses; `UserNotificationService` creates notifications safely across supported notification schemas.
- **Migrations** in `database/migrations` initialise and evolve the database.
- **Views** in `resources/views` share Blade components for layout, navigation, forms, and feedback states.

## Database overview

Every user owns their personal career data:

```text
User
 ├─ Resumes → Resume Versions and AI Analyses
 ├─ Job Applications → Contacts and Attachments
 ├─ Interview Sessions
 ├─ Skills → Milestones
 ├─ Career Goals → Milestones
 ├─ Portfolio Projects
 ├─ Cover Letters
 ├─ Learning Paths → Learning Path Items
 ├─ Private Documents
 └─ Notifications and saved Job Searches
```

Foreign keys and ownership checks keep career data connected to its owner. Application code scopes user-owned records before showing, updating, downloading, or deleting them.

## AI guidance

AI use is optional. Before calling Groq, SmartCV requires consent and sends only the resume text plus job information needed for the requested analysis.

```text
Selected private resume + optional job description
        ↓
Server-side Groq request
        ↓
JSON response
        ↓
Laravel validates and normalises score, lists, and lengths
        ↓
Structured result saved in the user’s private history
```

Malformed provider JSON, missing configuration, timeouts, and provider failures are caught. The analysis is marked as failed with a safe user-facing message; raw provider errors and API keys are not displayed.

### About scores

Resume and job-match scores are SmartCV guidance. They summarise structured information such as role alignment, relevant skills, keywords, section completeness, and improvement opportunities. They are not an official ATS score, employer decision, or guarantee of an interview.

## Live job board

The live board requests public data from Arbeitnow. SmartCV cleans description HTML before rendering it, filters results, and caches a page briefly to avoid unnecessary external requests. If Arbeitnow is unavailable, the rest of SmartCV keeps working and the page shows a clear fallback message. Applying happens on the employer’s original listing.

## Security and privacy

- Workspace routes require authentication and verified email addresses.
- Controllers scope records to the signed-in user and reject cross-account access with a 404 response.
- Public portfolio pages require an enabled public profile and only render projects explicitly marked public.
- Files are stored on Laravel’s private local disk, outside the public web root.
- Resume, document, attachment, certificate, and recording downloads verify ownership before access.
- Upload routes validate file input, extension, and the 10 MB size limit.
- Forms use Laravel CSRF protection and server-side validation.
- Login, registration, reset, uploads, and AI routes are throttled where appropriate.
- Groq keys come from environment variables. Never put real credentials in Blade, JavaScript, Git commits, or documentation.
- Administrator access can only be changed from the protected admin area, and the last administrator cannot be removed.

Before deployment, set `APP_DEBUG=false`, use HTTPS, configure a production mail service, rotate test credentials, and review server permissions.

## Installation

### Prerequisites

- PHP 8.2 or newer and Composer
- Node.js and npm
- MySQL 8+ or a compatible MySQL/MariaDB server
- A Groq API key only when AI features are required

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Configure environment

```bash
copy .env.example .env
php artisan key:generate
```

On macOS/Linux use `cp .env.example .env` instead of `copy`.

Update `.env` for MySQL:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartcv
DB_USERNAME=root
DB_PASSWORD=
```

Create the `smartcv` database in phpMyAdmin or MySQL first. Do not commit `.env` because it may contain private credentials.

### 3. Configure optional AI

```dotenv
GROQ_API_KEY=your_private_key_here
GROQ_MODEL=llama-3.1-8b-instant
GROQ_TIMEOUT=30
```

Without a key, normal SmartCV features still work. AI actions fail safely with a configuration message. Configure mail variables too if real email verification and reset emails are required.

### 4. Create tables and run SmartCV

```bash
php artisan migrate
npm run build
php artisan serve
```

Open the Laravel URL, usually `http://127.0.0.1:8000`. Use `npm run dev` in another terminal during frontend development.

> `php artisan migrate:fresh` deletes existing tables. Use it only for a completely fresh local database.

## Useful commands

```bash
# Run automated tests
php artisan test --compact

# Show routes
php artisan route:list

# Check migrations
php artisan migrate:status

# Clear local caches while developing
php artisan optimize:clear

# Build frontend assets
npm run build
```

## Testing

The suite focuses on real workflows rather than meaningless test counts. It covers:

- authentication and email verification;
- resume upload, parsing, versions, ownership, and AI review;
- job matching and live-job API failure fallback;
- private-document upload, download, deletion, and cross-user access rejection;
- applications and portfolio visibility;
- learning paths, reminders, analytics exports, and admin authorization.

Run the suite before pushing changes:

```bash
php artisan test --compact
```

## Deployment checklist

1. Create the production database and run `php artisan migrate --force`.
2. Set `APP_ENV=production` and `APP_DEBUG=false`.
3. Keep a unique `APP_KEY` and Groq key in hosting-provider secrets.
4. Use HTTPS and a real mail configuration.
5. Keep uploads on persistent private storage; do not expose the storage directory directly.
6. Configure a production cache driver.

## Suggested demo flow

1. Register and verify an account.
2. Upload or create a resume.
3. Run a resume review or compare it with a live job.
4. Save the job to the tracker and add a follow-up.
5. Create a cover letter and interview practice session.
6. Add portfolio work, then publish only explicitly public projects.

## Future improvements

- Queue slow AI and document processing for larger deployments.
- Use dedicated production object storage for uploads.
- Add job sources only after reviewing their terms of use.
- Expand accessibility audits and browser-level end-to-end tests.
- Add administrator audit logs for sensitive account changes.

## Team and repository safety

Keep repository write access limited to trusted team members. Do not commit `.env`, API keys, real resumes, or private documents. Choose and add a license before allowing broad public reuse.

---

SmartCV is a college and portfolio project demonstrating practical Laravel engineering: authenticated workflows, data ownership, external API resilience, structured AI guidance, and connected career management.
