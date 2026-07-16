#!/usr/bin/env bash

set -euo pipefail

PROJECT_SLUG="dww-fingerprinting"

ROOT_DIR="$(
    cd "$(dirname "${BASH_SOURCE[0]}")/.."
    pwd
)"

RELEASE_DIR="$ROOT_DIR/release"
BUILD_DIR="$RELEASE_DIR/build"
PACKAGE_DIR="$BUILD_DIR/$PROJECT_SLUG"

cd "$ROOT_DIR"

echo
echo "========================================="
echo " DWW Fingerprinting Release Builder"
echo "========================================="
echo

########################################
# Validation
########################################

echo "[1/6] Validating release..."

"$ROOT_DIR/tools/validate-release.sh"

echo "✔ Validation passed."
echo

########################################
# Version
########################################

echo "[2/6] Detecting version..."

PLUGIN_VERSION=$(
    sed -nE \
        's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*([^[:space:]]+).*$/\1/p' \
        dww-fingerprinting.php |
    head -1
)

if [[ -z "$PLUGIN_VERSION" ]]; then
    echo "✘ Plugin version could not be detected."
    exit 1
fi

ZIP_NAME="${PROJECT_SLUG}-${PLUGIN_VERSION}.zip"
ZIP_PATH="$RELEASE_DIR/$ZIP_NAME"
SHA_PATH="$ZIP_PATH.sha256"

echo "✔ Version $PLUGIN_VERSION"
echo

########################################
# Production dependencies
########################################

echo "[3/6] Preparing production dependencies..."

composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

echo "✔ Production dependencies ready."
echo

########################################
# Build directory
########################################

echo "[4/6] Preparing build directory..."

rm -rf "$BUILD_DIR"
mkdir -p "$PACKAGE_DIR"

echo "✔ Build directory ready."
echo

########################################
# Copy release files
########################################

echo "[5/6] Copying release files..."

rsync -a \
    --exclude='.git/' \
    --exclude='.github/' \
    --exclude='.idea/' \
    --exclude='.vscode/' \
    --exclude='release/' \
    --exclude='tests/' \
    --exclude='tools/' \
    --exclude='docs/' \
    --exclude='*.zip' \
    --exclude='*.log' \
    --exclude='*.tmp' \
    --exclude='*.bak' \
    --exclude='.DS_Store' \
    --exclude='composer.json' \
    --exclude='composer.lock' \
    --exclude='README.md' \
    --exclude='readme.md' \
    --exclude='CHANGELOG.md' \
    --exclude='ROADMAP.md' \
    --exclude='LICENSE' \
    ./ "$PACKAGE_DIR/"

echo "✔ Release files copied."
echo

########################################
# ZIP and checksum
########################################

echo "[6/6] Creating ZIP package..."

rm -f "$ZIP_PATH" "$SHA_PATH"

(
    cd "$BUILD_DIR"
    zip -rq "$ZIP_PATH" "$PROJECT_SLUG"
)

shasum -a 256 "$ZIP_PATH" > "$SHA_PATH"

rm -rf "$BUILD_DIR"

echo "✔ ZIP package created."
echo

echo "========================================="
echo " Release package ready."
echo " Version: $PLUGIN_VERSION"
echo " ZIP:     release/$ZIP_NAME"
echo " SHA256:  release/$ZIP_NAME.sha256"
echo "========================================="
echo