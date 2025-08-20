import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { toast } from 'react-hot-toast';
import { apiClient } from '@/lib/api';
import { User, LoginCredentials } from '@/types';

interface AuthContextType {
  user: User | null;
  loading: boolean;
  login: (credentials: LoginCredentials) => Promise<boolean>;
  logout: () => void;
  isAuthenticated: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

interface AuthProviderProps {
  children: ReactNode;
}

export function AuthProvider({ children }: AuthProviderProps) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    console.log('AuthContext useEffect triggered');
    console.log('apiClient.isAuthenticated():', apiClient.isAuthenticated());
    
    // Kiểm tra xem có token không khi app load
    if (apiClient.isAuthenticated()) {
      console.log('Token found, loading user...');
      // Lấy thông tin user từ token
      loadUserFromToken();
    } else {
      console.log('No token found, setting loading to false');
      setLoading(false);
    }
  }, []);

  const loadUserFromToken = async () => {
    try {
      console.log('Loading user from token...');
      const response = await apiClient.getMe();
      console.log('getMe response:', response);
      
      // Backend returns JWT payload directly in response.data
      const responseData = response.data as any;
      console.log('responseData:', responseData);
      
      if (responseData && responseData.sub) {
        // Convert JWT payload to User object
        const payload = responseData;
        const user: User = {
          id: payload.sub,
          email: payload.email,
          full_name: payload.full_name,
          phone: '',
          address: '',
          user_type: payload.user_type,
          account: {
            username: payload.username,
            is_admin: false
          }
        };
        console.log('User loaded from token:', user);
        setUser(user);
      } else {
        console.log('No user data in response, responseData:', responseData);
        setUser(null);
      }
    } catch (error) {
      console.error('Error loading user from token:', error);
      // Token không hợp lệ, xóa token
      apiClient.removeToken();
      console.error('Invalid token, removed from storage');
    } finally {
      setLoading(false);
    }
  };

  const login = async (credentials: LoginCredentials): Promise<boolean> => {
    try {
      setLoading(true);
      const response = await apiClient.login(credentials);
      
      // Backend returns {user, token, message} directly
      if (response.token) {
        apiClient.setToken(response.token);
        console.log('Token saved:', response.token);
      }
      
      if (response.user) {
        setUser(response.user);
        console.log('User set:', response.user);
      }
      
      toast.success('Đăng nhập thành công!');
      return true;
    } catch (error: any) {
      const message = error.response?.data?.message || 'Đăng nhập thất bại';
      toast.error(message);
      return false;
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    try {
      await apiClient.logout();
    } catch (error) {
      // Ignore logout errors
    } finally {
      setUser(null);
      apiClient.removeToken();
      toast.success('Đăng xuất thành công!');
    }
  };

  const value: AuthContextType = {
    user,
    loading,
    login,
    logout,
    isAuthenticated: !!user,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
