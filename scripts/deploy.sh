#!/bin/bash
# Auto-deployment script for shared hosting
# This script runs on the GitHub Actions runner (or can be run locally)

set -e # Exit immediately if a command exits with a non-zero status

# 1. Validation
echo "==> Validating environment variables..."
if [ -z "$REMOTE_HOST" ]; then
    echo "ERROR: REMOTE_HOST is not set." >&2
    exit 1
fi

if [ -z "$REMOTE_USER" ]; then
    echo "ERROR: REMOTE_USER is not set." >&2
    exit 1
fi

if [ -z "$REMOTE_TARGET" ]; then
    echo "ERROR: REMOTE_TARGET is not set." >&2
    exit 1
fi

if [ -z "$SSH_KEY_FILE" ]; then
    echo "ERROR: SSH_KEY_FILE is not set." >&2
    exit 1
fi

# Set default port to 22 if not specified
REMOTE_PORT=${REMOTE_PORT:-22}
ARCHIVE_NAME="bigdata-deploy.tar.gz"
ARCHIVE_PATH="/tmp/$ARCHIVE_NAME"

echo "Host: $REMOTE_HOST"
echo "Port: $REMOTE_PORT"
echo "User: $REMOTE_USER"
echo "Target Dir: $REMOTE_TARGET"
echo "SSH Key File: $SSH_KEY_FILE"

# 2. Archive local directory
echo "==> Creating deployment archive ($ARCHIVE_PATH)..."
tar -czf "$ARCHIVE_PATH" \
    --exclude='.git*' \
    --exclude='.github*' \
    --exclude='.vscode*' \
    --exclude='settings.php' \
    --exclude='scripts*' \
    .

echo "Archive created successfully."

# 3. Upload to remote host
echo "==> Uploading archive to remote host..."
scp -P "$REMOTE_PORT" \
    -i "$SSH_KEY_FILE" \
    -o StrictHostKeyChecking=no \
    -o UserKnownHostsFile=/dev/null \
    "$ARCHIVE_PATH" \
    "$REMOTE_USER@$REMOTE_HOST:$REMOTE_TARGET/"

echo "Archive uploaded successfully."

# 4. Extract archive on remote host
echo "==> Extracting archive on remote host..."
ssh -p "$REMOTE_PORT" \
    -i "$SSH_KEY_FILE" \
    -o StrictHostKeyChecking=no \
    -o UserKnownHostsFile=/dev/null \
    "$REMOTE_USER@$REMOTE_HOST" \
    "tar -xzf $REMOTE_TARGET/$ARCHIVE_NAME -C $REMOTE_TARGET/ && rm $REMOTE_TARGET/$ARCHIVE_NAME"

echo "Archive extracted and removed on remote host."

# 5. Local Cleanup
echo "==> Cleaning up local archive..."
rm -f "$ARCHIVE_PATH"

echo "==> Deployment completed successfully!"
