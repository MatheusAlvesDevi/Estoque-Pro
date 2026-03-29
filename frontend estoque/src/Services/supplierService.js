import api from "../composables/useApi"

const removeNullish = (obj = {}) => {
  const cleaned = { ...obj }
  Object.keys(cleaned).forEach((key) => {
    if (cleaned[key] === null || cleaned[key] === undefined || cleaned[key] === '') {
      delete cleaned[key]
    }
  })
  return cleaned
}

const buildSupplierPayload = (data = {}) => {
  const name = data.name ?? data.nome ?? ''
  const email = data.email ?? ''
  const cnpj = data.cnpj ?? data.CNPJ ?? ''
  const tel = data.tel ?? data.phone ?? data.telefone ?? ''

  return removeNullish({
    name,
    email,
    cnpj,
    CNPJ: cnpj,
    tel,
    phone: tel
  })
}

export const getSuppliers = () => {
  return api.get("/supply").then(res => res.data)
}

export const createSupplier = (data) => {
  return api.post("/supply", buildSupplierPayload(data)).then(res => res.data)
}

export const updateSupplier = (id, data) => {
  return api.put(`/supply/${id}`, buildSupplierPayload(data)).then(res => res.data)
}

export const deleteSupplier = (id) => {
  return api.delete(`/supply/${id}`).then(res => res.data)
}