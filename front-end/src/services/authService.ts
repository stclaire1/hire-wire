import axios from 'axios'

// Configure axios base URL
const API_URL = 'http://localhost:8000/api'

axios.defaults.baseURL = API_URL
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

// Add a request interceptor to include auth token
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Add a response interceptor to handle token expiration
axios.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response?.status === 401) {
      // Only redirect if we're not already on login/register pages and have a stored token
      const currentPath = window.location.pathname
      const hasToken = localStorage.getItem('auth_token')
      
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      
      // Only redirect if we have a token (meaning user was previously authenticated)
      // and we're not already on authentication pages
      if (hasToken && !currentPath.includes('/login') && !currentPath.includes('/register')) {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export interface User {
  id: number
  name: string
  cpf: string
  email: string
  email_verified_at: string | null
  created_at: string
  updated_at: string
}

export interface LoginData {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  cpf: string
  email: string
  password: string
  password_confirmation: string
}

export interface AuthResponse {
  success: boolean
  message: string
  data?: {
    user: User
    token: string
    token_type: string
  }
  errors?: any
}

class AuthService {
  async login(data: LoginData): Promise<AuthResponse> {
    try {
      const response = await axios.post('/login', data)
      
      if (response.data.success && response.data.data) {
        localStorage.setItem('auth_token', response.data.data.token)
        localStorage.setItem('user', JSON.stringify(response.data.data.user))
      }
      
      return response.data
    } catch (error: any) {
      return error.response?.data || { success: false, message: 'Network error' }
    }
  }

  async register(data: RegisterData): Promise<AuthResponse> {
    try {
      const response = await axios.post('/register', data)
      
      if (response.data.success && response.data.data) {
        localStorage.setItem('auth_token', response.data.data.token)
        localStorage.setItem('user', JSON.stringify(response.data.data.user))
      }
      
      return response.data
    } catch (error: any) {
      return error.response?.data || { success: false, message: 'Network error' }
    }
  }

  async logout(): Promise<void> {
    try {
      await axios.post('/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
    }
  }

  async getUser(): Promise<User | null> {
    try {
      const response = await axios.get('/user')
      return response.data.data.user
    } catch (error) {
      return null
    }
  }

  getStoredUser(): User | null {
    const user = localStorage.getItem('user')
    return user ? JSON.parse(user) : null
  }

  getToken(): string | null {
    return localStorage.getItem('auth_token')
  }

  isAuthenticated(): boolean {
    return !!this.getToken()
  }
}

export default new AuthService()
