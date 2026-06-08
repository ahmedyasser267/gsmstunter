@echo off
setlocal EnableExtensions

REM One-click setup: run migrations + open system check.
REM Update DB_PASS if your MySQL root has a password.

set "XAMPP_DIR=D:\xampp"
set "MYSQL_EXE=%XAMPP_DIR%\mysql\bin\mysql.exe"
set "PROJECT_DIR=%~dp0"
if "%PROJECT_DIR:~-1%"=="\" set "PROJECT_DIR=%PROJECT_DIR:~0,-1%"
for %%I in ("%PROJECT_DIR%") do set "PROJECT_FOLDER=%%~nxI"

set "DB_NAME=gsmstunter"
set "DB_USER=root"
set "DB_PASS="

set "MIG_DIR=%PROJECT_DIR%\database"
set "URL_CHECK=http://localhost/%PROJECT_FOLDER%/admin/system-check.php"
set "URL_ADMIN=http://localhost/%PROJECT_FOLDER%/admin/login.php"

echo ==============================================
echo Setup + Health Check
echo Project: %PROJECT_DIR%
echo Database: %DB_NAME%
echo ==============================================

if exist "%XAMPP_DIR%\xampp_start.exe" (
  start "" "%XAMPP_DIR%\xampp_start.exe"
) else (
  echo [WARN] xampp_start.exe not found at %XAMPP_DIR%
)

if not exist "%MYSQL_EXE%" (
  echo [ERROR] mysql.exe not found: %MYSQL_EXE%
  echo Fix XAMPP_DIR in this file and run again.
  pause
  exit /b 1
)

timeout /t 3 /nobreak >nul

echo.
echo Running SQL migrations...

call :RunSql "%MIG_DIR%\schema.sql"
call :RunSql "%MIG_DIR%\migration_v2_admin_control.sql"
call :RunSql "%MIG_DIR%\migration_v3_categories_and_views.sql"
call :RunSql "%MIG_DIR%\migration_v4_pricing_global_discount.sql"
call :RunSql "%MIG_DIR%\migration_v5_products_dynamic_adjust.sql"
call :RunSql "%MIG_DIR%\migration_v6_section_keys.sql"
call :RunSql "%MIG_DIR%\migration_v7_ecommerce_core.sql"
call :RunSql "%MIG_DIR%\migration_v8_seed_products.sql"
call :RunSql "%MIG_DIR%\migration_v9_product_specs.sql"

echo.
echo Opening admin + system check pages...
start "" "%URL_ADMIN%"
start "" "%URL_CHECK%"

echo.
echo Done.
echo If any migration failed, read messages above.
pause
exit /b 0

:RunSql
set "FILE=%~1"
if not exist "%FILE%" (
  echo [SKIP] Missing: %FILE%
  goto :eof
)

echo [RUN ] %~nx1
if defined DB_PASS (
  "%MYSQL_EXE%" -u%DB_USER% -p%DB_PASS% "%DB_NAME%" < "%FILE%"
) else (
  "%MYSQL_EXE%" -u%DB_USER% "%DB_NAME%" < "%FILE%"
)

if errorlevel 1 (
  echo [FAIL] %~nx1
) else (
  echo [ OK ] %~nx1
)
goto :eof

