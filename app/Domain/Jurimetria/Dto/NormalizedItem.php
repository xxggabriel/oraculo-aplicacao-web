<?php

namespace App\Domain\Jurimetria\Dto;

class NormalizedItem
{
    public function __construct(
        public string $id,
        public ?string $itemBrutoId,
        public ?string $processo,
        public ?string $classe,
        public ?string $assunto,
        public ?string $partes,
        public ?string $relator,
        public ?string $orgaoJulgador,
        public ?string $teor,
        public ?string $resumo,
        public ?int $grau,
        public ?string $dataDistribuicao,
        public ?string $dataJulgamento,
        public ?string $classificacao,
        public ?float $classificacaoConfianca,
        public array $classificacaoEmbedding = [],
        public ?string $classificacaoModelo = null,
        public ?string $classificacaoVersao = null,
        public ?string $classificadoEm = null,
        public array $metadata = [],
        public array $raw = [],
    ) {
    }

    /** @param array<string, mixed> $hit */
    public static function fromHit(array $hit): self
    {
        $source = $hit['_source'] ?? [];

        $toString = static function ($value): ?string {
            if (is_array($value)) {
                return implode('; ', array_filter(array_map('strval', $value)));
            }

            return $value !== null ? (string) $value : null;
        };

        // Campos seguem o mapping do índice itens-normalizados.
        return new self(
            id: (string) ($source['id'] ?? $hit['_id'] ?? ''),
            itemBrutoId: $toString($source['item_bruto_id'] ?? null),
            processo: $toString($source['processo'] ?? null),
            classe: $toString($source['classe'] ?? null),
            assunto: $toString($source['assunto'] ?? null),
            partes: $toString($source['partes'] ?? null),
            relator: $toString($source['relator'] ?? null),
            orgaoJulgador: $toString($source['orgao_julgador'] ?? null),
            teor: $toString($source['teor'] ?? null),
            resumo: $toString($source['resumo'] ?? null),
            grau: isset($source['grau']) ? (int) $source['grau'] : null,
            dataDistribuicao: $toString($source['data_distribuicao'] ?? null),
            dataJulgamento: $toString($source['data_julgamento'] ?? null),
            classificacao: $toString($source['classificacao'] ?? null),
            classificacaoConfianca: isset($source['classificacao_confianca']) ? (float) $source['classificacao_confianca'] : null,
            classificacaoEmbedding: (array) ($source['classificacao_embedding'] ?? []),
            classificacaoModelo: $toString($source['classificacao_modelo'] ?? null),
            classificacaoVersao: $toString($source['classificacao_versao'] ?? null),
            classificadoEm: $toString($source['classificado_em'] ?? null),
            metadata: (array) ($source['metadata'] ?? []),
            raw: $hit,
        );
    }
}
