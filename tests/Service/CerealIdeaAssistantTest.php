<?php

namespace App\Tests\Service;

use App\Service\CerealIdeaAssistant;
use PHPUnit\Framework\TestCase;

class CerealIdeaAssistantTest extends TestCase
{
    private ?string $originalApiKey = null;

    protected function setUp(): void
    {
        $current = getenv('OPENAI_API_KEY');
        $this->originalApiKey = $current === false ? null : $current;

        putenv('OPENAI_API_KEY');
    }

    protected function tearDown(): void
    {
        if ($this->originalApiKey === null) {
            putenv('OPENAI_API_KEY');

            return;
        }

        putenv('OPENAI_API_KEY='.$this->originalApiKey);
    }

    public function testSuggestDraftIncludesSuggestedNameWhenNameMissing(): void
    {
        $assistant = new CerealIdeaAssistant();

        $result = $assistant->suggestDraft('', 'chocolate berry for kids');

        $this->assertArrayHasKey('suggestedName', $result);
        $this->assertNotSame('', trim((string) $result['suggestedName']));
    }

    public function testSuggestDraftOmitsSuggestedNameWhenNameProvided(): void
    {
        $assistant = new CerealIdeaAssistant();

        $result = $assistant->suggestDraft('Crunch Rocket', 'space themed breakfast');

        $this->assertArrayNotHasKey('suggestedName', $result);
    }
}
