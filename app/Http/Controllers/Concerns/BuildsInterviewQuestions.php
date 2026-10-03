<?php

namespace App\Http\Controllers\Concerns;

trait BuildsInterviewQuestions
{
    private function interviewQuestions(string $type, string $role): array
    {
        $questions = match ($type) {
            'technical' => [
                "Which technical skill is most important for a {$role}, and how have you used it in real work?",
                'Describe a difficult technical problem you solved. How did you investigate it and measure the result?',
                'How do you make sure your work is reliable, maintainable, and understandable for your team?',
                'Tell me about a time you received a bug report or failure. What did you do first?',
                'What would you learn first during your first 30 days in this role?',
            ],
            'behavioral' => [
                'Tell me about a time you handled a difficult situation with a teammate. What was the outcome?',
                'Describe a project where you made a mistake. What did you learn and change afterwards?',
                'Give an example of a time you took ownership without being asked.',
                'How do you prioritise when several important tasks arrive at the same time?',
                "Why are you interested in a {$role} role now?",
            ],
            'hr' => [
                "Why are you interested in this {$role} opportunity?",
                'What kind of work environment helps you do your best work?',
                'What are you looking for in your next manager and team?',
                'How do you handle feedback that you do not initially agree with?',
                'What would make this role a successful next step for you?',
            ],
            'leadership' => [
                'Tell me about a time you influenced a decision without formal authority.',
                'How do you align people when priorities conflict?',
                'Describe how you helped another person grow or succeed.',
                'How do you communicate a difficult decision to stakeholders?',
                "What leadership habit would you bring to a {$role} team?",
            ],
            'case_study' => [
                "How would you break down an unfamiliar business problem in a {$role} case study?",
                'What facts would you collect before recommending a solution?',
                'How would you decide between two viable options?',
                'How would you explain your recommendation to a non-technical stakeholder?',
                'How would you measure whether your recommendation worked?',
            ],
            default => [
                "Tell me about yourself and the path that led you toward {$role}.",
                'What achievement are you most proud of, and how did you measure its impact?',
                'What kind of team and manager help you do your best work?',
                'What is one skill you are actively improving, and what is your learning plan?',
                'What questions would you ask us before accepting this role?',
            ],
        };

        return $questions;
    }

    private function isMeaningfulInterviewAnswer(string $answer): bool
    {
        $words = collect(preg_split('/\s+/', trim($answer)) ?: [])->filter();
        $letters = preg_replace('/[^a-z]/i', '', $answer) ?: '';
        $uniqueLetters = count(array_unique(str_split(strtolower($letters))));

        return $words->count() >= 8
            && $words->unique(fn (string $word) => strtolower($word))->count() >= 5
            && mb_strlen($answer) >= 45
            && $uniqueLetters >= 5;
    }
}
