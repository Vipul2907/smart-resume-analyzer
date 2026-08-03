<?php

namespace App\Services;

class ResumeParser
{
    /**
     * @return array<string, mixed>
     */
    public function parse(string $text): array
    {
        $lines = collect(preg_split('/\R/', $text) ?: [])
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values();

        $sections = $this->sections($lines->all());
        $contact = $this->contact($text, $lines->first() ?: '');

        return [
            'contact' => $contact,
            'summary' => $this->summary($sections, $lines->all()),
            'work_experience' => $sections['experience'] ?? [],
            'education' => $sections['education'] ?? [],
            'skills' => $this->splitItems($sections['skills'] ?? []),
            'projects' => $sections['projects'] ?? [],
            'certificates' => $sections['certificates'] ?? [],
            'raw_text' => $text,
        ];
    }

    /**
     * @param  list<string>  $lines
     * @return array<string, list<string>>
     */
    private function sections(array $lines): array
    {
        $aliases = [
            'summary' => ['summary', 'profile', 'objective', 'professional summary'],
            'experience' => ['experience', 'work experience', 'employment', 'professional experience'],
            'education' => ['education', 'academic background'],
            'skills' => ['skills', 'technical skills', 'core skills'],
            'projects' => ['projects', 'portfolio'],
            'certificates' => ['certifications', 'certificates', 'licenses'],
        ];

        $current = 'summary';
        $sections = [];

        foreach ($lines as $line) {
            $normalized = strtolower(trim($line, " :\t"));
            $matched = collect($aliases)->search(fn (array $names) => in_array($normalized, $names, true));

            if (is_string($matched)) {
                $current = $matched;
                $sections[$current] ??= [];
                continue;
            }

            $sections[$current][] = $line;
        }

        return $sections;
    }

    /**
     * @param  array<string, list<string>>  $sections
     * @param  list<string>  $lines
     */
    private function summary(array $sections, array $lines): string
    {
        $summary = $sections['summary'] ?? array_slice($lines, 1, 4);

        return trim(implode("\n", array_slice($summary, 0, 5)));
    }

    /**
     * @return array{name: string|null, email: string|null, phone: string|null, links: list<string>}
     */
    private function contact(string $text, string $firstLine): array
    {
        preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $text, $email);
        preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $text, $phone);
        preg_match_all('/https?:\/\/[^\s]+|(?:linkedin|github)\.com\/[^\s]+/i', $text, $links);

        return [
            'name' => $firstLine !== '' && strlen($firstLine) < 80 ? $firstLine : null,
            'email' => $email[0] ?? null,
            'phone' => isset($phone[0]) ? trim($phone[0]) : null,
            'links' => $links[0] ?? [],
        ];
    }

    /**
     * @param  list<string>  $lines
     * @return list<string>
     */
    private function splitItems(array $lines): array
    {
        return collect($lines)
            ->flatMap(fn (string $line) => preg_split('/[,|;]|\x{2022}/u', $line) ?: [])
            ->map(fn (string $item) => trim($item, " \t-"))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
