#!/bin/bash

# Function to read version from version.json
get_version() {
  version=$(jq -r '.version' version.json)
  echo $version
}

# Ensure the script exits if any command fails
set -e

# Check out the main branch
git checkout main

# Get the current version from version.json
version=$(get_version)
if [ -z "$version" ]; then
  echo "Failed to get the version from version.json."
  exit 1
fi

# Create a new tag for the release
git tag -a "v$version" -m "Release version $version"

# Push the tag to GitHub
git push origin "v$version"

echo "Release process completed successfully for version $version."