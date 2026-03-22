@echo off
echo Starting Baby Shop KE...

:: Start XAMPP Apache and MySQL
echo Starting Apache and MySQL...
"C:\xampp\xampp_start.exe"

:: Wait for XAMPP to start
timeout /t 3 /nobreak > nul

:: Open the React dev server
echo Starting React app...
cd /d "C:\xampp\htdocs\babyshopke\babyshopke-main"
start cmd /k "npm run dev"

:: Wait for React to start
timeout /t 5 /nobreak > nul

:: Open the browser automatically
echo Opening browser...
start chrome "http://localhost:8080"

echo Done! Baby Shop KE is running.