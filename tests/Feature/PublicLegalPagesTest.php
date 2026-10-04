<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicLegalPagesTest extends TestCase
{
    public function test_privacy_and_terms_pages_are_available_to_guests(): void
    {
        $this->get(route('privacy'))->assertOk()->assertSee('Optional AI tools')->assertSee('Groq');
        $this->get(route('terms'))->assertOk()->assertSee('Terms of use')->assertSee('applicant-tracking-system score');
    }
}
