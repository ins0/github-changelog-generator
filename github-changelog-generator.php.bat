@ECHO OFF
SET BIN_TARGET=%~dp0/github-changelog-generator-cli.php
php "%BIN_TARGET%" %*
