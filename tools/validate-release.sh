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
    "docs/architecture.md"
    "docs/developer-guide.md"
    "docs/installation.md"
    "docs/user-guide.md"
    "docs/api-reference.md"
    "docs/LICENSE"
)

for file in "${required_files[@]}"; do
    if [[ ! -e "$file" ]]; then
        echo "✘ Missing: $file"
        exit 1
    fi
done

echo "✔ Required files present."
echo

########################################
# Composer
########################################

echo "[3/7] Validating composer.json..."

composer validate --strict --no-check-publish >/dev/null

echo "✔ Composer OK."
echo

########################################
# TODO / FIXME
########################################

echo "[4/7] Searching TODO/FIXME..."

if grep -R \
    --exclude-dir=.git \
    --exclude-dir=vendor \
    "TODO\|FIXME" . >/dev/null; then

    echo "✘ TODO or FIXME found."
    exit 1
fi

echo "✔ No TODO/FIXME found."
echo

########################################
# Debug code
########################################

echo "[5/7] Searching debug code..."

if grep -R \
    "var_dump\|print_r\|dd(\|dump(" \
    includes assets >/dev/null; then

    echo "✘ Debug code found."
    exit 1
fi

echo "✔ No debug code found."
echo

########################################
# Version
########################################

echo "[6/7] Checking version consistency..."

PLUGIN_VERSION=$(grep "Version:" dww-fingerprinting.php | head -1 | awk '{print $2}')
CONST_VERSION=$(grep "DWW_FP_VERSION" dww-fingerprinting.php | head -1 | cut -d"'" -f2)

if [[ "$PLUGIN_VERSION" != "$CONST_VERSION" ]]; then
    echo "✘ Version mismatch."
    exit 1
fi

echo "✔ Version $PLUGIN_VERSION"
echo

if ! grep -q "$PLUGIN_VERSION" CHANGELOG.md; then
    echo "✘ Version not found in CHANGELOG.md"
    exit 1
fi

if ! grep -q "$PLUGIN_VERSION" readme.md; then
    echo "✘ Version not found in readme.md"
    exit 1
fi

########################################
# Documentation
########################################

echo "[7/7] Checking documentation..."

if [[ ! -d docs ]]; then
    echo "✘ docs directory missing."
    exit 1
fi

echo "✔ Documentation OK."
echo

########################################

echo "========================================="
echo "✔ Release validation completed."
echo "========================================="
echo