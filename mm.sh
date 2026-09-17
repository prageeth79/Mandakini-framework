#!/bin/bash

# Get the directory where mm.sh is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"

# Run the PHP file in the same folder and forward all arguments
php "$SCRIPT_DIR/mm" "$@"
