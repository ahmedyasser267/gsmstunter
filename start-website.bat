@echo off
setlocal
cd /d "%~dp0"

set "XAMPP_DIR=D:\xampp"
set "PROJECT_DIR=%cd%"
for %%I in ("%PROJECT_DIR%") do set "PROJECT_FOLDER=%%~nxI"
set "PROJECT_URL=http://localhost/%PROJECT_FOLDER%/"
set "ADMIN_URL=http://localhost/%PROJECT_FOLDER%/admin/login.php"
set "CHECK_URL=http://localhost/%PROJECT_FOLDER%/admin/system-check.php"

echo ==============================================
echo Starting GSMStunter with XAMPP...
echo Project URL: %PROJECT_URL%
echo ==============================================

if exist "%XAMPP_DIR%\xampp_start.exe" (
  start "" "%XAMPP_DIR%\xampp_start.exe"
) else (
  echo [WARN] xampp_start.exe not found at %XAMPP_DIR%
  echo        Start Apache/MySQL manually from XAMPP Control Panel.
)

echo Waiting for Apache/MySQL...
timeout /t 5 /nobreak >nul

start "" "%PROJECT_URL%"
start "" "%ADMIN_URL%"
start "" "%CHECK_URL%"

echo.
echo Opened:
echo - %PROJECT_URL%
echo - %ADMIN_URL%
echo - %CHECK_URL%
echo.
echo IMPORTANT: If system-check shows FAIL, run SQL files in database/ then refresh.
pause
endlocal

