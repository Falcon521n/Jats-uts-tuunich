<?php

declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

(new App\Controllers\ReportController($pdo))->index();
