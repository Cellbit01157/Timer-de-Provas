@echo off
title Timer de Provas - Servidor Local
cls
echo ========================================================
echo         TIMER DE PROVAS - SERVIDOR LOCAL PHP
echo ========================================================
echo.
echo Iniciando servidor em http://localhost:8000 ...
echo Pressione Ctrl+C para encerrar o servidor.
echo.

set PHP_EXE=%LOCALAPPDATA%\php\php.exe

if not exist "%PHP_EXE%" (
    where php >nul 2>nul
    if %ERRORLEVEL% EQU 0 (
        set PHP_EXE=php
    ) else (
        echo [ERRO] Executavel do PHP nao encontrado!
        pause
        exit /b 1
    )
)

start "" http://localhost:8000
"%PHP_EXE%" -S 127.0.0.1:8000 -t "%~dp0"
pause
