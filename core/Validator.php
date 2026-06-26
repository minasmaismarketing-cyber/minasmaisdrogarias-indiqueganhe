<?php

declare(strict_types=1);

class Validator
{
    public static function cpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf) ?? '';

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;
            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

    public static function telefone(string $telefone): bool
    {
        $digits = preg_replace('/\D/', '', $telefone) ?? '';

        return (bool) preg_match('/^\d{10,11}$/', $digits);
    }

    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function senha(string $senha): bool
    {
        if (strlen($senha) < 8) {
            return false;
        }

        if (!preg_match('/[A-Za-z]/', $senha)) {
            return false;
        }

        if (!preg_match('/\d/', $senha)) {
            return false;
        }

        return true;
    }

    public static function sanitizeString(string $value, int $maxLength = 255): string
    {
        $value = trim(strip_tags($value));

        if (mb_strlen($value) > $maxLength) {
            $value = mb_substr($value, 0, $maxLength);
        }

        return $value;
    }

    public static function onlyDigits(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    public static function validateCpf(string $cpf): bool
    {
        return self::cpf($cpf);
    }

    public static function validateTelefone(string $telefone): bool
    {
        return self::telefone($telefone);
    }

    public static function validateEmail(string $email): bool
    {
        return self::email($email);
    }

    public static function sanitizeEmail(string $email): string
    {
        $email = trim(strip_tags($email));

        if (mb_strlen($email) > 255) {
            $email = mb_substr($email, 0, 255);
        }

        return mb_strtolower($email);
    }
}
