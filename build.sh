#!/bin/bash

# Exit on error
set -e

PLUGIN_SLUG="novastats-hockeydata"
VERSION=$(grep "Version:" naba-hdwp-widgets.php | awk '{print $NF}')
if [ -z "$VERSION" ]; then
    VERSION="latest"
fi

ZIP_NAME="${PLUGIN_SLUG}-${VERSION}.zip"
BUILD_DIR="/tmp/${PLUGIN_SLUG}-build"

echo "Building ${PLUGIN_SLUG} version ${VERSION}..."

# Clean up previous build directory if it exists
rm -rf "${BUILD_DIR}"
mkdir -p "${BUILD_DIR}/${PLUGIN_SLUG}"

# Install production PHP dependencies
echo "Installing production dependencies..."
composer install --no-dev --optimize-autoloader

# Copy files to build directory, respecting .distignore
echo "Copying files..."
rsync -av --exclude-from='.distignore' ./ "${BUILD_DIR}/${PLUGIN_SLUG}/"

# Create the zip file
echo "Creating zip archive ${ZIP_NAME}..."
cd "${BUILD_DIR}"
zip -r "${ZIP_NAME}" "${PLUGIN_SLUG}"
cd -

# Move zip back to project root
mv "${BUILD_DIR}/${ZIP_NAME}" .

# Clean up build directory
rm -rf "${BUILD_DIR}"

# Restore dev dependencies
echo "Restoring dev dependencies..."
composer install

echo "Build complete! Output: ${ZIP_NAME}"
