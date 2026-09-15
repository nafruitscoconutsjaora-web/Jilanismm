<?php

use App\Core\Env;

return [
    'name' => Env::get('APP_NAME', 'SMM Panel'),
    'env' => Env::get('APP_ENV', 'production'),
    'url' => Env::get('APP_URL', 'http://localhost:3000'),
    'key' => Env::get('APP_KEY', 'base64:smm_panel_key'),
    'theme' => 'classic',
    'debug' => Env::get('APP_ENV') === 'local',
];
