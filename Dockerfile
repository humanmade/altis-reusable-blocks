# Dockerfile for local testing of GitHub Actions workflow
# Mimics the environment from .github/workflows/main.yml

ARG PHP_VERSION=8.2
FROM php:${PHP_VERSION}-apache

# Set environment variables
ENV NODE_ENV=development
ENV DEBIAN_FRONTEND=noninteractive

# Install system dependencies
RUN echo "Update package lists." && \
    apt-get -y update && \
    echo "Install base packages." && \
    apt-get -y --allow-downgrades --allow-remove-essential --allow-change-held-packages \
    -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confnew \
    install --fix-missing --fix-broken \
    build-essential \
    libssl-dev \
    gnupg \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    vim \
    wget \
    unzip \
    git \
    curl \
    g++ \
    make

# Install Node.js from NodeSource
ARG NODE_VERSION=16
RUN curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - && \
    apt-get install -y nodejs python3 python3-pip && \
    ln -s /usr/bin/python3 /usr/bin/python

# Add yarn package repository (using modern method without apt-key)
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor -o /usr/share/keyrings/yarn-archive-keyring.gpg && \
    echo "deb [signed-by=/usr/share/keyrings/yarn-archive-keyring.gpg] https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list && \
    apt-get -y update

# Install yarn
RUN apt-get -y --allow-downgrades --allow-remove-essential --allow-change-held-packages \
    -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confnew \
    install --fix-missing --fix-broken yarn

# Install Composer (matching the version in the workflow)
RUN curl -sS https://getcomposer.org/installer | php -- --filename=composer.phar --version=1.10.20 && \
    mv composer.phar /usr/local/bin/composer

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) iconv intl xml soap opcache pdo

# Set working directory
WORKDIR /var/www/html

# Default command
CMD ["/bin/bash"]
