#!/usr/bin/env bash
set -euo pipefail
# Publica a imagem no Artifact Registry da GCP.
# Pré-requisitos:
#  - gcloud autenticado com permissões de push no repositório.
#  - Variáveis de ambiente abaixo preenchidas.

: "${GCP_PROJECT_ID:=oraculo-478817}"
: "${GCP_REGION:=us-central1}"
: "${REPO_NAME:=oraculo}"
: "${IMAGE_NAME:=oraculo-aplicacao-web}"
: "${IMAGE_TAG:=latest}"

REGISTRY="${GCP_REGION}-docker.pkg.dev"
IMAGE_PATH="${REGISTRY}/${GCP_PROJECT_ID}/${REPO_NAME}/${IMAGE_NAME}:${IMAGE_TAG}"

REGISTRY="${GCP_REGION}-docker.pkg.dev"
IMAGE_PATH="${REGISTRY}/${GCP_PROJECT_ID}/${REPO_NAME}/${IMAGE_NAME}:${IMAGE_TAG}"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PLATFORM="linux/amd64"


echo "=> Imagem: ${IMAGE_PATH}"
echo "=> Registry: ${REGISTRY}"

if [[ -n "${GOOGLE_APPLICATION_CREDENTIALS:-}" && -f "${GOOGLE_APPLICATION_CREDENTIALS}" ]]; then
  echo "=> Autenticando service account..."
  gcloud auth activate-service-account --key-file "${GOOGLE_APPLICATION_CREDENTIALS}" >/dev/null
fi

echo "=> Configurando docker para ${REGISTRY}..."
gcloud auth configure-docker "${REGISTRY}" --quiet >/dev/null

echo "=> Build da imagem..."
docker build --platform "${PLATFORM}" -t "${IMAGE_PATH}" -f "${ROOT_DIR}/Dockerfile" "${ROOT_DIR}"

echo "=> Push da imagem..."
docker push "${IMAGE_PATH}"

echo "✓ Concluído: ${IMAGE_PATH}"
