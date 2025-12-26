#!/usr/bin/env bash
set -e

echo "Installing backend dependencies"
cd backend-php

composer install
npm install

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
  echo "✔ .env created"
fi

cd ..

echo "Installing frontend dependencies"
npm install

echo "Installation complete!"
