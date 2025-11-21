#!/bin/bash
set -ex

# Configuration
REMOTE_USER="akilnoqy"
REMOTE_HOST="66.29.146.96"
REMOTE_PORT=21098
REMOTE_PATH="/home/akilnoqy/aiota.online"

# Local Moodle path (current project directory)
LOCAL_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "Deploying Moodle from ${LOCAL_PATH} to ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}"

# Ensure remote directory exists
ssh -p "${REMOTE_PORT}" "${REMOTE_USER}@${REMOTE_HOST}" "mkdir -p '${REMOTE_PATH}'"

# Sync files to the server, excluding dependencies and local-only files
rsync -avz \
  -e "ssh -p ${REMOTE_PORT}" \
  --delete \
  --exclude='vendor/' \
  --exclude='node_modules/' \
  --exclude='moodledata/' \
  --exclude='.git/' \
  --exclude='.idea/' \
  --exclude='.vscode/' \
  --exclude='deploy.sh' \
  --exclude='/config.php' \
  "${LOCAL_PATH}/" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}/"

echo "Deployment complete."
echo "Remember to install PHP/Composer dependencies on the server (e.g. via cPanel Terminal or SSH) so Moodle can run correctly."

