import api from "../composables/useApi"

const toNumberOrNull = (value) => {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : null
}

const removeNullish = (obj = {}) => {
  const cleaned = { ...obj }
  Object.keys(cleaned).forEach((key) => {
    if (cleaned[key] === null || cleaned[key] === undefined || cleaned[key] === '') {
      delete cleaned[key]
    }
  })
  return cleaned
}

const buildProductPayload = (data = {}) => {
  const name = data.name ?? data.nome ?? ''
  const code = data.code ?? data.codigo ?? ''
  const price = toNumberOrNull(data.price ?? data.preco)
  const quantity = toNumberOrNull(data.quantity ?? data.quantidade)
  const minimumstock = toNumberOrNull(
    data.minimumstock ??
    data.minimumStock ??
    data.minimum_stock ??
    data.minStock ??
    data.min_stock ??
    data.estoque_minimo ??
    data.estoqueMinimo
  )
  const supplier_id = toNumberOrNull(
    data.supplier_id ??
    data.supplierId ??
    data.supplier?.id ??
    data.fornecedor_id ??
    data.id_fornecedor
  )

  const payload = {
    name,
    code,
    price,
    quantity,
    minimumstock,
    supplier_id
  }

  return removeNullish(payload)
}

export const getProducts = () => {
  return api.get("/products").then(res => res.data)
}

export const createProduct = (data) => {
  const payload = buildProductPayload(data)
  return api.post('/products', payload).then((res) => res.data)
}

export const updateProduct = (id, data) => {
  return api.put(`/products/${id}`, buildProductPayload(data)).then(res => res.data)
}

export const deleteProduct = (id) => {
  return api.delete(`/products/${id}`).then(res => res.data)
}