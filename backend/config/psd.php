<?php

return [
    'python_bin' => env('PSD_IMPORT_PYTHON_BIN', is_file(base_path('../.venv-psd/bin/python3'))
        ? base_path('../.venv-psd/bin/python3')
        : 'python3'),
    'timeout' => (int) env('PSD_IMPORT_TIMEOUT', 180),
    'max_upload_kb' => (int) env('PSD_IMPORT_MAX_UPLOAD_KB', 524288),
];
