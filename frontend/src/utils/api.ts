import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? "http://localhost:8000/api/v1",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
  timeout: 15_000,
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem("brasero_admin_token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("brasero_admin_token");
      localStorage.removeItem("brasero_admin_user");
      window.location.href = "/admin/login";
    }
    return Promise.reject(error);
  },
);

export default api;
