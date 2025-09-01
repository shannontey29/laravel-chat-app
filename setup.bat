@echo off
setlocal

choco -v >nul 2>&1
IF %ERRORLEVEL% NEQ 0 (
    echo Chocolatey not found. Installing Chocolatey...
    powershell -NoProfile -ExecutionPolicy Bypass -Command "Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))"
) ELSE (
    echo Chocolatey already installed!
)

where make >nul 2>&1
IF %ERRORLEVEL% NEQ 0 (
    echo Installing make...
    choco install make -y
) ELSE (
    echo make already installed!
)

where zip >nul 2>&1
IF %ERRORLEVEL% NEQ 0 (
    echo Installing zip...
    choco install zip -y
) ELSE (
    echo zip already installed!
)

echo Running make build...
make build

echo Setup and build completed!
pause
