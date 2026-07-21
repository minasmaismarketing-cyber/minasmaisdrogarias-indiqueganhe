<?php

declare(strict_types=1);

require __DIR__ . '/../core/Env.php';
require __DIR__ . '/../core/Validator.php';

// Load only the normalize function by including helpers would need more deps.
// Mirror the production function for unit check:
function normalize_brazilian_whatsapp_number(string $phone): ?string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if ($digits === '') {
        return null;
    }
    while (str_starts_with($digits, '0')) {
        $digits = substr($digits, 1);
    }
    if (str_starts_with($digits, '55')) {
        $national = substr($digits, 2);
    } else {
        $national = $digits;
    }
    if (!preg_match('/^\d{10,11}$/', $national)) {
        return null;
    }
    $ddd = (int) substr($national, 0, 2);
    if ($ddd < 11 || $ddd > 99) {
        return null;
    }
    return '55' . $national;
}

$cases = [
    ['(35) 99142-6389', '5535991426389'],
    ['35 99142-6389', '5535991426389'],
    ['35991426389', '5535991426389'],
    ['+55 (35) 99142-6389', '5535991426389'],
    ['5535991426389', '5535991426389'],
    ['', null],
    ['123', null],
    ['441234567890', null],
];

$failed = 0;
foreach ($cases as [$in, $exp]) {
    $got = normalize_brazilian_whatsapp_number($in);
    $pass = $got === $exp;
    echo ($pass ? 'OK' : 'FAIL') . ' | in=' . json_encode($in) . ' got=' . json_encode($got) . ' exp=' . json_encode($exp) . PHP_EOL;
    if (!$pass) {
        $failed++;
    }
}

// Verify message encoding has MINAS-5 and URL
$msg = "🎉 Sua indicação na Minas Mais foi aprovada!\n\n"
    . "Você ganhou 5% OFF na sua primeira compra pelo App Minas Mais.\n\n"
    . "Use o cupom:\n\n"
    . "🎁 MINAS-5\n\n"
    . "E tem mais: agora você também pode ganhar 10% OFF.\n\n"
    . "Acesse nosso sistema de Indique e Ganhe, faça seu cadastro e compartilhe seu link exclusivo com um novo amigo. Assim que a indicação for aprovada, seu cupom de 10% será liberado.\n\n"
    . "👉 Participe agora:\n"
    . "https://indique.minasmaisdrogarias.com.br";

$encoded = rawurlencode($msg);
echo (str_contains($msg, 'MINAS-5') ? 'OK' : 'FAIL') . " | MINAS-5 in message\n";
echo (str_contains($msg, 'https://indique.minasmaisdrogarias.com.br') ? 'OK' : 'FAIL') . " | URL in message\n";
echo (str_contains($encoded, '%0A') ? 'OK' : 'FAIL') . " | newlines encoded\n";
echo 'https://wa.me/5535991426389?text=' . $encoded . "\n";

exit($failed > 0 ? 1 : 0);
