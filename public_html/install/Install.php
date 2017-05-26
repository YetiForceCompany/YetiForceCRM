<?php
chdir(__DIR__ . '/../../install/');
\App\Config::$isPublicDir = true;
require 'Install.php';
