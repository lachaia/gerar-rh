<?php
//
// google_service_account.php | Carrega a conta de serviço do Google Calendar
// a partir do arquivo JSON apontado por GOOGLE_CALENDAR_CREDENTIALS (.env).
// O JSON em si nunca é versionado (ver .gitignore, app/chaves/).
//

require_once __DIR__ . '/env.php';

$credentialsPath = $_ENV['GOOGLE_CALENDAR_CREDENTIALS']
    ?? (__DIR__ . '/../chaves/google_calendar_service_account.json');

$google_service_account = json_decode(file_get_contents($credentialsPath), true);
