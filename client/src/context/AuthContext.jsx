import { createContext, useContext, useState, useCallback } from 'react';
import api from '../api/axios';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(() => {
    try {
      return JSON.parse(localStorage.getItem('orchid_user')) || null;
    } catch { return null; }
  });
  const [token, setToken] = useState(() => localStorage.getItem('orchid_token') || null);

  const login = useCallback(async (identifier, password) => {
    const res = await api.post('/auth/login', { identifier, password });
    localStorage.setItem('orchid_token', res.data.token);
    localStorage.setItem('orchid_user', JSON.stringify(res.data.user));
    setToken(res.data.token);
    setUser(res.data.user);
    return res.data.user;
  }, []);

  const register = useCallback(async (data) => {
    const res = await api.post('/auth/register', data);
    localStorage.setItem('orchid_token', res.data.token);
    localStorage.setItem('orchid_user', JSON.stringify(res.data.user));
    setToken(res.data.token);
    setUser(res.data.user);
    return res.data.user;
  }, []);

  const loginWithGoogle = useCallback(async (googleData = {}) => {
    const res = await api.post('/auth/google', googleData);
    localStorage.setItem('orchid_token', res.data.token);
    localStorage.setItem('orchid_user', JSON.stringify(res.data.user));
    setToken(res.data.token);
    setUser(res.data.user);
    return res.data.user;
  }, []);

  const logout = useCallback(() => {
    localStorage.removeItem('orchid_token');
    localStorage.removeItem('orchid_user');
    setToken(null);
    setUser(null);
  }, []);

  const isLoggedIn  = !!token && !!user;
  const isAdmin     = user?.role === 'admin';
  const isCashier   = user?.role === 'cashier';
  const isCustomer  = user?.role === 'customer';

  return (
    <AuthContext.Provider value={{ user, token, login, register, loginWithGoogle, logout, isLoggedIn, isAdmin, isCashier, isCustomer }}>
      {children}
    </AuthContext.Provider>
  );
}

// eslint-disable-next-line react-refresh/only-export-components
export const useAuth = () => {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used inside AuthProvider');
  return ctx;
};
