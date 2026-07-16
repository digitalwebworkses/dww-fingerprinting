#!/usr/bin/env bash

set -e

echo
echo "========================================="
echo " DWW Fingerprinting Release Validator"
echo "========================================="
echo

########################################
# Repository status
########################################

echo "[1/7] Checking repository status..."

if ! git diff --quiet || ! git diff --cached --quiet; then
    echo "✘ Working tree is not clean."
    echo
    git status --short
    exit 1
fi

echo "✔ Repository clean."
echo

########################################
# Required files
########################################

echo "[2/7] Checking required files..."

required_files=(
    "dww-fingerprinting.php"
    "readme.md"
    "CHANGELOG.md"
    "ROADMAP.md"
    "composer.json"
    "LICENSE"
    "docs/architecture.md"
    "docs/developer-guide.md"
    "docs/installation.md"
    "docs/user-guide.md"
    "docs/api-reference.md"
)

for file in "${required_files[@]}"; do
    if [[ ! -e "$file" ]]; then
        echo "✘ Missing required file: $file"
        exit 1
    fi
done

echo "✔ Required files present."
echo

########################################
# Composer
########################################

echo "[3/7] Validating composer.json..."

if ! composer validate --strict --no-check-publish >/dev/null; then
    echo "✘ Composer validation failed."
    echo
    echo "Hint:"
    echo "  composer update --lock"
    exit 1
fi

echo "✔ Composer OK."
echo

########################################
# TODO / FIXME
########################################

echo "[4/7] Searching TODO/FIXME..."

matches=$(
grep -R \
    --exclude-dir=.git \
    --exclude-dir=vendor \
    --exclude-dir=tools \
    --exclude-dir=release \
    "TODO\|FIXME" . || true
)

if [[ -n "$matches" ]]; then
    echo "✘ TODO/FIXME markers found:"
    echo
    echo "$matches"
    exit 1
fi

echo "✔ No TODO/FIXME found."
echo

########################################
# Debug code
########################################

echo "[5/7] Searching debug code..."

matches=$(
grep -R -E \
    --exclude-dir=.git \
    --exclude-dir=vendor \
    --exclude-dir=tools \
    --exclude-dir=release \
    '\b(var_dump|print_r|dump|dd)\s*\(' . || true
)

if [[ -n "$matches" ]]; then
    echo "✘ Debug code found:"
    echo
    echo "$matches"
    exit 1
fi

echo "✔ No debug code found."
echo

########################################
# Version consistency
########################################

echo "[6/7] Checking version consistency..."

PLUGIN_VERSION=$(
    sed -nE \
        's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*([^[:space:]]+).*$/\1/p' \
        dww-fingerprinting.php |
    head -1
)

CONST_VERSION=$(
    sed -nE \
        "s/.*define\('DWW_FP_VERSION',[[:space:]]*'([^']+)'\).*/\1/p" \
        dww-fingerprinting.php |
    head -1
)

if [[ -z "$PLUGIN_VERSION" ]]; then
    echo "✘ Plugin header version could not be detected."
    exit 1
fi

if [[ -z "$CONST_VERSION" ]]; then
    echo "✘ DWW_FP_VERSION constant could not be detected."
    exit 1
fi

if [[ "$PLUGIN_VERSION" != "$CONST_VERSION" ]]; then
    echo "✘ Plugin header version and constant do not match."
    echo "  Header:   $PLUGIN_VERSION"
    echo "  Constant: $CONST_VERSION"
    exit 1
fi

if ! grep -Fq "$PLUGIN_VERSION" CHANGELOG.md; then
    echo "✘ Version $PLUGIN_VERSION not found in CHANGELOG.md"
    exit 1
fi

if ! grep -Fq "$PLUGIN_VERSION" readme.md; then
    echo "✘ Version $PLUGIN_VERSION not found in readme.md"
    exit 1
fi

echo "✔ Version $PLUGIN_VERSION"
echo

########################################
# Documentation
########################################

echo "[7/7] Checking documentation..."

if [[ ! -d docs ]]; then
    echo "✘ Documentation directory not found."
    exit 1
fi

echo "✔ Documentation OK."
echo

########################################

echo "========================================="
echo " Release ready for packaging."
echo " Version: $PLUGIN_VERSION"
echo "========================================="
echo