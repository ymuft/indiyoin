<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Env;

require dirname(__DIR__) . '/vendor/autoload.php';
$root = dirname(__DIR__);
Env::load($root . '/.env');
$driver = Env::get('DB_DRIVER', 'sqlite') ?? 'sqlite';
$file = $root . '/migrations/' . ($driver === 'mysql' ? '001_init.mysql.sql' : '001_init.sql');
$sql = file_get_contents($file);
if ($sql === false) {
    throw new RuntimeException('Migration file not found.');
}
Database::connection()->exec($sql);
fwrite(STDOUT, "Migration applied ({$driver}).\n");
