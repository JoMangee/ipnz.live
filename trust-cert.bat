@echo off
REM Run this as Administrator to trust the localhost certificate
REM Right-click this file and select "Run as administrator"

setlocal enabledelayedexpansion

cd /d "%~dp0"

echo Removing old certificate if it exists...
certutil -delstore -f "Root" localhost 2>nul

echo.
echo Adding new certificate with SAN entries...
certutil -addstore -f "Root" docker\ssl\localhost.crt

if %ERRORLEVEL% EQU 0 (
    echo.
    echo SUCCESS: Certificate with SAN entries added to Trusted Root Certification Authorities
    echo Please close all browsers completely and reopen them.
    echo Then visit: https://localhost:8443
    pause
) else (
    echo.
    echo FAILED: Could not add certificate. Make sure you run this as Administrator.
    pause
)
