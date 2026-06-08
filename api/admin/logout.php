<?php
declare(strict_types=1);
require_once __DIR__ . '/../../php/config.php';
session_destroy();
json_response(['ok' => true]);

