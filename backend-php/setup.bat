@echo off
echo MedWorld Laravel Backend Setup
echo ==============================
echo.

echo Step 1: Creating SQLite database...
if not exist database\database.sqlite (
    type nul > database\database.sqlite
    echo Database file created successfully
) else (
    echo Database file already exists
)

echo.
echo Step 2: Installing Composer dependencies...
call composer install --no-interaction
if errorlevel 1 (
    echo Error installing dependencies. Please run 'composer install' manually
    pause
    exit /b 1
)

echo.
echo Step 3: Generating application key...
php artisan key:generate --force

echo.
echo Step 4: Running migrations and seeding database...
php artisan migrate:fresh --seed --force
if errorlevel 1 (
    echo Warning: Migration had some issues. You may need to run migrations separately.
)

echo.
echo Step 5: Testing the installation...
php artisan route:list

echo.
echo ==============================
echo Setup Complete!
echo ==============================
echo.
echo To start the server, run:
echo   php artisan serve --port=8000
echo.
echo The API will be available at: http://localhost:8000/api/v1
echo.
echo Test credentials:
echo   SuperAdmin - admin@medworld.com / admin123
echo   Admin Doctor - admin.doctor@medworld.com / admin123
echo   Doctor - doctor@medworld.com / doctor123
echo   Assistant - assistant@medworld.com / assistant123
echo   Patient - patient@medworld.com / patient123
echo.
pause
