#!/bin/bash

###############################################################################
# DWW Fingerprinting
# Version Bump Utility
###############################################################################

set -e

echo
echo "==============================================="
echo "   DWW Fingerprinting - Version Bump Utility"
echo "==============================================="
echo

OLD="$1"
NEW="$2"

# ---------------------------------------------------------------------------
# Solicitar datos si no se pasan como argumentos
# ---------------------------------------------------------------------------

if [ -z "$OLD" ]; then
    read -rp "Versión actual: " OLD
fi

if [ -z "$NEW" ]; then
    read -rp "Nueva versión : " NEW
fi

if [ -z "$OLD" ] || [ -z "$NEW" ]; then
    echo
    echo "ERROR: Debes indicar ambas versiones."
    exit 1
fi

if [ "$OLD" = "$NEW" ]; then
    echo
    echo "ERROR: Las versiones no pueden ser iguales."
    exit 1
fi

echo
echo "Actualizando versión:"
echo
echo "    ${OLD}  -->  ${NEW}"
echo

# ---------------------------------------------------------------------------
# Reemplazo
# ---------------------------------------------------------------------------

find . \
    -type f \
    \( \
        -name "*.php"  -o \
        -name "*.md"   -o \
        -name "*.txt"  -o \
        -name "*.json" \
    \) \
    -not -path "./.git/*" \
    -not -path "./vendor/*" \
    -not -path "./tests/*" \
    -not -path "./tools/*" \
    -exec perl -0pi -e "s/\b${OLD}\b/${NEW}/g" {} +

# ---------------------------------------------------------------------------
# Comprobación
# ---------------------------------------------------------------------------

echo
echo "Comprobando referencias antiguas..."
echo

MATCHES=$(grep -RI "${OLD}" . \
    --exclude-dir=.git \
    --exclude-dir=vendor \
    --exclude-dir=tests \
    --exclude-dir=tools \
    || true)

if [ -n "$MATCHES" ]; then

    echo "⚠ Todavía existen referencias a ${OLD}:"
    echo
    echo "$MATCHES"
    echo

else

    echo "✔ No quedan referencias antiguas."
    echo

fi

echo "==============================================="
echo "Proceso finalizado."
echo "==============================================="
echo