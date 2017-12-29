@echo off
echo Select test suite.
echo 1 - All.
echo 2 - Init.
echo 3 - Entity.
echo 4 - Settings.
echo 5 - Gui.
echo 6 - Finish.
set /p testMode=Enter a number:

echo.
IF %testMode%==1 goto All
IF %testMode%==2 goto Init
IF %testMode%==3 goto Entity
IF %testMode%==4 goto Settings
IF %testMode%==5 goto Gui
IF %testMode%==6 goto Finish

EXIT /B

:All
C:\wamp\bin\php\php7.0.23\php.exe C:\www\phpunit.phar --configuration="C:\www\YetiForceCRM\tests\phpunit.xml" --debug --stderr --verbose
echo.
pause
exit

:Init
C:\wamp\bin\php\php7.0.23\php.exe C:\www\phpunit.phar --configuration="C:\www\YetiForceCRM\tests\phpunit.xml" --debug --stderr --verbose --testsuite Init
echo.
pause
exit

:Base
C:\wamp\bin\php\php7.0.23\php.exe C:\www\phpunit.phar --configuration="C:\www\YetiForceCRM\tests\phpunit.xml" --debug --stderr --verbose --testsuite Base
echo.
pause
exit

:Settings
C:\wamp\bin\php\php7.0.23\php.exe C:\www\phpunit.phar --configuration="C:\www\YetiForceCRM\tests\phpunit.xml" --debug --stderr --verbose --testsuite Settings
echo.
pause
exit

:Gui
C:\wamp\bin\php\php7.0.23\php.exe C:\www\phpunit.phar --configuration="C:\www\YetiForceCRM\tests\phpunit.xml" --debug --stderr --verbose --testsuite Gui
echo.
pause
exit

:Finish
C:\wamp\bin\php\php7.0.23\php.exe C:\www\phpunit.phar --configuration="C:\www\YetiForceCRM\tests\phpunit.xml" --debug --stderr --verbose --testsuite Finish
echo.
pause
exit
