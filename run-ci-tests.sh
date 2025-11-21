#!/bin/bash
# Script to run all CI checks locally
# Mimics the steps from .github/workflows/main.yml

set -e  # Exit on any error

echo "=========================================="
echo "Running CI Tests Locally"
echo "=========================================="
echo ""

# Step 1: PHP Syntax Linting
echo "📝 Step 1: PHP Syntax Linting"
echo "------------------------------------------"
find . -path ./vendor -prune -o -name "*.php" -print0 | xargs -0 -n1 -P8 php -l
echo "✅ PHP Syntax check passed"
echo ""

# Step 2: Run yarn install
echo "📦 Step 2: Installing Node dependencies"
echo "------------------------------------------"
# Remove node_modules to ensure clean build
rm -rf node_modules
# Install dependencies normally (node-sass will try to download pre-built binary)
yarn install --ignore-engines
echo "✅ Yarn install completed"
echo ""

# Step 3: Run composer validate
echo "🔍 Step 3: Validating composer.json"
echo "------------------------------------------"
composer validate
echo "✅ Composer validation passed"
echo ""

# Step 4: Run composer install
echo "📦 Step 4: Installing PHP dependencies"
echo "------------------------------------------"
composer install --prefer-dist --no-progress
echo "✅ Composer install completed"
echo ""

# Step 5: Run PHPUnit
echo "🧪 Step 5: Running PHPUnit tests"
echo "------------------------------------------"
composer test:unit
echo "✅ PHPUnit tests passed"
echo ""

# Step 6: Run PHPCS Coding Standards
echo "📏 Step 6: Running PHPCS coding standards"
echo "------------------------------------------"
composer run lint:phpcs
echo "✅ PHPCS checks passed"
echo ""

# Step 7: Run ESLint
echo "🔧 Step 7: Running ESLint"
echo "------------------------------------------"
yarn lint:js
echo "✅ ESLint checks passed"
echo ""

# Step 8: Run yarn build
echo "🏗️  Step 8: Building assets"
echo "------------------------------------------"
yarn run build
echo "✅ Build completed"
echo ""

echo "=========================================="
echo "✅ All CI checks passed successfully!"
echo "=========================================="
