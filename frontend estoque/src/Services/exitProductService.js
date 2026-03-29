import api from "../composables/useApi"

const toIntOrNull = (value) => {
  const parsed = Number.parseInt(value, 10)
  return Number.isFinite(parsed) ? parsed : null
}

const buildExitPayload = (data = {}) => {
  const productId = toIntOrNull(data.productId ?? data.product_id ?? data.produto_id)
  const quantity = toIntOrNull(data.quantity ?? data.quantidade)
  const userId = toIntOrNull(data.userId ?? data.user_id ?? data.usuario_id ?? data.registered_by)
  const userName = data.userName ?? data.user_name ?? data.nome_usuario ?? ''
  const name = data.name ?? data.productName ?? data.nome ?? ''
  const date = data.date ?? data.data_de_saida ?? new Date().toISOString().slice(0, 10)
  const reason = data.reason ?? data.razao ?? data.motivo ?? 'Saida de estoque'

  return {
    productId,
    product_id: productId,
    produto_id: productId,
    quantity,
    quantidade: quantity,
    name,
    nome: name,
    productName: name,
    userId,
    user_id: userId,
    userid: userId,
    id_user: userId,
    users_id: userId,
    usuario_id: userId,
    user: userId,
    registered_by: userId,
    registeredBy: userId,
    userName,
    user_name: userName,
    nome_usuario: userName,
    registered_by_name: userName,
    registrado_por: userName,
    date,
    data_de_saida: date,
    reason,
    razao: reason,
    motivo: reason
  }
}

export const getExits = () => {
  return api.get("/exitProduct").then(res => res.data)
}

export const createExit = (data) => {
  return api.post("/exitProduct", buildExitPayload(data)).then(res => res.data)
}

export const updateExit = (id, data) => {
  return api.put(`/exitProduct/${id}`, buildExitPayload(data)).then(res => res.data)
}

export const deleteExit = (id) => {
  return api.delete(`/exitProduct/${id}`).then(res => res.data)
}