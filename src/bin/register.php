<?php
/** @noinspection PhpIncludeInspection */
require_once '/var/www/html/vendor/autoload.php';

use CarloNicora\Minimalism\Minimalism;
use CarloNicora\Minimalism\Services\Discovery\Models\Discovery\RunRegister;

$minimalism = new Minimalism();
$minimalism->render(RunRegister::class);