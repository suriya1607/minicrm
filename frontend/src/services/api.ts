import api from '@/api/axios'

export const fetchRoles = async (url: string) => {
  const res = await api.get(url)
  return res.data.data
}

export const setPassword = async (url: string, data: any) => {
  const res = await api.post(url, data)
  return res.data
}

export const createUser = async (data: any) => {
  const res = await api.post('/users', data);
  return res.data;
};

export const updateUser = async (id: string | number, data: any) => {
  const res = await api.put(`/users/${id}`, data); // or patch
  return res.data;
};

export const fetchUser = async (url: string) => {
  const res = await api.get(url)
  return res.data.data
}

export const updateProfile = async (data: any) => {
  const res = await api.post('/profile/update', data, {
    headers: {
      "Content-Type": "multipart/form-data"
    }
  });

  return res.data;
}

export const deleteUser = async (id: string | number) => {
  const res = await api.delete(`/users/${id}`);
  return res.data;
}

export const updatePassword = async (data: any) => {
  const res = await api.post('/updatepassword', data);
  return res.data;
}

// contacts

export const createContact = async (data: any) => {
  const res = await api.post('/contacts', data);
  return res.data;
};

export const updateContact = async (id: string | number, data: any) => {
  const res = await api.put(`/contacts/${id}`, data);
  return res.data;
};

export const fetchContact = async (id: string | number) => {
  const res = await api.get(`/contacts/${id}`);
  return res.data.data;
};

export const deleteContact = async (id: string | number) => {
  const res = await api.delete(`/contacts/${id}`);
  return res.data;
};
