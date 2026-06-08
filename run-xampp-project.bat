@echo off
setlocal EnableExtensions

REM Run this file from inside your project folder (copied under D:\xampp\htdocs\...)
REM It will start Apache + MySQL (if not running) and open project URLs.

set "XAMPP_DIR=D:\xampp"
set "PROJECT_DIR=%~dp0"

REM Remove trailing backslash from PROJECT_DIR
if "%PROJECT_DIR:~-1%"=="\" set "PROJECT_DIR=%PROJECT_DIR:~0,-1%"

for %%I in ("%PROJECT_DIR%") do set "PROJECT_FOLDER=%%~nxI"

set "PROJECT_URL=http://localhost/%PROJECT_FOLDER%/"
set "ADMIN_URL=http://localhost/%PROJECT_FOLDER%/admin/login.php"
set "CHECK_URL=http://localhost/%PROJECT_FOLDER%/admin/system-check.php"
set "PHPMYADMIN_URL=http://localhost/phpmyadmin"

echo ==============================================
echo Starting XAMPP and opening the project...
echo XAMPP: %XAMPP_DIR%
echo Project URL: %PROJECT_URL%
echo ==============================================

if exist "%XAMPP_DIR%\xampp_start.exe" (
  start "" "%XAMPP_DIR%\xampp_start.exe"
) else (
  echo [WARN] xampp_start.exe not found at %XAMPP_DIR%
  echo        Start Apache/MySQL manually from XAMPP Control Panel.
)

echo Waiting for local server...
timeout /t 5 /nobreak >nul

start "" "%PROJECT_URL%"
start "" "%ADMIN_URL%"
start "" "%CHECK_URL%"
start "" "%PHPMYADMIN_URL%"

echo Done.
echo If the site does not load, make sure Apache and MySQL are running.
echo Project: %PROJECT_URL%
echo Admin:   %ADMIN_URL%
pause
endlocal

