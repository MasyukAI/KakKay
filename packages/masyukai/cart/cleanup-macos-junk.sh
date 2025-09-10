#!/bin/bash

# Clean up macOS junk files script
echo "Cleaning up macOS junk files..."

# Remove .DS_Store files
find . -name ".DS_Store" -type f -delete

# Remove ._* files created by macOS
find . -name "._*" -type f -delete

# Remove __MACOSX folders
find . -name "__MACOSX" -type d -exec rm -rf {} +

echo "Cleanup complete!"

# List any remaining junk files for verification
echo "Checking for any remaining junk files..."
remaining_files=$(find . \( -name ".DS_Store" -o -name "._*" -o -name "__MACOSX" \) 2>/dev/null)

if [ -z "$remaining_files" ]; then
    echo "No junk files found. ✓"
else
    echo "Remaining junk files:"
    echo "$remaining_files"
fi
