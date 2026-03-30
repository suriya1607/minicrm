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

export const fetchUsers = async () => {
  const res = await api.get('/users', { params: { per_page: 100 } });
  return res.data.data.map((u: any) => ({ label: u.name, value: u.id }));
};

// leads 

export const createLead = async (data: any) => {
  const res = await api.post('/leads', data);
  return res.data;
};

export const updateLead = async (id: string | number, data: any) => {
  const res = await api.put(`/leads/${id}`, data);
  return res.data;
};

export const fetchLead = async (id: string | number) => {
  const res = await api.get(`/leads/${id}`);
  return res.data.data;
};

export const deleteLead = async (id: string | number) => {
  const res = await api.delete(`/leads/${id}`);
  return res.data;
};

export const convertLead = async (id: string | number, data: any) => {
  const res = await api.post(`/leads/${id}/convert`, data);
  return res.data;
};

// Deals
export const createDeal = async (data: any) => {
  const res = await api.post('/deals', data);
  return res.data;
};

export const updateDeal = async (id: string | number, data: any) => {
  const res = await api.put(`/deals/${id}`, data);
  return res.data;
};

export const fetchDeal = async (id: string | number) => {
  const res = await api.get(`/deals/${id}`);
  return res.data.data;
};

export const deleteDeal = async (id: string | number) => {
  const res = await api.delete(`/deals/${id}`);
  return res.data;
};

export const moveDeal = async (id: string | number, stage: string) => {
  const res = await api.patch(`/deals/${id}/move`, { stage });
  return res.data;
};