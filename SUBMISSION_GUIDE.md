# SmartCV — organization review guide

## 60-second explanation

SmartCV brings resume preparation, job matching, application tracking, interview practice, skill development, and portfolio sharing into one career workspace. The main design goal is to connect these existing tasks while keeping personal workspace data private by default.

## Demo path

1. Register and verify an account; complete onboarding.
2. Open the resume builder or upload a text-based resume.
3. Show a resume review. If using AI, explain the consent checkbox and that the score is guidance only.
4. Compare a selected resume with a job description, then save the role in the job tracker.
5. Open interview practice and skills to show how preparation progress is saved.
6. Publish a portfolio with one public project, then show how sharing can be turned off.
7. Show personal data export and account privacy settings.

Use a demo account and fictional resume details. Never show real API keys, private resumes, or another person’s personal data during a presentation.

## Technical map

Laravel 12 handles page requests and data rules. Blade renders pages. Eloquent models connect each user’s private records. Services handle Groq requests and resume text extraction. Migrations create and update the database. Feature tests exercise the main request workflows and access controls.

## Local setup and checks

Use the prerequisites and full commands in [README.md](README.md). The quick checks are:

```bash
php artisan test
npm run build
```

The AI integration needs a private Groq API key. The live job board needs an internet connection. Do not commit `.env` or demo secrets.

## Known limitations

- Image-only PDFs are detected but OCR is not included.
- AI output can be wrong and is not an official ATS or hiring decision.
- Email career reminders and weekly career reviews are currently preference fields only; they are not delivered or scheduled.
- A real public deployment still needs a chosen host, email service, private persistent file storage, and deployment-specific policy review.

## Discussion questions

- How does SmartCV stop one user from downloading another user’s file?
- What does the AI consent checkbox explain?
- Which portfolio content is public, and who controls that choice?
- What should change before using a real production host?
