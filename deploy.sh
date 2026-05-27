#!/bin/bash
# Deploy a tagged version of the plugin to the wordpress.org SVN repository.
#
# History: originally a modification of Dean Clatworthy's deploy script
# (https://github.com/deanc/wordpress-plugin-git-svn). Rewritten for the
# v1.0 codebase to:
#   * use `git archive` (honours .gitattributes export-ignore)
#   * run composer test + composer readme as pre-flight checks
#   * be distribution-agnostic (no dpkg-query)

set -euo pipefail

# ---- config ----------------------------------------------------------------
PLUGINSLUG="slick-google-map"
SVNUSER="norbusan"
GITPATH="$(pwd)/"
SVNPATH="${GITPATH%/}/../${PLUGINSLUG}-wordpress.svn"
SVNURL="https://plugins.svn.wordpress.org/${PLUGINSLUG}/"

# ---- pre-flight ------------------------------------------------------------
echo "==> Pre-flight checks"

for cmd in svn rsync git tar composer make; do
	command -v "$cmd" >/dev/null 2>&1 || { echo "ERROR: $cmd is not installed."; exit 1; }
done

# Working tree must be clean.
if [ -n "$(git status --porcelain)" ]; then
	echo "ERROR: working tree has uncommitted changes. Commit or stash first."
	exit 1
fi

# Versions in readme.txt and plugin header must agree.
make version-check

# Tests must pass.
composer test

# README.md must be in sync with readme.txt.
composer readme
if ! git diff --quiet -- README.md; then
	echo "ERROR: README.md is out of sync with readme.txt. Run 'composer readme', commit, retry."
	exit 1
fi

# Local SVN checkout must be clean.
if [ ! -d "$SVNPATH" ]; then
	echo "ERROR: SVN checkout not found at $SVNPATH"
	echo "       Run: svn checkout $SVNURL $SVNPATH"
	exit 1
fi
if [ -n "$(svn status "$SVNPATH")" ]; then
	echo "ERROR: SVN checkout at $SVNPATH has uncommitted changes."
	exit 1
fi

# ---- version & tag ---------------------------------------------------------
NEWVERSION=$(awk -F': ' '/^Stable tag:/ {print $2}' "${GITPATH}readme.txt" | tr -d ' \r')
echo "==> Releasing version $NEWVERSION"

if git show-ref --tags --quiet --verify -- "refs/tags/$NEWVERSION"; then
	echo "ERROR: git tag $NEWVERSION already exists."
	exit 1
fi

echo "==> Tagging $NEWVERSION in git"
git tag -a "$NEWVERSION" -s -m "Tagging version $NEWVERSION"

echo "==> Pushing master + tags to origin"
git push origin master
git push origin "refs/tags/$NEWVERSION"

# ---- export to SVN trunk ---------------------------------------------------
TMPDIR=$(mktemp -d)
trap 'rm -rf "$TMPDIR"' EXIT

echo "==> Exporting HEAD to $TMPDIR (honouring .gitattributes export-ignore)"
# git archive | tar honours export-ignore, unlike git checkout-index.
git archive HEAD | tar -x -C "$TMPDIR"

echo "==> Updating SVN working copy"
svn up "$SVNPATH"

echo "==> Syncing into $SVNPATH/trunk/"
rsync -av --delete "$TMPDIR/" "$SVNPATH/trunk/"

cd "$SVNPATH/trunk/"
# Add untracked files (skip svn meta) and remove deleted ones.
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs --no-run-if-empty svn add
svn status | grep -v "^.[ \t]*\..*" | grep "^!" | awk '{print $2}' | xargs --no-run-if-empty svn rm

# ---- commit trunk + tag ----------------------------------------------------
read -p "SVN commit message for trunk: " COMMITMSG
svn commit --username="$SVNUSER" -m "$COMMITMSG"

cd "$SVNPATH"
echo "==> Copying trunk to tags/$NEWVERSION"
svn copy trunk/ "tags/$NEWVERSION/"
cd "$SVNPATH/tags/$NEWVERSION"
svn commit --username="$SVNUSER" -m "Tagging version $NEWVERSION"

echo "*** Released $NEWVERSION ***"
