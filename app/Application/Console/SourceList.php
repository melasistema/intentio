<?php

declare(strict_types=1);

namespace Intentio\Application\Console;

final class SourceList
{
    /**
     * Prints, under an answer, the knowledge it was built from.
     *
     * @param array $result What CognitiveEngine::chat returned.
     */
    public static function write(array $result): void
    {
        if (empty($result['pinned']) && empty($result['retrieved'])) {
            fwrite(STDOUT, "Sources: none. Nothing in this space was close enough to the query, so the answer does not come from its knowledge." . PHP_EOL);
        } else {
            fwrite(STDOUT, "Sources:" . PHP_EOL);
            foreach ($result['pinned'] as $path) {
                fwrite(STDOUT, "  pinned  {$path}" . PHP_EOL);
            }
            foreach ($result['retrieved'] as $passage) {
                // The number is the similarity between the passage and the query, from 0 to 1
                fwrite(STDOUT, sprintf("  %.2f    %s" . PHP_EOL, $passage['score'], $passage['source']));
            }
        }

        if ($result['warning'] !== null) {
            fwrite(STDERR, "Warning: " . $result['warning'] . PHP_EOL);
        }
    }
}
