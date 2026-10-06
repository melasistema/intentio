<?php

declare(strict_types=1);

/**
 * Prepares a release: works out the next version from the conventional commits since the last tag,
 * writes the release section of CHANGELOG.md, and sets the version in composer.json and config/app.php.
 *
 * It touches no git state. release.sh calls it, then commits, tags and publishes.
 *
 *   php tools/release.php                      write the release
 *   php tools/release.php --dry-run            show the version and the section, write nothing
 *   php tools/release.php --release-as 1.0.0   use this version instead of the computed one
 */

const UNRELEASED_HEADING = '## [unreleased]';

// Commit types that reach the changelog, and the heading each one is listed under
const SECTIONS = [
    'feat' => 'Added',
    'fix' => 'Fixed',
    'perf' => 'Changed',
    'refactor' => 'Changed',
    'revert' => 'Changed',
];

chdir(dirname(__DIR__));

try {
    main(array_slice($argv, 1));
} catch (RuntimeException $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

function main(array $arguments): void
{
    $dryRun = in_array('--dry-run', $arguments, true);
    $releaseAs = optionValue($arguments, '--release-as');

    $currentVersion = currentVersion();
    $lastTag = lastTag();
    $commits = commitsSince($lastTag);

    $version = $releaseAs ?? nextVersion($currentVersion, $commits);
    if (preg_match('/^\d+\.\d+\.\d+$/', $version) !== 1) {
        throw new RuntimeException("'{$version}' is not a version. Use three numbers, e.g. 1.0.0.");
    }
    if (version_compare($version, $currentVersion, '<=')) {
        throw new RuntimeException("Version {$version} is not higher than the current version {$currentVersion}.");
    }

    $changelog = readFileOrFail('CHANGELOG.md');
    $handWritten = unreleasedNotes($changelog);
    $notes = $handWritten !== '' ? $handWritten : notesFromCommits($commits);
    if ($notes === '') {
        throw new RuntimeException(
            'Nothing to release: no feat, fix, perf, refactor or revert commit since ' . ($lastTag ?? 'the first commit')
            . ", and no notes under '" . UNRELEASED_HEADING . "'."
        );
    }

    $section = "## [{$version}] - " . today() . "\n\n" . $notes . "\n";

    echo "Current version: {$currentVersion}" . PHP_EOL;
    echo 'Commits since ' . ($lastTag ?? 'the first commit') . ': ' . count($commits) . PHP_EOL;
    echo "Next version:    {$version}" . PHP_EOL;
    echo 'Release notes:   ' . ($handWritten !== ''
        ? "the notes written under '" . UNRELEASED_HEADING . "'"
        : 'compiled from the commits') . PHP_EOL . PHP_EOL;
    echo $section . PHP_EOL;

    if ($dryRun) {
        return;
    }

    writeFileOrFail('CHANGELOG.md', withSection($changelog, $section));
    setVersion('composer.json', '/("version":\s*")[^"]*(")/', $version);
    setVersion('config/app.php', '/(\'app_version\'\s*=>\s*\')[^\']*(\')/', $version);
}

function optionValue(array $arguments, string $name): ?string
{
    $position = array_search($name, $arguments, true);
    if ($position === false) {
        return null;
    }
    if (!isset($arguments[$position + 1])) {
        throw new RuntimeException("{$name} needs a version, e.g. {$name} 1.0.0.");
    }

    return $arguments[$position + 1];
}

function currentVersion(): string
{
    $composer = json_decode(readFileOrFail('composer.json'), true);
    if (!is_array($composer) || !isset($composer['version'])) {
        throw new RuntimeException('composer.json has no version.');
    }

    return $composer['version'];
}

/**
 * The most recent tag reachable from the current commit, or null when there is none.
 */
function lastTag(): ?string
{
    exec('git describe --tags --abbrev=0 2>/dev/null', $lines, $exitCode);

    return $exitCode === 0 && isset($lines[0]) ? $lines[0] : null;
}

/**
 * The commits since the given tag, newest first, without merge commits.
 * Each one is ['hash' => ..., 'type' => ..., 'scope' => ..., 'subject' => ..., 'breaking' => bool].
 * A commit whose subject is not a conventional commit is left out.
 */
function commitsSince(?string $tag): array
{
    $range = $tag === null ? 'HEAD' : escapeshellarg($tag . '..HEAD');
    $log = shell_exec("git log --no-merges --format=%h%x1f%s%x1f%b%x1e {$range}");
    if (!is_string($log)) {
        throw new RuntimeException('git log failed.');
    }

    $commits = [];
    foreach (explode("\x1e", $log) as $record) {
        $fields = explode("\x1f", trim($record));
        if (count($fields) !== 3) {
            continue;
        }
        [$hash, $subject, $body] = $fields;

        if (preg_match('/^([a-z]+)(?:\(([^)]+)\))?(!)?:\s*(.+)$/', $subject, $matches) !== 1) {
            continue;
        }

        $commits[] = [
            'hash' => $hash,
            'type' => $matches[1],
            'scope' => $matches[2],
            'subject' => $matches[4],
            'breaking' => $matches[3] === '!' || str_contains($body, 'BREAKING CHANGE:'),
        ];
    }

    return $commits;
}

/**
 * A fix raises the patch number, a feature the minor number, a breaking change the major number.
 * Before 1.0.0 a breaking change raises the minor number: the major number stays 0 until 1.0.0 is chosen.
 */
function nextVersion(string $currentVersion, array $commits): string
{
    [$major, $minor, $patch] = array_map('intval', explode('.', $currentVersion));

    $types = array_column($commits, 'type');
    $breaking = in_array(true, array_column($commits, 'breaking'), true);

    if ($breaking && $major > 0) {
        return ($major + 1) . '.0.0';
    }
    if ($breaking || in_array('feat', $types, true)) {
        return $major . '.' . ($minor + 1) . '.0';
    }

    return $major . '.' . $minor . '.' . ($patch + 1);
}

/**
 * The commits as changelog entries, grouped under Added, Changed and Fixed.
 */
function notesFromCommits(array $commits): string
{
    $entries = [];
    foreach ($commits as $commit) {
        if (!isset(SECTIONS[$commit['type']])) {
            continue;
        }

        $entry = '-   ';
        if ($commit['breaking']) {
            $entry .= '**Breaking** ';
        }
        if ($commit['scope'] !== '') {
            $entry .= "**{$commit['scope']}:** ";
        }
        $entries[SECTIONS[$commit['type']]][] = $entry . $commit['subject'] . " ({$commit['hash']})";
    }

    $blocks = [];
    foreach (['Added', 'Changed', 'Fixed'] as $heading) {
        if (isset($entries[$heading])) {
            $blocks[] = "### {$heading}\n" . implode("\n", $entries[$heading]);
        }
    }

    return implode("\n\n", $blocks);
}

/**
 * The notes written by hand under the unreleased heading, or an empty string.
 */
function unreleasedNotes(string $changelog): string
{
    $start = strpos($changelog, UNRELEASED_HEADING);
    if ($start === false) {
        return '';
    }

    $start += strlen(UNRELEASED_HEADING);
    $end = strpos($changelog, "\n## [", $start);

    return trim($end === false ? substr($changelog, $start) : substr($changelog, $start, $end - $start));
}

/**
 * The changelog with the release section in place of the unreleased one,
 * or above the latest release when there is no unreleased section.
 */
function withSection(string $changelog, string $section): string
{
    $start = strpos($changelog, UNRELEASED_HEADING);
    if ($start !== false) {
        $end = strpos($changelog, "\n## [", $start + strlen(UNRELEASED_HEADING));
        $rest = $end === false ? '' : substr($changelog, $end + 1);

        return substr($changelog, 0, $start) . $section . "\n" . $rest;
    }

    $firstRelease = strpos($changelog, "\n## [");
    if ($firstRelease === false) {
        return rtrim($changelog) . "\n\n" . $section;
    }

    return substr($changelog, 0, $firstRelease + 1) . $section . "\n" . substr($changelog, $firstRelease + 1);
}

function setVersion(string $path, string $pattern, string $version): void
{
    $content = preg_replace($pattern, '${1}' . $version . '${2}', readFileOrFail($path), 1, $count);
    if ($content === null || $count !== 1) {
        throw new RuntimeException("No version line found in {$path}.");
    }

    writeFileOrFail($path, $content);
}

/**
 * Today's date on this machine's clock. PHP keeps its own timezone setting, which is often UTC.
 */
function today(): string
{
    $date = trim((string) shell_exec('date +%Y-%m-%d'));

    return $date !== '' ? $date : date('Y-m-d');
}

function readFileOrFail(string $path): string
{
    $content = @file_get_contents($path);
    if ($content === false) {
        throw new RuntimeException("Cannot read {$path}.");
    }

    return $content;
}

function writeFileOrFail(string $path, string $content): void
{
    if (@file_put_contents($path, $content) === false) {
        throw new RuntimeException("Cannot write {$path}.");
    }
}
