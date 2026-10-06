<?php

declare(strict_types=1);

namespace Intentio\Tests\Unit;

use Intentio\Domain\Cognitive\PromptResolver;
use Intentio\Shared\Exceptions\IntentioException;
use Intentio\Tests\SpaceTestCase;

/**
 * How a prompt template is read: its front matter, its text, and the knowledge files it names.
 */
final class PromptResolverTest extends SpaceTestCase
{
    public function testATemplateWithoutFrontMatterIsAllContent(): void
    {
        $this->writeFile('prompts/default.md', "Answer from the context.\n\n{{QUERY}}");

        $prompt = (new PromptResolver())->resolve($this->space, 'default');

        $this->assertSame("Answer from the context.\n\n{{QUERY}}", $prompt['content']);
        $this->assertSame('', $prompt['instruction']);
        $this->assertFalse($prompt['render']);
        $this->assertSame([], $prompt['context_files']);
    }

    public function testFrontMatterIsReadAndLeftOutOfTheContent(): void
    {
        $this->writeFile('prompts/logo.md', "---\ninstruction: Describe the brand:\nrender: true\n---\n# Logo\n\n{{QUERY}}\n");

        $prompt = (new PromptResolver())->resolve($this->space, 'logo');

        $this->assertSame('Describe the brand:', $prompt['instruction']);
        $this->assertTrue($prompt['render']);
        $this->assertSame("# Logo\n\n{{QUERY}}", $prompt['content']);
    }

    public function testATemplateRendersOnlyWhenItSaysTrue(): void
    {
        $this->writeFile('prompts/absent.md', "---\ninstruction: Ask:\n---\n{{QUERY}}");
        $this->writeFile('prompts/false.md', "---\nrender: false\n---\n{{QUERY}}");
        $this->writeFile('prompts/yes.md', "---\nrender: yes\n---\n{{QUERY}}");

        $resolver = new PromptResolver();

        $this->assertFalse($resolver->resolve($this->space, 'absent')['render']);
        $this->assertFalse($resolver->resolve($this->space, 'false')['render']);
        $this->assertFalse($resolver->resolve($this->space, 'yes')['render']);
    }

    public function testATemplateNamesTheKeptImagesItUsesAndTheNameItsRenderIsKeptUnder(): void
    {
        $this->writeFile('prompts/page.md', "---\nrender: true\nuses: product, logo\nkeep_as: page\n---\n{{QUERY}}");
        $this->writeFile('prompts/plain.md', "---\nrender: true\n---\n{{QUERY}}");

        $resolver = new PromptResolver();

        $this->assertSame(['product', 'logo'], $resolver->resolve($this->space, 'page')['uses']);
        $this->assertSame('page', $resolver->resolve($this->space, 'page')['keep_as']);
        $this->assertSame([], $resolver->resolve($this->space, 'plain')['uses']);
        $this->assertNull($resolver->resolve($this->space, 'plain')['keep_as']);
    }

    public function testAKeptImageNameThatIsNotAFileNameIsAnError(): void
    {
        $this->writeFile('prompts/page.md', "---\nuses: ../logo\n---\n{{QUERY}}");

        $this->expectException(IntentioException::class);
        $this->expectExceptionMessage("names the kept image '../logo'");

        (new PromptResolver())->resolve($this->space, 'page');
    }

    public function testFrontMatterWithWindowsLineEndingsIsRead(): void
    {
        $this->writeFile('prompts/logo.md', "---\r\ninstruction: Describe the brand:\r\nrender: true\r\n---\r\n{{QUERY}}");

        $prompt = (new PromptResolver())->resolve($this->space, 'logo');

        $this->assertSame('Describe the brand:', $prompt['instruction']);
        $this->assertTrue($prompt['render']);
        $this->assertSame('{{QUERY}}', $prompt['content']);
    }

    public function testALineOfDashesInsideTheTextDoesNotStartFrontMatter(): void
    {
        $this->writeFile('prompts/default.md', "Rules:\n---\nrender: true\n---\n{{QUERY}}");

        $prompt = (new PromptResolver())->resolve($this->space, 'default');

        $this->assertFalse($prompt['render']);
        $this->assertStringContainsString('render: true', $prompt['content']);
    }

    public function testKnowledgeFilesNamedInTheTextArePinned(): void
    {
        $leanCanvas = $this->writeFile('knowledge/frameworks/lean_canvas.md', '# Lean Canvas');
        $redFlags = $this->writeFile('knowledge/red_flags.md', '# Red Flags');
        $this->writeFile('knowledge/unused.md', '# Unused');
        $this->writeFile('prompts/validate.md', "Use `lean_canvas.md` and the checklist in red_flags.md. Again: `lean_canvas.md`. Ignore `missing.md`.\n\n{{QUERY}}");

        $prompt = (new PromptResolver())->resolve($this->space, 'validate');

        $this->assertSame([$leanCanvas, $redFlags], $prompt['context_files']);
    }

    public function testAFileNamedWithItsFolderIsFoundInThatFolder(): void
    {
        $this->writeFile('knowledge/drafts/pricing.md', '# Draft');
        $final = $this->writeFile('knowledge/final/pricing.md', '# Final');
        $this->writeFile('prompts/price.md', "Use `final/pricing.md`.\n\n{{QUERY}}");

        $prompt = (new PromptResolver())->resolve($this->space, 'price');

        $this->assertSame([$final], $prompt['context_files']);
    }

    public function testAMissingTemplateIsAnError(): void
    {
        $this->expectException(IntentioException::class);
        $this->expectExceptionMessage("Prompt 'absent' not found");

        (new PromptResolver())->resolve($this->space, 'absent');
    }

    public function testTemplatesAreListedInAlphabeticalOrder(): void
    {
        $this->writeFile('prompts/validate_idea.md', 'a');
        $this->writeFile('prompts/default.md', 'b');
        $this->writeFile('prompts/craft_pitch.md', 'c');
        $this->writeFile('prompts/notes.txt', 'not a template');

        $this->assertSame(['craft_pitch', 'default', 'validate_idea'], (new PromptResolver())->listPromptKeys($this->space));
    }
}
