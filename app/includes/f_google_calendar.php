<?php
//
//- f_google_calendar.php | Integração com Google Calendar via conta de serviço com
//- delegação em todo o domínio (gerar.org.br) - cria eventos "como se fosse" o
//- usuário do Workspace informado em $organizador_email, com Google Meet automático.
//- (C)haia, 28/08/2026
//
//- Não usa a biblioteca oficial do Google (google/apiclient) porque este projeto não
//- usa Composer - autentica via JWT assinado manualmente com openssl_sign(), trocado
//- por um access token no endpoint padrão do OAuth2 (fluxo "service account" com
//- "subject" para impersonar o usuário).
//

function google_base64url(string $dados): string
{
    return rtrim(strtr(base64_encode($dados), '+/', '-_'), '=');
}

//- Obtém um access token válido por ~1h, impersonando $organizador_email.
//- Retorna null e preenche $erro em caso de falha.
function google_obter_token(string $organizador_email, string $escopo, ?string &$erro = null): ?string
{
    include __DIR__ . "/google_service_account.php";

    $agora = time();
    $header = google_base64url(json_encode(["alg" => "RS256", "typ" => "JWT"]));
    $claim = google_base64url(json_encode([
        "iss" => $google_service_account['client_email'],
        "scope" => $escopo,
        "aud" => $google_service_account['token_uri'],
        "exp" => $agora + 3600,
        "iat" => $agora,
        "sub" => $organizador_email,
    ]));

    if (!openssl_sign("$header.$claim", $assinatura, $google_service_account['private_key'], OPENSSL_ALGO_SHA256)) {
        $erro = "Falha ao assinar o JWT.";
        return null;
    }
    $jwt = "$header.$claim." . google_base64url($assinatura);

    $ch = curl_init($google_service_account['token_uri']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
        "assertion" => $jwt,
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resposta = curl_exec($ch);
    curl_close($ch);

    $dados = json_decode($resposta, true);
    if (empty($dados['access_token'])) {
        $erro = "Falha ao obter token: " . ($dados['error_description'] ?? $resposta);
        return null;
    }
    return $dados['access_token'];
}

//- Cria um evento com Google Meet automático na agenda de $organizador_email,
//- convidando $convidados_email (array). Retorna ["meetLink" => ..., "eventoId" => ...]
//- ou null em caso de falha (preenche $erro).
function google_criar_evento_screening(
    string $organizador_email,
    array $convidados_email,
    string $titulo,
    string $inicio_iso,
    string $fim_iso,
    ?string &$erro = null
): ?array {
    $token = google_obter_token($organizador_email, "https://www.googleapis.com/auth/calendar", $erro);
    if (!$token) {
        return null;
    }

    $requestId = uniqid("screening_", true);
    $payload = [
        "summary" => $titulo,
        "start" => ["dateTime" => $inicio_iso, "timeZone" => "America/Sao_Paulo"],
        "end" => ["dateTime" => $fim_iso, "timeZone" => "America/Sao_Paulo"],
        "attendees" => array_map(fn($email) => ["email" => $email], $convidados_email),
        "conferenceData" => [
            "createRequest" => [
                "requestId" => $requestId,
                "conferenceSolutionKey" => ["type" => "hangoutsMeet"],
            ],
        ],
    ];

    $ch = curl_init("https://www.googleapis.com/calendar/v3/calendars/primary/events?conferenceDataVersion=1&sendUpdates=all");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$token}",
        "Content-Type: application/json",
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resposta = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $dados = json_decode($resposta, true);
    if ($httpCode !== 200 || empty($dados['id'])) {
        $erro = "Falha ao criar evento: " . ($dados['error']['message'] ?? $resposta);
        return null;
    }

    return [
        "eventoId" => $dados['id'],
        "meetLink" => $dados['hangoutLink'] ?? ($dados['conferenceData']['entryPoints'][0]['uri'] ?? null),
    ];
}
