<?php

declare(strict_types=1);

namespace Intentio\Tests\Unit;

use Intentio\Domain\Cognitive\CognitiveEngine;
use Intentio\Domain\Cognitive\IngestionService;
use Intentio\Domain\Cognitive\RetrievalService;
use Intentio\Domain\Cognitive\VectorStoreInterface;
use Intentio\Domain\Model\EmbeddingInterface;
use Intentio\Domain\Model\ImageRendererInterface;
use Intentio\Domain\Model\LLMInterface;
use Intentio\Infrastructure\Filesystem\FileProcessor;
use Intentio\Tests\SpaceTestCase;

/**
 * How the one text the model receives is built from the knowledge in scope, the template and the query.
 * No model runs here: the language model is replaced by one that keeps the prompt it was given.
 */
final class CognitiveEngineTest extends SpaceTestCase
{
    /** The prompt the engine last sent to the language model. */
    private string $sentPrompt = '';

    public function testWithoutATemplateOrKnowledgeTheQueryIsThePrompt(): void
    {
        $result = $this->engine()->chat($this->space, 'What is a hook?');

        $this->assertSame('What is a hook?', $this->sentPrompt);
        $this->assertSame([], $result['pinned']);
        $this->assertSame([], $result['retrieved']);
        $this->assertNull($result['warning']);
    }

    public function testTheKnowledgeComesBeforeTheTemplate(): void
    {
        $engine = $this->engine([$this->passage('The top three problems.', 'lean_canvas.md', ['Lean Canvas', 'Problem'], 0.61)]);

        $engine->chat($this->space, 'A cheese box for expats', "Validate this idea:\n{{QUERY}}");

        $this->assertSame(
            "Context:\n"
            . "### Retrieved Information\n"
            . "Source: lean_canvas.md > Lean Canvas > Problem\n"
            . "Content: The top three problems.\n"
            . "\n"
            . "Validate this idea:\n"
            . "A cheese box for expats",
            $this->sentPrompt
        );
    }

    public function testATemplatePlacesTheKnowledgeItself(): void
    {
        $engine = $this->engine([$this->passage('The top three problems.', 'lean_canvas.md', [], 0.61)]);

        $engine->chat($this->space, 'A cheese box', "Idea: {{QUERY}}\n\nFrameworks:\n{{CONTEXT}}\n\nBe brief.");

        $this->assertSame(
            "Idea: A cheese box\n\nFrameworks:\n### Retrieved Information\nSource: lean_canvas.md\nContent: The top three problems.\n\nBe brief.",
            $this->sentPrompt
        );
    }

    public function testATemplateWithoutAPlaceForTheQueryGetsItAtTheEnd(): void
    {
        $this->engine()->chat($this->space, 'A cheese box', 'Validate the idea below.');

        $this->assertSame("Validate the idea below.\n\nA cheese box", $this->sentPrompt);
    }

    public function testAPlaceholderWrittenInTheKnowledgeOrTheQueryIsLeftAsItIs(): void
    {
        $engine = $this->engine([$this->passage('A template marks the query with {{QUERY}}.', 'templates.md', [], 0.7)]);

        $engine->chat($this->space, 'What does {{CONTEXT}} do?', "{{CONTEXT}}\n\nQuestion: {{QUERY}}");

        $this->assertSame(
            "### Retrieved Information\nSource: templates.md\nContent: A template marks the query with {{QUERY}}.\n\nQuestion: What does {{CONTEXT}} do?",
            $this->sentPrompt
        );
    }

    public function testPinnedFilesAreGivenInFullBeforeTheRetrievedPassages(): void
    {
        $pinned = $this->writeFile('knowledge/frameworks/red_flags.md', "# Red Flags\n\n1. Nobody pays.\n");
        $engine = $this->engine([$this->passage('The top three problems.', 'lean_canvas.md', [], 0.61)]);

        $result = $engine->chat($this->space, 'A cheese box', '{{QUERY}}', [$pinned]);

        $this->assertSame(
            "Context:\n"
            . "### Knowledge Base\n"
            . "--- Content from red_flags.md ---\n"
            . "# Red Flags\n\n1. Nobody pays.\n"
            . "\n"
            . "### Retrieved Information\n"
            . "Source: lean_canvas.md\n"
            . "Content: The top three problems.\n"
            . "\n"
            . "A cheese box",
            $this->sentPrompt
        );
        $this->assertSame(['frameworks/red_flags.md'], $result['pinned']);
    }

    public function testPassagesOfAPinnedFileAreNotRetrieved(): void
    {
        $pinned = $this->writeFile('knowledge/frameworks/red_flags.md', '# Red Flags');

        $store = $this->createMock(VectorStoreInterface::class);
        $store->expects($this->once())
            ->method('findSimilar')
            ->with($this->space, [1.0], 5, 0.32, ['frameworks/red_flags.md'])
            ->willReturn([]);

        $this->engineWithStore($store)->chat($this->space, 'A cheese box', '{{QUERY}}', [$pinned]);
    }

    public function testTheResultNamesWhatTheAnswerWasBuiltFrom(): void
    {
        $engine = $this->engine([
            $this->passage('The top three problems.', 'frameworks/lean_canvas.md', ['Lean Canvas', 'Problem'], 0.61),
            $this->passage('Charge early.', 'pricing.md', [], 0.4),
        ]);

        $result = $engine->chat($this->space, 'A cheese box');

        $this->assertSame('the answer', $result['answer']);
        $this->assertSame(
            [
                ['source' => 'frameworks/lean_canvas.md > Lean Canvas > Problem', 'score' => 0.61],
                ['source' => 'pricing.md', 'score' => 0.4],
            ],
            $result['retrieved']
        );
    }

    public function testAPromptLargerThanTheContextWindowIsReported(): void
    {
        // 100 tokens are about 350 bytes
        $fits = $this->engine([], 100)->chat($this->space, str_repeat('a', 350));
        $tooLarge = $this->engine([], 100)->chat($this->space, str_repeat('a', 351));

        $this->assertNull($fits['warning']);
        $this->assertStringContainsString('about 101 tokens and the context window is 100', $tooLarge['warning']);
    }

    public function testTheAnswerIsHandedOverWhileItIsWritten(): void
    {
        $pieces = [];

        $this->engine()->chat($this->space, 'What is a hook?', '{{QUERY}}', [], function (string $text) use (&$pieces): void {
            $pieces[] = $text;
        });

        $this->assertSame(['the ', 'answer'], $pieces);
    }

    /**
     * An engine whose index returns the given passages for every query.
     */
    private function engine(array $passages = [], int $contextWindow = 32768): CognitiveEngine
    {
        $store = $this->createStub(VectorStoreInterface::class);
        $store->method('findSimilar')->willReturn($passages);

        return $this->engineWithStore($store, $contextWindow);
    }

    private function engineWithStore(VectorStoreInterface $store, int $contextWindow = 32768): CognitiveEngine
    {
        $embedding = $this->createStub(EmbeddingInterface::class);
        $embedding->method('embed')->willReturn([1.0]);

        // A language model that keeps the prompt and writes "the answer" in two pieces
        $llm = $this->createStub(LLMInterface::class);
        $llm->method('generate')->willReturnCallback(function (string $prompt, ?callable $onText = null): string {
            $this->sentPrompt = $prompt;
            if ($onText !== null) {
                $onText('the ');
                $onText('answer');
            }

            return 'the answer';
        });

        return new CognitiveEngine(
            $llm,
            new IngestionService(new FileProcessor(), $embedding, $store, 'test-embedding-model', ''),
            new RetrievalService($embedding, $store, 5, 0.32, ''),
            $store,
            $this->createStub(ImageRendererInterface::class),
            $contextWindow
        );
    }

    /**
     * A passage as the index returns it.
     */
    private function passage(string $content, string $path, array $headings, float $score): array
    {
        return [
            'content' => $content,
            'metadata' => ['relative_path' => $path, 'headings' => $headings],
            'score' => $score,
        ];
    }
}
