<?php

use Adapters\DatabaseAdapter;
use Adapters\SqliteDatabaseAdapter;
use Adapters\LoggingDecorator;

require_once  'adapters/DatabaseAdapter.php';
require_once  'adapters/SqliteDatabaseAdapter.php';
require_once 'adapters/LoggingDecorator.php';

class DatabaseFactory {
   public static function getAdapter(): DatabaseAdapter {
        $baseAdapter = new SqliteDatabaseAdapter(); // створюємо базовий адаптер
        return new LoggingDecorator($baseAdapter);  // обгортаємо його в декоратор
    }
    }

