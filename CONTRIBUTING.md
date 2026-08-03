# Contributing to AI Banking GRC Platform

First off, thank you for considering contributing to the AI Banking GRC Platform! 🎉 It's people like you that make this platform better for the banking industry.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Branch Strategy](#branch-strategy)
- [Commit Messages](#commit-messages)
- [Pull Requests](#pull-requests)
- [Bug Reports](#bug-reports)
- [Feature Requests](#feature-requests)
- [Testing](#testing)
- [Documentation](#documentation)
- [Security](#security)

## 📜 Code of Conduct

This project and everyone participating in it is governed by the [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.

## 🚀 Getting Started

### Prerequisites

- PHP 8.2+
- MySQL 5.7+
- Composer
- Git
- Node.js (for frontend assets)

### Setup Development Environment

```bash
# Clone the repository
git clone https://github.com/yourusername/AI-Banking-GRC-Platform.git
cd AI-Banking-GRC-Platform

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Configure database in .env
# Run migrations
php database/migrate.php

# Start development server
php -S localhost:8080 -t public/