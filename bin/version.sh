#!/bin/bash

# Exit on error
set -e

cd "$(dirname "${BASH_SOURCE[0]}")/../"

# Fetch latest changes
echo "Fetching from remote..."
git fetch origin

# Checkout main
echo "Checking out main..."
git checkout main
git pull origin main

# Files containing the version
PLUGIN_FILE="bible-plugin.php"
README_FILE="readme.txt"

# Get current version from the plugin header
CURRENT_VERSION=$(grep -m 1 "Version:" "$PLUGIN_FILE" | awk '{print $2}')

if [ -z "$CURRENT_VERSION" ]; then
    echo "Error: Could not find version in $PLUGIN_FILE"
    exit 1
fi

echo "Current version: $CURRENT_VERSION"

# Determine new version
if [ -n "$1" ]; then
    VERSION_TYPE=$1
else
    # Default to patch
    VERSION_TYPE="patch"
fi

# Parse current version
IFS='.' read -r MAJOR MINOR PATCH <<< "$CURRENT_VERSION"

# Handle cases where components are missing
[ -z "$MAJOR" ] && MAJOR=0
[ -z "$MINOR" ] && MINOR=0
[ -z "$PATCH" ] && PATCH=0

case "$VERSION_TYPE" in
    major)
        NEW_MAJOR=$((MAJOR + 1))
        NEW_VERSION="$NEW_MAJOR.0.0"
        echo "Bumping major version: $NEW_VERSION"
        ;;
    minor)
        NEW_MINOR=$((MINOR + 1))
        NEW_VERSION="$MAJOR.$NEW_MINOR.0"
        echo "Bumping minor version: $NEW_VERSION"
        ;;
    patch)
        NEW_PATCH=$((PATCH + 1))
        NEW_VERSION="$MAJOR.$MINOR.$NEW_PATCH"
        echo "Bumping patch version: $NEW_VERSION"
        ;;
    *)
        # If it's a specific version like 1.2.3
        NEW_VERSION=$VERSION_TYPE
        echo "Using provided version: $NEW_VERSION"
        ;;
esac

# Update version in the plugin header
# Mac's sed needs an empty string for -i extension or it behaves differently
sed -i '' "s/Version: .*/Version: $NEW_VERSION/" "$PLUGIN_FILE"

# Keep readme.txt's Stable tag in sync
sed -i '' "s/Stable tag: .*/Stable tag: $NEW_VERSION/" "$README_FILE"

# Run linting before commit
echo "Running composer lint:fix..."
composer lint:fix

# Git operations
echo "Committing change..."
# Add any changes from lint:fix and the version bump
git add .
git commit -m "$NEW_VERSION"

echo "Tagging commit..."
git tag -a "$NEW_VERSION" -m "$NEW_VERSION"

echo "Pushing changes and tags..."
git push origin main
git push origin "$NEW_VERSION"

echo "Version bumped to $NEW_VERSION successfully."
