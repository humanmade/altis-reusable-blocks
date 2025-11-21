# Local Testing with Docker

This guide shows you how to test the GitHub Actions workflow locally using Docker.

## Prerequisites

- Docker installed on your machine
- Docker Compose installed

## Quick Start

### 1. Build the Docker Image

```bash
docker compose build
```

This creates a container with:
- PHP 8.2 (configurable to 8.1 or 8.3)
- Node.js 16 LTS
- Yarn
- Composer 1.10.20
- All required PHP extensions

### 2. Run All CI Tests

```bash
docker compose run --rm ci-test ./run-ci-tests.sh
```

This runs all workflow steps:
1. PHP Syntax Linting
2. Install Node dependencies
3. Validate composer.json
4. Install PHP dependencies
5. Run PHPUnit tests
6. Run PHPCS coding standards
7. Run ESLint
8. Build assets

## Advanced Usage

### Test with Different PHP Versions

```bash
# PHP 8.1
PHP_VERSION=8.1 docker compose build
docker compose run --rm ci-test ./run-ci-tests.sh

# PHP 8.3
PHP_VERSION=8.3 docker compose build
docker compose run --rm ci-test ./run-ci-tests.sh
```

### Run Individual Steps

Start an interactive shell:

```bash
docker compose run --rm ci-test bash
```

Then run individual commands:

```bash
# PHP syntax check
find . -path ./vendor -prune -o -name "*.php" -print0 | xargs -0 -n1 -P8 php -l

# Run tests
composer test:unit

# Run linting
composer run lint:phpcs

# Run ESLint
yarn lint:js

# Build assets
PYTHON=/usr/bin/python3 npm rebuild node-sass
yarn run build
```

### Clean Up

Remove the Docker container and images:

```bash
docker compose down
docker rmi altis-reusable-blocks-ci-test
```

## Known Issues

### PHPCS Typed Property Errors

PHPCS may report errors about typed properties not being supported in PHP 7.3:

```
tests/unit/rest-api/search/RESTEndpointTest.php
  13 | ERROR | Typed properties are not supported in PHP 7.3 or earlier
```

**This is expected** and can be ignored. The project targets PHP 8.1+ where typed properties are fully supported. These are test files and use PHP 8.x features.

### node-sass Build Issues

The project uses node-sass 4.12.0, which is old and expects Python 2. The Docker setup handles this by:
- Installing Python 3
- Setting `PYTHON=/usr/bin/python3` when rebuilding node-sass
- Using `--ignore-scripts` during `yarn install` to skip the initial build

## Files Created

- `Dockerfile` - Container definition matching the GitHub Actions workflow
- `docker compose.yml` - Docker Compose configuration
- `run-ci-tests.sh` - Script that runs all CI checks in sequence
- `LOCAL-TESTING.md` - This documentation file

## Troubleshooting

### Permission Issues

If you encounter permission errors, try running with `--user`:

```bash
docker compose run --rm --user $(id -u):$(id -g) ci-test ./run-ci-tests.sh
```

### Build Failures

If the Docker build fails, try building without cache:

```bash
docker compose build --no-cache
```

### Port Conflicts

If you have services running on port 80, you can remove the port mapping from `docker compose.yml` (it's not needed for testing).
