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

        // Campos seguem o mapping do índice itens-normalizados.
        return new self(
            id: (string) ($source['id'] ?? $hit['_id'] ?? ''),
            itemBrutoId: $source['item_bruto_id'] ?? null,
            processo: $source['processo'] ?? null,
            classe: $source['classe'] ?? null,
            assunto: $source['assunto'] ?? null,
            partes: $source['partes'] ?? null,
            relator: $source['relator'] ?? null,
            orgaoJulgador: $source['orgao_julgador'] ?? null,
            teor: $source['teor'] ?? null,
            resumo: $source['resumo'] ?? null,
            grau: isset($source['grau']) ? (int) $source['grau'] : null,
            dataDistribuicao: $source['data_distribuicao'] ?? null,
            dataJulgamento: $source['data_julgamento'] ?? null,
            classificacao: $source['classificacao'] ?? null,
            classificacaoConfianca: isset($source['classificacao_confianca']) ? (float) $source['classificacao_confianca'] : null,
            classificacaoEmbedding: (array) ($source['classificacao_embedding'] ?? []),
            classificacaoModelo: $source['classificacao_modelo'] ?? null,
            classificacaoVersao: $source['classificacao_versao'] ?? null,
            classificadoEm: $source['classificado_em'] ?? null,
            metadata: (array) ($source['metadata'] ?? []),
            raw: $hit,
        );
    }
}
