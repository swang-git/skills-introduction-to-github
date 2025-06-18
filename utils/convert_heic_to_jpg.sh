#!/bin/bash

# Check if directory is provided
if [ -z "$1" ]; then
    echo "Usage: $0 <directory>"
    exit 1
fi

# Navigate to the target directory
cd "$1" || exit

# Loop through all .heic files
for file in *.heic; do
    if [ -f "$file" ]; then
        # Get the original modification time
        original_mtime=$(stat -f "%m" "$file")

        # Convert HEIC to JPG (same basename)
        jpg_file="${file%.heic}.jpg"
        sips -s format jpeg "$file" --out "$jpg_file" &> /dev/null

        if [ $? -eq 0 ]; then
            # Restore original modification time
            touch -m -t "$(date -r "$original_mtime" +"%Y%m%d%H%M.%S")" "$jpg_file"
            echo "✅ Converted: $file → $jpg_file (mtime preserved)"
        else
            echo "❌ Failed to convert: $file"
        fi
    fi
done

echo "✨ Conversion complete!"