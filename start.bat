@echo off
php bin\console app:test-stripe-checkout
symfony server:start
pause