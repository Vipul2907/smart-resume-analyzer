# smart-resume-analyzer
# 🧠 Smart Resume Analyzer

> An AI-powered career and resume management platform built with Laravel that helps users create, manage, parse, analyze, improve, and track their resumes while organizing their job-search journey in one centralized workspace.

**Smart Resume Analyzer** is a full-stack web application designed to make resume management and career preparation more structured, intelligent, and actionable.

Instead of treating a resume as a static document, the application turns it into a central career profile that can be analyzed with AI, versioned, improved, connected with job applications, used for interview preparation, and supported with career goals, skills, portfolio projects, and analytics.

The project is built using **Laravel 12**, **PHP 8.2+**, **Vite**, **Tailwind CSS**, and a combination of custom resume-processing services and the **Groq API** for AI-powered resume analysis.

---

## 📌 Table of Contents

* [Overview](#-overview)
* [Why Smart Resume Analyzer?](#-why-smart-resume-analyzer)
* [Problem Statement](#-problem-statement)
* [Project Goals](#-project-goals)
* [Key Features](#-key-features)
* [Application Workflow](#-application-workflow)
* [Resume Management](#-resume-management)
* [Resume Builder](#-resume-builder)
* [Resume Parsing](#-resume-parsing)
* [Document Text Extraction](#-document-text-extraction)
* [AI Resume Analysis](#-ai-resume-analysis)
* [Groq AI Integration](#-groq-ai-integration)
* [AI Analysis Output](#-ai-analysis-output)
* [Resume Versioning](#-resume-versioning)
* [Job Application Tracking](#-job-application-tracking)
* [Interview Preparation](#-interview-preparation)
* [Skills Management](#-skills-management)
* [Career Goals](#-career-goals)
* [Portfolio Management](#-portfolio-management)
* [Dashboard](#-dashboard)
* [Analytics](#-analytics)
* [Authentication](#-authentication)
* [Onboarding](#-onboarding)
* [Application Architecture](#-application-architecture)
* [Technology Stack](#-technology-stack)
* [Backend Architecture](#-backend-architecture)
* [Frontend Architecture](#-frontend-architecture)
* [Database Architecture](#-database-architecture)
* [Project Structure](#-project-structure)
* [Important Directories](#-important-directories)
* [Controllers](#-controllers)
* [Services](#-services)
* [Models](#-models)
* [Routes](#-routes)
* [Resume Processing Pipeline](#-resume-processing-pipeline)
* [AI Processing Pipeline](#-ai-processing-pipeline)
* [Security Considerations](#-security-considerations)
* [Rate Limiting](#-rate-limiting)
* [Environment Configuration](#-environment-configuration)
* [Installation](#-installation)
* [Local Development](#-local-development)
* [Database Setup](#-database-setup)
* [Frontend Setup](#-frontend-setup)
* [Running the Application](#-running-the-application)
* [Running Tests](#-running-tests)
* [Production Build](#-production-build)
* [Troubleshooting](#-troubleshooting)
* [Supported Resume Formats](#-supported-resume-formats)
* [Current Limitations](#-current-limitations)
* [Future Improvements](#-future-improvements)
* [Development Practices](#-development-practices)
* [Contributing](#-contributing)
* [Privacy](#-privacy)
* [License](#-license)
* [Acknowledgements](#-acknowledgements)
* [Author](#-author)

---

# 🚀 Overview

Smart Resume Analyzer is a career-focused web application that combines traditional resume management with document parsing and AI-assisted analysis.

The application allows a user to maintain multiple resumes, upload resume documents, extract their textual content, parse common resume sections, send the extracted information to an AI service for analysis, and store the resulting analysis for later review.

The system goes beyond resume analysis by providing a broader career workspace.

Users can manage:

* Resumes
* Resume versions
* AI analyses
* Job applications
* Job contacts
* Job attachments
* Interview sessions
* Interview responses
* Skills
* Skill certificates
* Career goals
* Portfolio projects
* Career profiles
* Personal settings
* Analytics

This approach makes the project more than a simple resume parser.

It acts as a **personal career management workspace** where resume optimization, job tracking, interview preparation, skill development, and portfolio management can exist together.

---

# 🎯 Why Smart Resume Analyzer?

Creating a resume is only one part of the job-search process.

A candidate may need to:

1. Create a professional resume.
2. Maintain different versions for different roles.
3. Understand what information is present in the resume.
4. Identify missing sections.
5. Evaluate the overall quality of the resume.
6. Improve weak areas.
7. Track job applications.
8. Prepare for interviews.
9. Track skills and certifications.
10. Maintain portfolio projects.
11. Set career goals.
12. Review progress through analytics.

Many tools focus on only one of these activities.

Smart Resume Analyzer attempts to bring these activities into a unified application.

The central idea is simple:

> **Your resume should be a living career document, not a static file.**

The application therefore treats the resume as structured career data that can be parsed, analyzed, versioned, improved, and connected to other parts of a user's career workflow.

---

# 🧩 Problem Statement

Traditional resume management often involves disconnected tools.

A candidate may use one application to write a resume, another website to check it, a spreadsheet to track job applications, a separate document to prepare interview questions, and another tool to track skills or portfolio projects.

This creates several problems:

* Resume versions become difficult to manage.
* Previous changes can be lost.
* Job applications are disconnected from the resume used.
* Resume feedback is not stored in one place.
* Interview preparation is disconnected from career information.
* Skill development is difficult to track.
* Portfolio information may become outdated.
* Candidates lack a centralized view of their job-search progress.

Smart Resume Analyzer addresses these problems by providing a centralized career workspace.

---

# 🎯 Project Goals

The main goals of the project are:

### 1. Resume Management

Provide users with a structured way to create, upload, view, update, download, and delete resumes.

### 2. Resume Parsing

Extract useful information from uploaded resume documents.

### 3. AI Analysis

Use AI to evaluate resume content and provide structured feedback.

### 4. Resume Improvement

Identify strengths, weaknesses, missing sections, and actionable improvements.

### 5. Resume Versioning

Allow users to maintain multiple iterations of their resumes.

### 6. Career Organization

Provide tools for job applications, interviews, skills, goals, and portfolio projects.

### 7. Personal Workspace

Give users a centralized dashboard for their career-related information.

### 8. Developer-Friendly Architecture

Keep document processing, AI integration, controllers, models, and application logic separated into maintainable components.

---

# ✨ Key Features

## 📄 Resume Management

Users can manage multiple resumes inside their workspace.

The application provides functionality for:

* Creating resumes
* Uploading resumes
* Viewing resumes
* Updating resume information
* Deleting resumes
* Downloading resumes
* Selecting a primary resume
* Duplicating resumes
* Creating new versions
* Previewing resumes
* Exporting resume documents

The routing layer explicitly provides endpoints for resume CRUD operations, primary-resume selection, download functionality, parsing, versioning, preview, and DOCX export.

---

# 📝 Resume Builder

Smart Resume Analyzer includes a resume-builder workflow.

The builder allows users to create and maintain resume content within the application rather than relying exclusively on externally created files.

The application provides dedicated controller functionality for:

* Creating a resume
* Storing builder data
* Editing an existing resume
* Updating resume content
* Duplicating a resume
* Creating a new resume version
* Previewing a resume
* Exporting the resume to DOCX

This makes the application useful for both:

* Users who already have a resume file
* Users who want to build a resume inside the application

The resume builder functionality is represented by `ResumeBuilderController` and corresponding routes in the application.

---

# 🔍 Resume Parsing

One of the core components of Smart Resume Analyzer is its resume parsing system.

The parser converts extracted resume text into structured information.

The parser identifies common resume sections such as:

* Summary
* Profile
* Objective
* Work Experience
* Employment
* Professional Experience
* Education
* Academic Background
* Skills
* Technical Skills
* Core Skills
* Projects
* Portfolio
* Certifications
* Certificates
* Licenses

The parser normalizes section headings and maps different naming conventions into a consistent internal representation.

For example, these headings can represent the same conceptual section:

```text
Experience
Work Experience
Employment
Professional Experience
```

Instead of treating them as unrelated sections, the parser maps them to the internal `experience` category.

This makes the parsing process more tolerant of different resume writing styles.

---

# 📑 Document Text Extraction

The application includes a dedicated `ResumeTextExtractor` service.

This service is responsible for reading uploaded files and extracting readable text before the parser processes that text.

The current implementation supports extraction paths for:

* `.txt`
* `.docx`
* `.pdf`

The service uses:

* Laravel Storage
* `Smalot\PdfParser`
* PHP `ZipArchive`

for document processing.

---

## TXT Extraction

Text files are processed directly and normalized before being passed to the parser.

This provides a simple path for plain-text resumes.

---

## DOCX Extraction

DOCX files are ZIP-based Office documents.

The application checks for the PHP ZIP extension and reads relevant XML files from the DOCX archive.

The extractor looks at:

```text
word/document.xml
word/footnotes.xml
word/endnotes.xml
```

The extracted XML content is then processed to obtain readable text.

---

## PDF Extraction

PDF resumes are processed using the `Smalot\PdfParser` package.

The application retrieves the uploaded file through Laravel's configured storage disk and passes the PDF content through the parser.

This allows text-based PDF resumes to be converted into text that can subsequently be analyzed.

---

# 🖼️ Image-Only PDF Handling

Not every PDF contains selectable text.

Some resumes are scanned documents or PDFs containing only images.

The application detects this situation and can return an `image_only` parsing status.

The current implementation communicates that OCR would be required for such documents because there is no directly readable text available.

This is an important distinction because text extraction and OCR are different processing problems.

A normal PDF parser can extract embedded text, but it cannot automatically understand text that exists only inside an image.

---

# 🧠 AI Resume Analysis

After a resume has been parsed and readable text is available, the application can send the extracted content to an AI analysis service.

The AI integration is implemented through:

```text
app/Services/GroqAiService.php
```

The service communicates with Groq's chat-completion API.

The application first validates that:

1. A Groq API key exists.
2. The resume has already been parsed.
3. Extracted resume text is available.

If these conditions are not met, the service returns an appropriate application-level error.

---

# 🤖 Groq AI Integration

The project uses a dedicated service class instead of putting AI API calls directly inside a controller.

This is a good architectural decision because it separates:

* HTTP request handling
* Resume management
* AI communication
* AI response processing

The `GroqAiService` reads configuration values for:

* API key
* Model
* Base URL
* Timeout

It then makes an authenticated HTTP request to the configured chat-completion endpoint.

The request is configured to request JSON output.

The service uses a low temperature setting to encourage more consistent structured responses.

The AI response is decoded into a PHP array and stored in the corresponding AI analysis record.

---

# 📊 AI Analysis Output

The AI prompt asks the model to return a structured JSON object containing:

```json
{
  "score": 0,
  "strengths": [],
  "weaknesses": [],
  "missing_sections": [],
  "next_actions": []
}
```

The actual project expects these conceptual categories:

### Score

A numerical evaluation from 0 to 100.

### Strengths

Positive aspects detected in the resume.

### Weaknesses

Areas that could be improved.

### Missing Sections

Important resume sections that may not be present.

### Next Actions

Recommended improvements that the user can take.

This structured output makes the AI response easier to store, render, and consume programmatically than a large block of unstructured generated text.

---

# 🔄 AI Analysis Workflow

The analysis flow can be summarized as:

```text
Resume Upload
     ↓
File Storage
     ↓
Text Extraction
     ↓
Resume Parsing
     ↓
Extracted Resume Text
     ↓
AI Analysis Request
     ↓
Groq API
     ↓
Structured JSON Response
     ↓
AI Analysis Database Record
     ↓
Dashboard / Resume Workspace
```

This separation makes the system easier to maintain because document extraction and AI analysis are independent stages.

---

# 🧾 AI Analysis Storage

AI analysis results are not simply displayed and discarded.

The application has an `AiAnalysis` model and stores analysis-related information in the database.

The Groq service records values including:

* Provider
* Model
* Result
* Score
* Input token count
* Output token count
* Completion timestamp
* Analysis status

The related resume also records when it was most recently analyzed.

This provides the foundation for historical analysis and future analytics.

---

# 📚 Resume Versioning

Resume editing is inherently iterative.

A candidate may create:

```text
Resume v1
   ↓
Resume v2
   ↓
Resume v3
   ↓
Job-specific Resume
```

Smart Resume Analyzer includes resume version support to accommodate this workflow.

The application exposes routes for:

* Creating versions
* Updating versions
* Viewing resume previews
* Duplicating resumes

This means users can experiment with resume changes without necessarily destroying their previous version.

---

# 🎯 Primary Resume

Users can maintain multiple resumes while identifying one as their primary resume.

The application uses the primary resume as the default resume in relevant workspace screens.

The routing logic loads resumes ordered by primary status and then by recency, allowing the workspace to determine the most relevant resume for the user.

This is useful for candidates who maintain separate resumes for:

* Software development
* Data roles
* Internships
* Full-time positions
* Different industries
* Different job descriptions

---

# 💼 Job Application Tracking

Smart Resume Analyzer includes a job-tracking workspace.

Users can manage job applications directly from the application.

The system provides functionality for:

* Creating job records
* Updating job records
* Deleting job records
* Adding job contacts
* Removing job contacts
* Uploading job attachments
* Downloading job attachments
* Deleting job attachments

This allows users to maintain more than just a resume.

They can organize the broader job-search process in the same workspace.

---

# 👥 Job Contacts

Job applications can be associated with contacts.

A contact may represent a person involved in the recruitment process, such as:

* Recruiter
* Hiring manager
* Referral
* HR representative
* Professional connection

The application provides routes for adding and deleting job contacts.

This creates a more complete job-search tracking system.

---

# 📎 Job Attachments

The job tracker supports attachments associated with job records.

Attachments can be:

* Uploaded
* Downloaded
* Deleted

This can be useful for storing supporting documents related to a particular application.

Examples could include:

* Job descriptions
* Supporting documents
* Application-related files
* Notes
* Reference material

---

# 🎤 Interview Preparation

Interview preparation is another major part of the career workspace.

The application provides an interview section where users can:

* Create interview sessions
* Save interview responses
* Complete interview sessions
* Delete interview sessions

This allows users to track interview preparation and practice as part of their career workflow.

---

# 🧪 Interview Practice Workflow

A typical workflow can look like:

```text
Create Interview Session
        ↓
Practice Questions
        ↓
Write / Save Responses
        ↓
Review Responses
        ↓
Complete Interview Session
        ↓
Track Progress
```

The interview functionality is connected to the broader workspace rather than existing as an isolated feature.

---

# 🛠️ Skills Management

The application also provides a dedicated skills section.

Users can:

* Add skills
* Remove skills
* Download skill certificates

This provides a structured way to maintain information about professional capabilities.

Skills can complement resume analysis by helping users identify what they already know and what they may want to improve.

---

# 🏆 Skill Certificates

Skills can be associated with certificate-related evidence.

The application includes functionality for downloading skill certificates.

This can help users maintain evidence of professional development alongside their career profile.

---

# 🎯 Career Goals

The platform includes career-goal functionality.

Users can:

* Create goals
* Update goals
* Delete goals

Career goals provide a way to turn resume improvement and job searching into a more structured development process.

Potential goals can include:

* Improve resume quality
* Learn a new technology
* Complete a certification
* Apply for a target number of positions
* Prepare for interviews
* Build portfolio projects

---

# 💻 Portfolio Management

The application includes a portfolio section.

Users can:

* Add projects
* Remove projects

Portfolio projects can complement the information contained in the resume.

This is especially useful for technical candidates whose projects demonstrate practical experience.

---

# 📊 Dashboard

The dashboard acts as a central workspace.

The application has a dedicated dashboard route and loads workspace-related information based on the authenticated user.

The system also loads the user's primary resume and recent completed AI analyses for workspace screens.

A centralized dashboard makes it easier for users to see their career information without navigating through completely separate applications.

---

# 📈 Analytics

The project includes a dedicated analytics screen.

Analytics can serve as a foundation for tracking:

* Resume activity
* AI analysis history
* Job-search activity
* Interview progress
* Skills
* Career goals
* Portfolio development

Because AI analysis results and timestamps are stored, the architecture also provides useful data for future analytical features.

---

# 👤 Profile Management

Users have a dedicated profile screen.

The workspace routes include functionality for updating profile information.

This allows the career workspace to maintain user information separately from individual resumes.

---

# ⚙️ Settings

The application includes a settings area for user-specific configuration.

The project provides dedicated settings routing and workspace handling.

This gives the application a natural location for future user preferences and configuration options.

---

# 🔐 Authentication

Smart Resume Analyzer includes a complete authentication workflow.

The authentication controller handles functionality including:

* Login
* Registration
* Logout
* Password reset
* Password reset requests

The application also supports email verification.

Guest-only routes protect login and registration pages, while authenticated routes protect the user's workspace.

---

# 📧 Email Verification

After authentication, users can verify their email address.

The application includes:

* Verification notice
* Verification link handling
* Verification notification resend

The verification route uses signed requests and throttling to prevent uncontrolled notification requests.

---

# 🧭 Onboarding

The application contains an onboarding workflow.

Users are directed through onboarding before accessing certain workspace screens.

The routing layer checks whether onboarding has been completed and redirects users to the onboarding page when required.

This creates a more controlled first-time user experience.

---

# 🏗️ Application Architecture

Smart Resume Analyzer follows a Laravel MVC-oriented architecture with dedicated service classes.

At a high level:

```text
                    ┌─────────────────────┐
                    │       Browser       │
                    └──────────┬──────────┘
                               │
                               ▼
                    ┌─────────────────────┐
                    │     Laravel Routes  │
                    └──────────┬──────────┘
                               │
                               ▼
                    ┌─────────────────────┐
                    │     Controllers     │
                    └──────────┬──────────┘
                               │
              ┌────────────────┼────────────────┐
              │                │                │
              ▼                ▼                ▼
        ┌───────────┐   ┌──────────────┐  ┌─────────────┐
        │  Models   │   │   Services   │  │ Validation  │
        └─────┬─────┘   └──────┬───────┘  └─────────────┘
              │                 │
              ▼                 ▼
        ┌─────────────┐   ┌───────────────┐
        │  Database   │   │ External APIs │
        └─────────────┘   └───────┬───────┘
                                  │
                                  ▼
                              Groq AI
```

This architecture keeps different responsibilities separated.

---

# 🧱 Technology Stack

## Backend

* PHP
* Laravel 12
* Laravel Eloquent
* Laravel HTTP Client
* Laravel Storage
* Laravel Authentication
* Laravel Routing
* Laravel Migrations

The project's Composer configuration requires PHP `^8.2` and Laravel `^12.0`.

---

# 🎨 Frontend

The frontend build system uses:

* Vite
* Tailwind CSS
* Laravel Vite Plugin
* Axios

The project uses Tailwind CSS 4 and Vite 7 according to the current `package.json`.

---

# 📄 Document Processing

The project uses:

* `smalot/pdfparser`
* PHP `ZipArchive`

for document text extraction.

---

# 🤖 Artificial Intelligence

AI-powered resume analysis is implemented using:

* Groq API
* HTTP API requests
* Structured JSON responses

The AI integration is isolated in `GroqAiService`, making it easier to modify the provider or model in the future.

---

# 🗄️ Database

The project uses Laravel's database abstraction and migration system.

The repository contains migrations for:

* Users
* Cache
* Jobs
* SmartCV workspace tables
* Resume compatibility information
* Job tracker workflow
* Workspace normalization
* Skill evidence
* Interview practice

This demonstrates that the application has evolved through multiple stages of feature development.

---

# 📦 Composer Dependencies

The main runtime dependencies include:

```text
PHP ^8.2
Laravel Framework ^12.0
Laravel Tinker ^2.10
Smalot PDF Parser ^2.12
```

Development dependencies include tooling for:

* Testing
* Code formatting
* Debugging
* Local development
* Faker
* Laravel Sail

These dependencies are defined in `composer.json`.

---

# 📦 NPM Dependencies

The frontend development dependencies include:

```text
Tailwind CSS
Tailwind Vite Plugin
Vite
Laravel Vite Plugin
Axios
Concurrently
```

The project uses Vite for frontend asset development and production builds.

---

# 🗂️ Project Structure

The repository follows a standard Laravel structure with additional organization for resume-specific services and workspace functionality.

```text
smart-resume-analyzer/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │
│   ├── Models/
│   │
│   ├── Providers/
│   │
│   └── Services/
│
├── bootstrap/
│
├── config/
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── public/
│
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│
├── routes/
│   ├── console.php
│   └── web.php
│
├── storage/
│
├── tests/
│   ├── Feature/
│   └── Unit/
│
├── .env.example
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json
├── phpunit.xml
├── vite.config.js
└── README.md
```

The current repository contains these major directories and configuration files.

---

# 📁 Important Directories

## `app/`

Contains the core application logic.

It includes:

* Controllers
* Models
* Services
* Providers

---

## `app/Http/Controllers`

Controllers coordinate HTTP requests and application operations.

The project currently contains controllers for:

* AI analysis
* Authentication
* Onboarding
* Resume management
* Resume building
* Resume parsing
* Resume versions
* Workspace operations

---

# 🎮 Controllers

## `AuthController`

Responsible for authentication-related workflows.

Responsibilities include:

* Login
* Registration
* Logout
* Password reset
* Password reset email handling

---

## `AiAnalysisController`

Coordinates requests related to AI resume analysis.

The controller acts as the HTTP layer while the actual external AI communication is handled by the service layer.

---

## `OnboardingController`

Handles onboarding pages and onboarding submission.

---

## `ResumeController`

Handles core resume operations.

Typical responsibilities include:

* Listing resumes
* Creating resumes
* Viewing resumes
* Updating resumes
* Deleting resumes
* Selecting a primary resume
* Downloading resumes

---

## `ResumeBuilderController`

Handles the resume-builder workflow.

It supports:

* Builder creation
* Builder storage
* Editing
* Updating
* Duplication
* Version creation
* Preview
* DOCX export

---

## `ResumeParseController`

Coordinates resume parsing requests.

It connects uploaded resume records with the document extraction and parsing pipeline.

---

## `ResumeVersionController`

Handles updates to stored resume versions.

---

## `WorkspaceController`

Acts as a major controller for workspace-oriented features.

It handles operations related to:

* Dashboard
* Jobs
* Job contacts
* Job attachments
* Interviews
* Interview responses
* Skills
* Skill certificates
* Goals
* Portfolio projects
* Profile

The route structure shows that a substantial portion of the career workspace is coordinated through this controller.

---

# 🧠 Services

The project contains dedicated services for resume processing and AI integration.

Current service files include:

```text
app/Services/
│
├── GroqAiService.php
├── ResumeParser.php
└── ResumeTextExtractor.php
```

---

# 📄 ResumeTextExtractor

The `ResumeTextExtractor` service is responsible for converting uploaded document files into readable text.

Its workflow includes:

```text
Stored Resume
     ↓
Detect Extension
     ↓
Choose Extraction Strategy
     ↓
Extract Text
     ↓
Normalize
     ↓
Return Parsing Status
```

It supports text extraction for TXT, DOCX, and PDF files and identifies image-only PDFs as a separate case.

---

# 🧩 ResumeParser

The `ResumeParser` converts extracted text into structured resume information.

The parser returns fields including:

```text
contact
summary
work_experience
education
skills
projects
certificates
raw_text
```

The parser also recognizes aliases for common resume section headings.

This makes the application more flexible when users format resumes differently.

---

# 🤖 GroqAiService

The `GroqAiService` is responsible for communication with the Groq API.

Its responsibilities include:

1. Reading configuration.
2. Validating the API key.
3. Validating parsed resume content.
4. Preparing the AI prompt.
5. Calling the Groq endpoint.
6. Handling the HTTP response.
7. Decoding JSON.
8. Updating the analysis record.
9. Recording token usage.
10. Updating the resume's analysis timestamp.

---

# 🧱 Models

The application contains models representing the major career entities.

Current models include:

```text
AiAnalysis
CareerGoal
CareerProfile
InterviewSession
JobApplication
JobAttachment
JobContact
PortfolioProject
Resume
ResumeVersion
Skill
User
```

These models provide the data layer for the application's career workspace.

---

# 🗃️ Database Design

The database is structured around the user's career workspace.

Conceptually:

```text
User
 │
 ├── Career Profile
 │
 ├── Resumes
 │    ├── Resume Versions
 │    └── AI Analyses
 │
 ├── Job Applications
 │    ├── Job Contacts
 │    └── Job Attachments
 │
 ├── Interview Sessions
 │
 ├── Skills
 │
 ├── Career Goals
 │
 └── Portfolio Projects
```

This relationship structure allows different parts of the career journey to remain connected.

---

# 🛣️ Routes

The application uses Laravel's web routing system.

The route file defines separate areas for:

* Guest authentication
* Authenticated users
* Verified users
* Onboarding
* Resumes
* Resume builder
* Resume parsing
* AI analysis
* Dashboard
* Jobs
* Interviews
* Skills
* Insights
* Goals
* Portfolio
* Analytics
* Profile
* Settings
* Help
* Privacy
* Terms

---

# 🔐 Route Protection

The routes are divided into logical middleware groups.

Guest users can access authentication pages.

Authenticated users can access general authenticated functionality.

Verified users can access the main career workspace.

This prevents unauthenticated users from directly accessing private career data.

---

# 🚦 Rate Limiting

The application applies request throttling to sensitive operations.

For example:

```text
Login
Registration
Password reset
Email verification
Resume creation
Resume parsing
AI analysis
```

The AI analysis endpoint has a dedicated throttle, helping reduce accidental or excessive external AI API requests.

---

# 🔄 Resume Processing Pipeline

The complete resume processing process can be represented as:

```text
                    User
                     │
                     ▼
              Upload Resume
                     │
                     ▼
              Store File
                     │
                     ▼
         ResumeTextExtractor
                     │
          ┌──────────┼──────────┐
          │          │          │
          ▼          ▼          ▼
         PDF        DOCX       TXT
          │          │          │
          └──────────┼──────────┘
                     │
                     ▼
             Extracted Text
                     │
                     ▼
              ResumeParser
                     │
                     ▼
           Structured Resume
                     │
          ┌──────────┴──────────┐
          │                     │
          ▼                     ▼
      Store Data          AI Analysis
                                │
                                ▼
                            Groq API
                                │
                                ▼
                         AI Result JSON
                                │
                                ▼
                       Store AI Analysis
```

This pipeline keeps file processing and AI processing as separate stages.

---

# 🧠 Parsing Pipeline

The parser begins by splitting the extracted resume text into individual lines.

It then:

1. Trims whitespace.
2. Removes empty lines.
3. Detects section headings.
4. Groups lines under the relevant section.
5. Extracts contact information.
6. Generates a summary.
7. Separates skills.
8. Returns structured data.

The implementation uses aliases for multiple common section names.

---

# 📇 Contact Information

The parser attempts to identify contact-related information from resume text.

The resulting structure includes:

```text
name
email
phone
links
```

This gives the application structured contact data rather than leaving everything as raw text.

---

# 📚 Resume Sections

The parser recognizes the following conceptual sections:

### Summary

Professional summary or objective.

### Experience

Employment and professional experience.

### Education

Academic history.

### Skills

Technical and general skills.

### Projects

Projects and portfolio work.

### Certificates

Certifications, certificates, and licenses.

This makes the extracted resume data more useful for both display and AI processing.

---

# 🧪 AI Prompt Design

The AI service intentionally limits the extracted resume text before sending it to the AI model.

The service also asks the AI to return JSON rather than unrestricted natural-language output.

This has several advantages:

* Easier database storage
* Easier frontend rendering
* Predictable response structure
* Easier future analytics
* Reduced parsing complexity

The current prompt explicitly asks for a score, strengths, weaknesses, missing sections, and next actions.

---

# 📈 AI Score

The AI analysis uses a score ranging from:

```text
0 → 100
```

The score is stored with the analysis result when returned by the model.

It should be understood as an AI-generated evaluation rather than an objective or universal measure of resume quality.

Different recruiters, industries, roles, and applicant tracking systems can evaluate resumes differently.

---

# 🧠 Strengths

The strengths section is intended to identify parts of the resume that are already effective.

Examples may include:

* Clear professional summary
* Relevant technical skills
* Strong project descriptions
* Good experience structure
* Relevant education
* Clear career direction

The actual output depends on the resume content and AI model.

---

# ⚠️ Weaknesses

The weaknesses section highlights areas that could be improved.

Potential categories include:

* Weak wording
* Missing information
* Poor organization
* Insufficient detail
* Lack of measurable achievements
* Generic descriptions
* Missing role-specific information

The AI determines the actual weaknesses based on the supplied resume text.

---

# ❌ Missing Sections

The AI can identify sections that may be absent.

For example:

```text
Missing Projects
Missing Certifications
Missing Professional Summary
Missing Skills Section
```

This helps users identify structural gaps.

---

# 🚀 Next Actions

The AI output also includes actionable recommendations.

Rather than simply telling users that something is wrong, the system can provide a list of improvements that should be performed next.

This creates a more practical feedback loop:

```text
Analyze
   ↓
Identify Problems
   ↓
Recommend Actions
   ↓
Improve Resume
   ↓
Create New Version
   ↓
Analyze Again
```

---

# 🔁 Iterative Resume Improvement

One of the strongest architectural ideas in the project is the possibility of repeated analysis.

A user can:

```text
Upload Resume
      ↓
Analyze
      ↓
Review Feedback
      ↓
Edit Resume
      ↓
Create Version
      ↓
Analyze Again
      ↓
Compare Progress
```

Because the application stores resume versions and AI analysis records, it provides a foundation for iterative resume optimization.

---

# 🧑‍💻 Frontend Architecture

The frontend is integrated into Laravel's application structure.

The project uses:

* Blade views
* CSS resources
* JavaScript resources
* Vite
* Tailwind CSS

The `resources` directory contains:

```text
resources/
├── css/
├── js/
└── views/
```

---

# ⚡ Vite

Vite handles frontend development and production asset compilation.

The project defines:

```bash
npm run dev
```

for development and:

```bash
npm run build
```

for production asset compilation.

---

# 🎨 Tailwind CSS

Tailwind CSS is used for styling the application.

The project currently uses Tailwind CSS 4 and its Vite integration.

This provides a utility-first approach to building the user interface.

---

# 🌐 Axios

Axios is included as a frontend dependency.

It can be used for HTTP communication between frontend components and backend endpoints.

---

# 🧪 Testing

The repository includes both:

```text
tests/Feature
tests/Unit
```

along with the project's base test case.

This gives the project a foundation for:

* Feature testing
* Unit testing
* Regression testing
* Service-level testing

---

# ▶️ Installation

## Requirements

Before installing the project, make sure the development environment contains:

* PHP 8.2 or newer
* Composer
* Node.js
* npm
* A supported database
* PHP ZIP extension
* PHP extensions required by Laravel
* A Groq API key for AI analysis

The project specifically requires PHP `^8.2`, Laravel `^12.0`, and the PHP ZIP extension is required for DOCX extraction.

---

# 📥 Clone the Repository

```bash
git clone https://github.com/Vipul2907/smart-resume-analyzer.git
cd smart-resume-analyzer
```

---

# 📦 Install PHP Dependencies

Run:

```bash
composer install
```

---

# 📦 Install Frontend Dependencies

Run:

```bash
npm install
```

---

# ⚙️ Configure Environment

Create the environment file:

```bash
cp .env.example .env
```

Then generate the Laravel application key:

```bash
php artisan key:generate
```

---

# 🗄️ Configure Database

Update the database settings inside `.env`.

For example:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_resume_analyzer
DB_USERNAME=root
DB_PASSWORD=
```

Use values appropriate for your local environment.

---

# 🤖 Configure Groq

The AI service requires a Groq API key.

Add the required API key to your environment configuration.

For example:

```env
GROQ_API_KEY=your_groq_api_key
```

The application explicitly checks for the configured Groq API key before attempting analysis.

Do not commit your real API key to GitHub.

---

# 🗃️ Run Migrations

Run:

```bash
php artisan migrate
```

The repository includes multiple migrations responsible for creating and evolving the application's workspace database.

---

# 🧹 Clear Configuration Cache

If environment variables are not being detected correctly, run:

```bash
php artisan config:clear
```

You can also clear the application cache when troubleshooting.

---

# 🖥️ Run Laravel Development Server

Start the backend:

```bash
php artisan serve
```

The application will normally be available at:

```text
http://127.0.0.1:8000
```

---

# 🎨 Run Vite Development Server

In another terminal:

```bash
npm run dev
```

Vite will watch frontend assets and rebuild them during development.

---

# 🚀 Recommended Development Command

The project includes a Composer development script that can run multiple processes together.

The configured development workflow starts:

* Laravel server
* Queue listener
* Laravel log output
* Vite

using `concurrently`.

Run:

```bash
composer run dev
```

This is convenient because you do not have to manually start each development process.

---

# 🧪 Run Tests

The project provides a Composer test script.

Run:

```bash
composer run test
```

The configured test script clears configuration and then runs:

```bash
php artisan test
```

You can also run:

```bash
php artisan test
```

directly.

---

# 🏗️ Production Build

Build frontend assets using:

```bash
npm run build
```

This executes:

```bash
vite build
```

according to the project's current package configuration.

---

# 🧰 Development Setup Shortcut

The project also includes a Composer `setup` script.

Its configured workflow includes:

```text
composer install
.env creation
application key generation
database migration
npm install
npm run build
```

Therefore, after cloning the repository, the setup workflow can be initiated with:

```bash
composer run setup
```

Make sure your database and environment configuration are appropriate before running migrations in your environment.

---

# 📂 Storage

Uploaded resumes and job-related files are handled through Laravel's storage abstraction.

The resume text extractor retrieves files using the storage disk associated with each resume.

This design makes the application less dependent on a single storage implementation.

---

# 🔒 Security Considerations

Resume files contain potentially sensitive information.

They may include:

* Names
* Email addresses
* Phone numbers
* Employment history
* Education
* Skills
* Portfolio links
* Personal information

Therefore, production deployments should treat uploaded resumes and job attachments as sensitive user data.

---

# 🔐 Environment Variables

Never commit secrets such as:

```text
GROQ_API_KEY
Database passwords
Application secrets
Private credentials
```

to source control.

Use `.env` for local configuration and a secure secrets mechanism for production deployments.

The repository already provides `.env.example` as an environment configuration template.

---

# 🚦 Request Throttling

The application uses Laravel throttling for several sensitive endpoints.

Examples include:

```text
Login
Registration
Password reset
Email verification
Resume creation
Resume parsing
AI analysis
```

The AI analysis endpoint is particularly important because each request can result in an external AI API call.

---

# 🛡️ AI Safety

AI-generated resume feedback should be treated as assistance rather than absolute truth.

The AI may misunderstand:

* Job requirements
* Industry terminology
* Career experience
* Skills
* Context
* Resume formatting

Users should review AI recommendations before applying them.

---

# 📄 Supported Resume Formats

The current extraction layer includes support for:

```text
TXT
DOCX
PDF
```

The extractor selects its processing strategy based on the uploaded file extension.

---

# ⚠️ Image-Based Resume Limitation

Scanned or image-only PDF resumes may not contain machine-readable text.

The current implementation detects image-only PDF cases and reports that OCR would be required for extraction.

This means a future OCR integration could significantly expand document compatibility.

---

# 🔮 Future Improvements

The current architecture provides a strong foundation for additional features.

Possible future improvements include:

## OCR Support

Add OCR for:

* Scanned PDFs
* Image resumes
* Photograph-based resumes

Possible technologies could include:

* Tesseract
* Cloud OCR services
* Document AI systems

---

## 📊 Resume Comparison

Allow users to compare:

```text
Resume Version 1
vs.
Resume Version 2
```

and visualize changes in:

* Score
* Skills
* Sections
* Strengths
* Weaknesses
* Recommendations

---

## 🎯 Job Description Matching

Allow users to paste or upload a job description and calculate how well a selected resume matches it.

Possible output:

```text
Overall Match
Keyword Match
Missing Skills
Relevant Experience
Suggested Changes
```

---

## 🧠 Role-Specific AI Analysis

Allow users to select a target role such as:

```text
Software Engineer
Frontend Developer
Backend Developer
Data Analyst
Data Scientist
Product Manager
UI/UX Designer
```

and customize the AI analysis around that role.

---

## 📈 Historical AI Score Tracking

Store analysis scores over time and visualize:

```text
Version 1 → 61
Version 2 → 72
Version 3 → 81
Version 4 → 89
```

This would turn resume improvement into a measurable process.

---

## 🧪 Automated Resume Testing

Add automated tests for:

* PDF extraction
* DOCX extraction
* Section detection
* Email detection
* Phone detection
* Skills parsing
* AI response validation
* Resume versioning
* Job tracking

---

## 🌎 Multi-Language Support

Expand parsing and AI analysis beyond English.

Possible languages could include:

* Hindi
* Gujarati
* Spanish
* French
* German
* Portuguese
* Arabic

---

## ☁️ Cloud Storage

The current storage abstraction makes it possible to consider cloud storage providers in future deployments.

Potential options include:

* Amazon S3
* Google Cloud Storage
* Azure Blob Storage

---

## 🔔 Notifications

Future versions could notify users about:

* Interview dates
* Application deadlines
* Follow-ups
* Career goals
* Resume review reminders

---

## 📱 Progressive Web App

The application could eventually be enhanced with PWA capabilities to provide a more app-like mobile experience.

---

# 🧪 Development Practices

A maintainable project should continue to follow these principles:

### Separation of Responsibilities

Keep:

```text
Controllers
Models
Services
Views
Routes
```

focused on their respective responsibilities.

---

### Service-Oriented Processing

External integrations such as AI APIs should remain inside dedicated services.

The current `GroqAiService` is an example of this pattern.

---

### Configuration Through Environment

Secrets and environment-specific configuration should remain outside source code.

---

### Database Migrations

Database changes should be represented through Laravel migrations rather than manual production database changes.

---

### Automated Testing

New functionality should ideally be accompanied by appropriate feature or unit tests.

---

# 🤝 Contributing

Contributions are welcome.

If you would like to contribute:

### 1. Fork the repository

```bash
git clone https://github.com/Vipul2907/smart-resume-analyzer.git
```

### 2. Create a feature branch

```bash
git checkout -b feature/your-feature
```

### 3. Make your changes

Follow the existing Laravel project structure.

### 4. Run tests

```bash
php artisan test
```

### 5. Build frontend assets

```bash
npm run build
```

### 6. Commit changes

```bash
git add .
git commit -m "Add your feature"
```

### 7. Push the branch

```bash
git push origin feature/your-feature
```

### 8. Open a Pull Request

Explain:

* What changed
* Why it changed
* How it works
* How it was tested

---

# 🐛 Reporting Issues

If you discover a bug, provide as much information as possible.

A useful issue report should include:

* Operating system
* PHP version
* Node.js version
* Browser
* Database
* Steps to reproduce
* Expected behavior
* Actual behavior
* Error message
* Relevant logs

Avoid publishing private API keys, passwords, resume files, or other sensitive information in public issues.

---

# 🔍 Troubleshooting

## Groq analysis does not work

Check:

```env
GROQ_API_KEY=...
```

Also make sure the resume has already been successfully parsed.

The AI service intentionally refuses to send a resume for analysis if extracted text is unavailable.

---

## DOCX parsing fails

Make sure the PHP ZIP extension is installed and enabled.

The document extractor explicitly requires `ZipArchive` for DOCX processing.

---

## PDF parsing returns no content

Make sure the PDF contains selectable text.

If the PDF is a scanned image, the current extraction layer can identify it as an image-only PDF but does not currently perform OCR.

---

## Environment changes are not detected

Run:

```bash
php artisan config:clear
```

Then restart the Laravel development server.

---

## Frontend assets are not updating

Make sure Vite is running:

```bash
npm run dev
```

For a production build:

```bash
npm run build
```

---

# 🧭 Typical User Journey

A typical user journey can look like this:

```text
                    ┌───────────────┐
                    │   Register    │
                    └───────┬───────┘
                            │
                            ▼
                    ┌───────────────┐
                    │ Verify Email  │
                    └───────┬───────┘
                            │
                            ▼
                    ┌───────────────┐
                    │   Onboarding  │
                    └───────┬───────┘
                            │
                            ▼
                    ┌───────────────┐
                    │   Dashboard   │
                    └───────┬───────┘
                            │
              ┌─────────────┼──────────────┐
              │             │              │
              ▼             ▼              ▼
          Build Resume   Upload Resume   Manage Career
              │             │              │
              │             ▼              │
              │       Parse Resume         │
              │             │              │
              │             ▼              │
              │        AI Analysis         │
              │             │              │
              └─────────────┼──────────────┘
                            │
                            ▼
                    ┌───────────────┐
                    │ Improve Resume│
                    └───────┬───────┘
                            │
                            ▼
                    ┌───────────────┐
                    │ New Version   │
                    └───────┬───────┘
                            │
                            ▼
                    ┌───────────────┐
                    │ Apply for Jobs│
                    └───────┬───────┘
                            │
                ┌───────────┼────────────┐
                │           │            │
                ▼           ▼            ▼
             Contacts   Interviews    Attachments
                │           │            │
                └───────────┼────────────┘
                            │
                            ▼
                    ┌───────────────┐
                    │ Career Growth │
                    │ Skills/Goals/ │
                    │   Portfolio   │
                    └───────────────┘
```

---

# 🏛️ Architectural Principles

The project can be understood through several important architectural principles.

## 1. Separation of Concerns

Controllers handle HTTP requests.

Services handle specialized operations.

Models represent application data.

Routes define application entry points.

Views handle presentation.

This makes the application easier to maintain.

---

## 2. Reusable Services

Resume extraction and parsing are isolated from controllers.

This means the same services can potentially be reused from:

* Web requests
* Console commands
* Queued jobs
* Automated tests
* Future APIs

---

## 3. External API Isolation

Groq communication is isolated inside:

```text
GroqAiService
```

This makes it possible to replace or extend AI providers without rewriting the entire application.

---

## 4. Structured AI Output

The AI response is requested in JSON format.

This makes AI-generated information more suitable for:

* Database storage
* UI rendering
* Analytics
* Validation
* Future integrations

---

# 🔄 Extensibility

The architecture can be extended in several directions.

For example, a future AI abstraction could support:

```text
AI Provider
    │
    ├── Groq
    ├── OpenAI
    ├── Gemini
    └── Local Model
```

Similarly, document extraction could evolve into:

```text
Document Processor
       │
       ├── PDF Parser
       ├── DOCX Parser
       ├── TXT Parser
       └── OCR Processor
```

This would allow the application to support more providers and document formats without changing the overall workflow.

---

# 📊 Data Flow

The core resume data flow is:

```text
File
 ↓
Storage
 ↓
Resume Record
 ↓
Text Extraction
 ↓
Extracted Text
 ↓
Resume Parser
 ↓
Structured Data
 ↓
AI Analysis
 ↓
AI Analysis Record
 ↓
User Dashboard
```

This design allows each stage to be independently improved.

---

# 🧠 What Makes the Project Different?

The application is not limited to:

> "Upload resume → receive score."

Instead, it attempts to build an entire career workflow around the resume.

The resume becomes connected to:

* AI feedback
* Resume versions
* Job applications
* Recruiter contacts
* Interview sessions
* Skills
* Certificates
* Career goals
* Portfolio projects
* Analytics

This creates a more complete career-management experience.

---

# 🔬 Technical Highlights

Some of the technically interesting aspects of the project include:

### Laravel 12 Architecture

The project is built on the latest major Laravel framework version currently defined in its Composer configuration.

### PHP 8.2+

The project uses modern PHP requirements.

### Dedicated Document Processing

Resume extraction is isolated into a dedicated service.

### Custom Resume Parser

The project does not simply store extracted text. It attempts to transform the text into meaningful resume sections.

### Groq Integration

AI analysis is implemented through a dedicated service and structured JSON response.

### Resume Versioning

The application supports iterative resume development.

### Career Workspace

Job applications, interviews, skills, goals, and portfolio information are integrated into the same platform.

### Request Throttling

Sensitive operations are protected using Laravel throttling.

### Database Migrations

The database schema is maintained through versioned migrations.

### Automated Testing Structure

The project contains both feature and unit test directories.

---

# 🧪 Example Resume Processing

Suppose a user uploads:

```text
john-doe-resume.pdf
```

The application can process it conceptually as follows:

```text
john-doe-resume.pdf
        │
        ▼
PDF Text Extraction
        │
        ▼
"John Doe
john@example.com
Software Engineer

EXPERIENCE
ABC Technologies
Software Developer

SKILLS
PHP, Laravel, JavaScript..."
        │
        ▼
Resume Parser
        │
        ├── Contact
        ├── Summary
        ├── Experience
        ├── Skills
        └── Other Sections
        │
        ▼
AI Analysis
        │
        ▼
{
  score: 82,
  strengths: [...],
  weaknesses: [...],
  missing_sections: [...],
  next_actions: [...]
}
```

The result can then become part of the user's resume workspace.

---

# 📌 Important Notes

## AI Results

AI analysis is advisory.

The generated score and recommendations should not be interpreted as guaranteed hiring outcomes.

---

## Resume Privacy

Users should avoid uploading documents containing information they do not want processed by external AI services.

Because the application sends extracted resume text to the configured AI provider, production deployments should clearly communicate their data-handling practices.

---

## API Costs

AI analysis may consume API credits depending on the configured Groq model and usage.

The application records token usage as part of the AI analysis record, which provides useful information for monitoring usage.

---

# 📈 Potential Product Roadmap

A possible roadmap for future releases:

## Phase 1 — Core Resume Platform

* Resume upload
* Resume parsing
* Resume builder
* Resume versions
* AI analysis

## Phase 2 — Job Search

* Job tracking
* Job contacts
* Attachments
* Application statuses
* Follow-up reminders

## Phase 3 — Career Development

* Skills
* Certifications
* Goals
* Portfolio

## Phase 4 — Advanced AI

* Job-description matching
* Skill-gap analysis
* Role-specific recommendations
* AI-generated resume improvements
* Interview question generation
* Personalized career recommendations

## Phase 5 — Intelligence & Analytics

* Historical score tracking
* Resume version comparison
* Application conversion analytics
* Interview success analytics
* Career progress dashboards

---

# 🌟 Project Vision

The long-term vision of Smart Resume Analyzer is to become a centralized AI-assisted career workspace.

Instead of using separate applications for:

```text
Resume
+
Resume Review
+
Job Tracker
+
Interview Preparation
+
Skills
+
Portfolio
+
Career Goals
```

the platform brings these workflows together.

The goal is to help users move from:

```text
"Do I have a good resume?"
```

to:

```text
"What should I improve?"
        ↓
"Which jobs should I apply for?"
        ↓
"Which resume version should I use?"
        ↓
"How should I prepare for the interview?"
        ↓
"What skills should I develop?"
        ↓
"How is my career progress improving?"
```

---

# 🏁 Conclusion

Smart Resume Analyzer is a full-stack Laravel application focused on modernizing the resume and career-management workflow.

The project combines:

* Resume creation
* Resume upload
* Document extraction
* Resume parsing
* AI-powered analysis
* Resume versioning
* Job application tracking
* Interview preparation
* Skills management
* Career goals
* Portfolio management
* Analytics
* Authentication
* Onboarding

The backend is structured around Laravel controllers, models, services, routes, migrations, and storage.

The document-processing layer separates extraction from parsing, while the AI layer isolates Groq API communication into a dedicated service.

This architecture provides a solid foundation for future improvements such as OCR, job-description matching, role-specific AI analysis, historical resume scoring, advanced analytics, cloud storage, notifications, and multi-language support.

The project demonstrates how modern web development, document processing, structured data, and generative AI can be combined into a practical career-focused application.

---

# 📚 Project Resources

**Repository**

[Smart Resume Analyzer — GitHub Repository](https://github.com/Vipul2907/smart-resume-analyzer?utm_source=chatgpt.com)

---

# 📄 License

This project should be used according to the license specified by the repository.

If you intend to distribute the project publicly, make sure an appropriate license file is included and that third-party dependencies and API usage comply with their respective licenses and terms.

---

# ⭐ Support the Project

If you find the project useful:

* ⭐ Star the repository
* 🐛 Report issues
* 💡 Suggest improvements
* 🔧 Submit pull requests
* 📢 Share the project with other developers

---

# 🙌 Final Note

Smart Resume Analyzer is designed around a simple idea:

> **A better resume is not just about getting a higher score. It is about understanding your career profile, identifying gaps, improving your presentation, and taking the next step with confidence.**

If you are interested in contributing, improving the AI analysis pipeline, adding OCR support, implementing job-description matching, or expanding the career workspace, contributions and ideas are welcome.
