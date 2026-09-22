<?php

declare(strict_types=1);

return [
    'technical_catalog' => getenv('TECHNICAL_CATALOG_PATH') ?: dirname(__DIR__) . '/data/technical-parameters.csv',
    'ppv_sheet_name' => getenv('PPV_SHEET_NAME') ?: 'PPV',
    'max_upload_mb' => (int) (getenv('MAX_UPLOAD_MB') ?: 30),
];
