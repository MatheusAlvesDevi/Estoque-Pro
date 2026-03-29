const SENSITIVE_ERROR_PATTERNS = [
  /sqlstate/i,
  /select\s+.+\s+from\s+/i,
  /insert\s+into\s+/i,
  /update\s+.+\s+set\s+/i,
  /delete\s+from\s+/i,
  /stack\s*trace/i,
  /exception/i,
  / at .*\.php/i,
  /at line \d+/i,
  /<html/i
]

const HTTP_STATUS_MESSAGES = {
  400: 'Requisicao invalida. Revise os dados enviados.',
  401: 'Sua sessao expirou. Faca login novamente.',
  403: 'Voce nao tem permissao para executar esta acao.',
  404: 'Recurso nao encontrado.',
  409: 'Conflito de dados. Atualize a tela e tente novamente.',
  422: 'Verifique os campos informados e tente novamente.',
  429: 'Muitas tentativas em sequencia. Aguarde e tente novamente.'
}

const looksSensitive = (value = '') => {
  const text = String(value || '')
  return SENSITIVE_ERROR_PATTERNS.some((pattern) => pattern.test(text))
}

export const getSafeErrorMessage = (err, fallbackMessage = 'Nao foi possivel concluir a operacao.') => {
  const status = err?.response?.status
  const backendMessage = err?.response?.data?.message
  const backendErrors = err?.response?.data?.errors

  // For 422 responses, surface the first actionable validation message from the API.
  if (status === 422 && backendErrors && typeof backendErrors === 'object') {
    const firstKey = Object.keys(backendErrors)[0]
    const firstValue = firstKey ? backendErrors[firstKey] : null
    const firstMessage = Array.isArray(firstValue) ? firstValue[0] : firstValue

    if (typeof firstMessage === 'string' && firstMessage.trim() && !looksSensitive(firstMessage)) {
      return firstMessage.trim()
    }
  }

  if (status >= 500) {
    return 'Erro interno no servidor. Tente novamente em instantes.'
  }

  if (HTTP_STATUS_MESSAGES[status]) {
    return HTTP_STATUS_MESSAGES[status]
  }

  if (typeof backendMessage === 'string' && backendMessage.trim() && !looksSensitive(backendMessage)) {
    return backendMessage.trim()
  }

  return fallbackMessage
}
