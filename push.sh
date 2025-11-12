#!/bin/bash

# Script to temporarily use only VCS repositories for composer update

COMPOSER_FILE="composer.json"
BACKUP_FILE="${COMPOSER_FILE}.backup"

echo "Backing up $COMPOSER_FILE to $BACKUP_FILE..."
cp "$COMPOSER_FILE" "$BACKUP_FILE"

echo "Filtering $COMPOSER_FILE to keep only VCS repositories..."
if command -v jq >/dev/null 2>&1; then
    jq '.repositories = [.repositories[] | select(.type == "vcs")]' "$COMPOSER_FILE" > "${COMPOSER_FILE}.tmp" && mv "${COMPOSER_FILE}.tmp" "$COMPOSER_FILE"
else
    echo "jq not found, using sed to remove path repositories..."
    # Remove lines from "type": "path" to the closing } of each path repo
    sed -i '' '/"type": "path"/{N;N;N;N;N;d;}' "$COMPOSER_FILE"
    # This is approximate; may need adjustment if format changes
fi

echo "Running composer update..."
composer update

echo "Restoring $COMPOSER_FILE from $BACKUP_FILE..."
mv "$BACKUP_FILE" "$COMPOSER_FILE"

echo "Done."