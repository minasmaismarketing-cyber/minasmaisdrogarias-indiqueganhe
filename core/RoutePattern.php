<?php

declare(strict_types=1);

/**
 * Compila rotas com placeholders ({id}, {slug}, etc.) em regex e extrai parâmetros.
 */
final class RoutePattern
{
    /** @var list<string> */
    private array $paramNames;

    private string $regex;

    private int $literalSegmentCount;

    /**
     * @param list<string> $paramNames
     */
    private function __construct(string $regex, array $paramNames, int $literalSegmentCount)
    {
        $this->regex = $regex;
        $this->paramNames = $paramNames;
        $this->literalSegmentCount = $literalSegmentCount;
    }

    /**
     * Compila o path da rota. Retorna null se a rota for estática (sem placeholders).
     */
    public static function compile(string $path): ?self
    {
        if (!str_contains($path, '{')) {
            return null;
        }

        $segments = explode('/', trim($path, '/'));
        $paramNames = [];
        $regexParts = [];
        $literalCount = 0;

        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }

            if (preg_match('/^\{([a-zA-Z]+)\}$/', $segment, $matches) === 1) {
                $name = $matches[1];
                $paramNames[] = $name;
                $regexParts[] = self::segmentPattern($name);
                continue;
            }

            $literalCount++;
            $regexParts[] = preg_quote($segment, '#');
        }

        if ($regexParts === []) {
            return null;
        }

        $regex = '#^/' . implode('/', $regexParts) . '$#';

        return new self($regex, $paramNames, $literalCount);
    }

    private static function segmentPattern(string $name): string
    {
        return match (strtolower($name)) {
            'id' => '(\d+)',
            'uuid' => '([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12})',
            'slug' => '([a-z0-9]+(?:-[a-z0-9]+)*)',
            'codigo' => '([A-Za-z0-9]+)',
            default => '([^/]+)',
        };
    }

    /**
     * @return array<string, string>|null
     */
    public function match(string $path): ?array
    {
        if (preg_match($this->regex, $path, $matches) !== 1) {
            return null;
        }

        $params = [];

        foreach ($this->paramNames as $index => $name) {
            $params[$name] = $matches[$index + 1] ?? '';
        }

        return $params;
    }

    public function literalSegmentCount(): int
    {
        return $this->literalSegmentCount;
    }
}
