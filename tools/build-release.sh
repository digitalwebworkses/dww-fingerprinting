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

echo "[1/7] Validating release..."

"$ROOT_DIR/tools/validate-release.sh"

echo "✔ Validation passed."
echo

########################################
# Version
########################################

echo "[2/7] Detecting version..."

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

echo "[3/7] Preparing production dependencies..."

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

echo "[4/7] Preparing build directory..."

rm -rf "$BUILD_DIR"
mkdir -p "$PACKAGE_DIR"

echo "✔ Build directory ready."
echo

########################################
# Copy release files
########################################

echo "[5/7] Copying release files..."

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
    ./ "$PACKAGE_DIR/"

echo "✔ Release files copied."
echo

########################################
# ZIP and checksum
########################################

echo "[6/7] Creating ZIP package..."

rm -f "$ZIP_PATH" "$SHA_PATH"

(
    cd "$BUILD_DIR"
    zip -rq "$ZIP_PATH" "$PROJECT_SLUG"
)

shasum -a 256 "$ZIP_PATH" > "$SHA_PATH"

rm -rf "$BUILD_DIR"

if [[ ! -f "$ZIP_PATH" ]]; then
    echo "✘ ZIP package was not created."
    exit 1
fi

if [[ ! -f "$SHA_PATH" ]]; then
    echo "✘ SHA256 file was not created."
    exit 1
fi

echo "[7/7] Verifying release package..."

unzip -tq "$ZIP_PATH" >/dev/null

ZIP_ENTRIES=$(unzip -Z1 "$ZIP_PATH")

for forbidden in tests tools docs .git composer.json composer.lock; do
    if grep -Eq "(^|/)${forbidden}(/|$)" <<< "$ZIP_ENTRIES"; then
        echo "✘ Forbidden release entry found: $forbidden"
        exit 1
    fi
done

if ! grep -Fqx "$PROJECT_SLUG/vendor/autoload.php" <<< "$ZIP_ENTRIES"; then
    echo "✘ Composer autoloader missing from release package."
    exit 1
fi

echo "✔ Release package verified."
echo

echo "✔ ZIP package created."
echo

echo "========================================="
echo " Release package ready."
echo " Version: $PLUGIN_VERSION"
echo " ZIP:     release/$ZIP_NAME"
echo " SHA256:  release/$ZIP_NAME.sha256"
echo "========================================="
echo
