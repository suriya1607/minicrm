import api from '@/api/axios'

export const fetchRoles = async (url: string) => {
  const res = await api.get(url)
  return res.data.data
}