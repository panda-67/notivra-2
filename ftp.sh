#!/usr/bin/env bash
set -euo pipefail

###############################################################################
# CONFIG
###############################################################################
# FTP credentials
FTP_NOTIVRA_USER="notivra@notivra.com"
FTP_PUBLIC_USER="frontend@notivra.com"

FTP_HOST="ftp.notivra.com"
FTP_DIR="/"

# Local directories
DIR_NOTIVRA="notivra"
DIR_PUBLIC="public_html"

# Build output
BUILD_NAME="notivra-laravel-$(date +%Y%m%d_%H%M%S)"
ZIP_PATH=~/Downloads/${BUILD_NAME}.zip

# Temp deploy folders
DEPLOY_DIR="deploy"
DEPLOY_NOTIVRA="${DEPLOY_DIR}/notivra"
DEPLOY_PUBLIC="${DEPLOY_DIR}/public_html"

###############################################################################
# Functions
###############################################################################
ask_password() {
  local __varname=$1
  read -rsp "🔐  Enter password for $2: " "$__varname"
  echo
}

ftp_mirror() {
  local local_dir=$1
  local host=$2
  local user=$3
  local pass=$4
  local remote_dir=$5

  lftp -c "
    set ssl:verify-certificate no
    set ftp:ssl-force true
    set ftp:ssl-protect-data true
    set ftp:sync-mode yes
    set net:timeout 20
    set net:max-retries 2
    set mirror:use-pget-n 4
    open -u ${user},${pass} ${host}
    mirror -R \
      --only-newer \
      --parallel=4 \
      --no-symlinks \
      --exclude-glob=.ftpquota \
      --exclude=staging/ \
      --exclude=print/ \
      --exclude=samsulmuarrif.my.id \
      \"${local_dir}\" \"${remote_dir}\"
    bye
  "
}

zip_laravel() {
  echo "📦 Zipping Laravel folder to $ZIP_PATH"
  TMP_ZIP_DIR=$(mktemp -d)
  rsync -a "$DIR_LARAVEL/" "$TMP_ZIP_DIR/laravel" \
    --include=.env.production \
    --exclude=.env.* \
    --exclude=.env \
    --exclude=node_modules \
    --exclude=tests \
    --exclude=.git \
    --exclude=.github \
    --exclude=*.log \
    --exclude=storage/logs \
    --exclude=public

  cd "$TMP_ZIP_DIR/laravel"
  composer install --no-dev --optimize-autoloader
  php artisan optimize:clear
  cd - >/dev/null

  cd "$TMP_ZIP_DIR"
  zip -r "$ZIP_PATH" laravel
  cd - >/dev/null
  echo "✅ Saved Laravel zip to $ZIP_PATH"
}

cleaning_deploy_folders() {
  echo "🧼 Cleaning and preparing deploy folders..."
  rm -rf "$DEPLOY_DIR"
}

###############################################################################
# Main
###############################################################################
clear
echo "📦 Notivra FTP Deploy + Laravel Zipper"
echo "-----------------------------------------"
echo "1) Deploy notivra & public_html (FTP)"
echo "2) Deploy public_html (FTP)"
echo "3) Create Laravel ZIP"
echo "q) Quit"
echo "-----------------------------------------"
read -rp "Select an option: " choice

case "$choice" in
1)
  ask_password FTP_NOTIVRA_PASS "$FTP_NOTIVRA_USER"
  mkdir -p "$DEPLOY_NOTIVRA"
  echo "📦 Syncing laravel file to deploy folder."
  rsync -a "$DIR_NOTIVRA/" "$DEPLOY_NOTIVRA" \
    --exclude="node_modules" \
    --exclude=".env" \
    --exclude=".env.staging" \
    --exclude=".git" \
    --exclude="*.log" \
    --exclude="tests" \
    --exclude=".github" \
    --exclude="storage/logs" \
    --exclude="public"
  cd "$DEPLOY_NOTIVRA"
  composer install --no-dev --optimize-autoloader
  php artisan config:clear
  cd - >/dev/null
  echo "🚀 Uploading notivra..."
  ftp_mirror "$DEPLOY_NOTIVRA" "$FTP_HOST" "$FTP_NOTIVRA_USER" "$FTP_NOTIVRA_PASS" "$FTP_DIR"
  echo "✅ FTP upload notivra completed."

  mkdir -p "$DEPLOY_PUBLIC"
  echo "📦 Buliding frontend."
  cd "$DIR_NOTIVRA"
  npm run build
  cd - >/dev/null
  echo "📦 Syncing public_html file to deploy folder."
  rsync -a "$DIR_PUBLIC/" "$DEPLOY_PUBLIC" \
    --exclude="hot" \
    --exclude=".git" \
    --exclude="*.log" \
    --exclude="notivra/"

  echo "🚀 Uploading public_html..."
  ftp_mirror "$DEPLOY_PUBLIC" "$FTP_HOST" "$FTP_PUBLIC_USER" "$FTP_NOTIVRA_PASS" "$FTP_DIR"
  echo "✅ FTP upload public completed."
  ;;
2)
  ask_password FTP_PUBLIC_PASS "$FTP_PUBLIC_USER"
  mkdir -p "$DEPLOY_PUBLIC"
  echo "📦 Buliding frontend."
  cd "$DIR_LARAVEL"
  npm run build
  cd - >/dev/null
  echo "📦 Syncing public_html file to deploy folder."
  rsync -a "$DIR_PUBLIC/" "$DEPLOY_PUBLIC" \
    --exclude="hot" \
    --exclude=".git" \
    --exclude="*.log" \
    --exclude="notivra/"

  echo "🚀 Uploading public_html..."
  ftp_mirror "$DEPLOY_PUBLIC" "$FTP_HOST" "$FTP_PUBLIC_USER" "$FTP_PUBLIC_PASS" "$FTP_DIR"
  echo "✅ FTP upload public completed."
  ;;
3)
  zip_laravel
  ;;
q | Q)
  echo "👋 Cancelled."
  exit 0
  ;;
*)
  echo "⚠️ Invalid choice."
  exit 1
  ;;
esac

echo "🎉 Congratulation, FTP Upload Done."
