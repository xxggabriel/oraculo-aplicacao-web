<?php

namespace App\Domain\Jurimetria\Dto;

use Carbon\CarbonImmutable;

class SearchFilters
{
    public ?string $query = null;

    public bool $includeSemClassificacao = false;

    /** @var array<int, string> */
    public array $tribunais = [];

    /** @var array<int, string> */
    public array $classes = [];

    /** @var array<int, string> */
    public array $assuntos = [];

    /** @var array<int, int|string> */
    public array $graus = [];

    /** @var array<int, string> */
    public array $classificacoes = [];

    /** @var array<int, string> */
    public array $modelos = [];

    /** @var array<int, string> */
    public array $versoes = [];

    public ?float $confiancaMinima = null;

    public ?float $confiancaMaxima = null;

    public ?CarbonImmutable $dataDistribuicaoInicial = null;

    public ?CarbonImmutable $dataDistribuicaoFinal = null;

    public ?CarbonImmutable $dataJulgamentoInicial = null;

    public ?CarbonImmutable $dataJulgamentoFinal = null;

    /** @param array<string, mixed> $input */
    public static function fromArray(array $input): self
    {
        $instance = new self();

        $instance->query = filled($input['query'] ?? null) ? (string) $input['query'] : null;
        $instance->includeSemClassificacao = (bool) ($input['includeSemClassificacao'] ?? false);
        $instance->tribunais = array_values(array_filter((array) ($input['tribunais'] ?? [])));
        $instance->classes = array_values(array_filter((array) ($input['classes'] ?? [])));
        $instance->assuntos = array_values(array_filter((array) ($input['assuntos'] ?? [])));
        $instance->graus = array_values(array_filter((array) ($input['graus'] ?? [])));
        $instance->classificacoes = array_values(array_filter((array) ($input['classificacoes'] ?? [])));
        $instance->modelos = array_values(array_filter((array) ($input['modelos'] ?? [])));
        $instance->versoes = array_values(array_filter((array) ($input['versoes'] ?? [])));

        $instance->confiancaMinima = isset($input['confiancaMinima']) ? (float) $input['confiancaMinima'] : null;
        $instance->confiancaMaxima = isset($input['confiancaMaxima']) ? (float) $input['confiancaMaxima'] : null;

        $instance->dataDistribuicaoInicial = static::parseDate($input['dataDistribuicaoInicial'] ?? null);
        $instance->dataDistribuicaoFinal = static::parseDate($input['dataDistribuicaoFinal'] ?? null);
        $instance->dataJulgamentoInicial = static::parseDate($input['dataJulgamentoInicial'] ?? null);
        $instance->dataJulgamentoFinal = static::parseDate($input['dataJulgamentoFinal'] ?? null);

        return $instance;
    }

    public static function parseDate(null|string $value): ?CarbonImmutable
    {
        if (blank($value)) {
            return null;
        }

        return CarbonImmutable::parse($value);
    }
}
