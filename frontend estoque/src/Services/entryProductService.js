import api from "../composables/useApi"

const toIntOrNull = (value) => {
  const parsed = Number.parseInt(value, 10)
  return Number.isFinite(parsed) ? parsed : null
}

const buildEntryPayload = (data = {}) => {
  const productId = toIntOrNull(data.productId ?? data.product_id ?? data.produto_id)
  const supplierId = toIntOrNull(data.supplierId ?? data.supplier_id ?? data.fornecedor_id)
  const quantity = toIntOrNull(data.quantity ?? data.quantidade)
  const userId = toIntOrNull(data.userId ?? data.user_id ?? data.usuario_id ?? data.registered_by)
  const userName = data.userName ?? data.user_name ?? data.nome_usuario ?? ''
  const name = data.name ?? data.productName ?? data.nome ?? ''
  const date = data.date ?? data.data_de_entrada ?? new Date().toISOString().slice(0, 10)
  const reason = data.reason ?? data.razao ?? data.motivo ?? 'Entrada de estoque'

  return {
    productId,
    product_id: productId,
    produto_id: productId,
    supplierId,
    supplier_id: supplierId,
    fornecedor_id: supplierId,
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
    data_de_entrada: date,
    reason,
    razao: reason,
    motivo: reason
  }
}

export const getEntries = () => {
  return api.get("/entryProduct").then(res => res.data)
}

export const createEntry = (data) => {
  return api.post("/entryProduct", buildEntryPayload(data)).then(res => res.data)
}

export const updateEntry = (id, data) => {
  return api.put(`/entryProduct/${id}`, buildEntryPayload(data)).then(res => res.data)
}

export const deleteEntry = (id) => {
  return api.delete(`/entryProduct/${id}`).then(res => res.data)
}