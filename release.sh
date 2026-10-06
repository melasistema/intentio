#!/bin/bash

# Cut a release: work out the version from the conventional commits since the last tag,
# write its section in CHANGELOG.md, commit, tag, push, and publish the tag as a GitHub Release.
#
# Runs on master only; from any other branch it stops with an error.
#
#   ./release.sh                     bump per the commits (fix -> patch, feat -> minor)
#   ./release.sh --dry-run           preview the version and the changelog section, write nothing
#   ./release.sh --release-as 1.0.0  force a specific version
#
# Notes written by hand under "## [unreleased]" in CHANGELOG.md become the release notes.
# Without them, the notes are compiled from the commits. See tools/release.php.

set -e

PRODUCTION_BRANCH="master"

cd "$(dirname "$0")"

dry_run=false
case " $* " in
  *" --dry-run "*) dry_run=true ;;
esac

echo "--- Starting Release Process ---"

# Releases are cut from the production branch only, and the script will not switch to it
# for you: merging the tested work into it is a deliberate step that comes first.
current_branch="$(git rev-parse --abbrev-ref HEAD)"
if [ "$current_branch" != "$PRODUCTION_BRANCH" ]; then
  echo "ERROR: releases are cut from '$PRODUCTION_BRANCH', but you are on '$current_branch'." >&2
  echo "       Merge your work into $PRODUCTION_BRANCH, check it out, then run this again." >&2
  exit 1
fi

# Refuse to release a dirty tree. The changelog is generated from committed history,
# so uncommitted work would be tagged as released without ever appearing in it.
if [ -n "$(git status --porcelain)" ]; then
  echo "ERROR: working tree is not clean. Commit or stash first." >&2
  git status --short >&2
  exit 1
fi

# The GitHub Release is the last step. Check that it can be made before anything is written,
# so a release is never left half done: tagged and pushed, but not published.
if [ "$dry_run" = false ] && ! gh auth status >/dev/null 2>&1; then
  echo "ERROR: the GitHub CLI is missing or not logged in. Install gh and run 'gh auth login'." >&2
  exit 1
fi

echo "Pulling latest changes from origin..."
git pull origin $PRODUCTION_BRANCH

echo "Running the tests..."
composer test

echo "Preparing the release (version and changelog)..."
php tools/release.php "$@"

# --dry-run writes nothing and creates no tag, so there is nothing to push.
if [ "$dry_run" = true ]; then
  echo "--- Dry run complete. Nothing was written or pushed. ---"
  exit 0
fi

version="$(php -r 'echo json_decode(file_get_contents("composer.json"), true)["version"];')"
tag="v$version"

if git rev-parse --quiet --verify "refs/tags/$tag" >/dev/null; then
  echo "ERROR: the tag $tag already exists. Undo the changes with 'git checkout .'." >&2
  exit 1
fi

echo "Committing and tagging $tag..."
git add CHANGELOG.md composer.json config/app.php
git commit -m "chore(release): $version"
git tag -a "$tag" -m "Release $version"

echo "Pushing the release commit and the tag to origin..."
git push origin $PRODUCTION_BRANCH
git push origin "$tag"

# The release notes are this version's section of the changelog, without its heading.
notes_file="$(mktemp)"
awk -v heading="## [$version]" '
  index($0, heading) == 1 { inside = 1; next }
  index($0, "## [") == 1  { inside = 0 }
  inside
' CHANGELOG.md > "$notes_file"

echo "Publishing the GitHub Release..."
gh release create "$tag" --title "$tag" --notes-file "$notes_file" --verify-tag
rm -f "$notes_file"

echo "--- Release Process Complete! $tag is published. ---"
