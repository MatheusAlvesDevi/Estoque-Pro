import api from "../composables/useApi"

export const getUsers = () => {
  return api.get("/users").then(res => res.data)
}

export const createUser = (data) => {
  return api.post("/users", data).then(res => res.data)
}

export const updateUser = (id, data) => {
  return api.put(`/users/${id}`, data).then(res => res.data)
}

export const deleteUser = (id) => {
  return api.delete(`/users/${id}`).then(res => res.data)
}