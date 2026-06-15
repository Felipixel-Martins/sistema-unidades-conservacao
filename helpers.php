<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string
{
    return $path . '?v=20260512-2';
}

function redirect(string $url): never
{
    header("Location: {$url}");
    exit;
}

function getIntParam(array $source, string $key): ?int
{
    $value = filter_var($source[$key] ?? null, FILTER_VALIDATE_INT);

    return $value === false ? null : $value;
}

function formatDate(?string $value, string $format = 'd/m/Y'): string
{
    if (!$value) {
        return 'Nao informado';
    }

    $timestamp = strtotime($value);

    return $timestamp ? date($format, $timestamp) : 'Nao informado';
}

function normalizeUnitName(?string $value): string
{
    $normalized = mb_strtolower(trim((string) $value), 'UTF-8');

    return strtr($normalized, [
        'á' => 'a',
        'à' => 'a',
        'ã' => 'a',
        'â' => 'a',
        'ä' => 'a',
        'é' => 'e',
        'è' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'í' => 'i',
        'ì' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ó' => 'o',
        'ò' => 'o',
        'õ' => 'o',
        'ô' => 'o',
        'ö' => 'o',
        'ú' => 'u',
        'ù' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'ç' => 'c',
    ]);
}

function isApaBaleiaFranca(array $unidade): bool
{
    return normalizeUnitName($unidade['nome'] ?? '') === normalizeUnitName('APA da Baleia Franca');
}

function isSerraDoTabuleiro(array $unidade): bool
{
    return normalizeUnitName($unidade['nome'] ?? '') === normalizeUnitName('Parque Estadual da Serra do Tabuleiro');
}

function isRioVermelho(array $unidade): bool
{
    return normalizeUnitName($unidade['nome'] ?? '') === normalizeUnitName('Parque Estadual do Rio Vermelho');
}

function isArvoredo(array $unidade): bool
{
    return normalizeUnitName($unidade['nome'] ?? '') === normalizeUnitName('Reserva Biológica Marinha do Arvoredo');
}

function getUnitImage(array $unidade): string
{
    if (isApaBaleiaFranca($unidade)) {
        return asset('assets/logo-baleia.png');
    }

    if (isSerraDoTabuleiro($unidade)) {
        return asset('assets/serra-do-tabuleiro.png');
    }

    if (isRioVermelho($unidade)) {
        return asset('assets/rio-vermelho.png');
    }

    if (isArvoredo($unidade)) {
        return asset('assets/reserva-marinha.jpg');
    }

    return (string) ($unidade['imagem'] ?? '');
}

function getUnitImageVariant(array $unidade): string
{
    if (
        isApaBaleiaFranca($unidade)
        || isSerraDoTabuleiro($unidade)
        || isRioVermelho($unidade)
    ) {
        return 'brand';
    }

    return 'photo';
}
