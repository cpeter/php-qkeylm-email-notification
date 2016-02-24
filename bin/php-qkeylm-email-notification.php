#!/usr/bin/env php
<?php
/*
 * This file is part of PHP CMS Version Checker.
 *
 * (c) Csaba Peter <peter_csaba@yahoo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Try to find the appropriate autoloader.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} elseif (__DIR__ . '/../../../autoload.php') {
    require __DIR__ . '/../../../autoload.php';
}

$application = new Cpeter\PhpQkeylmEmailNotification\Console\Application();
$application->run();
