import axios, { AxiosError } from 'axios'
import type { CreateAccountData, AccountResponse, AccountsListResponse } from '@/types'

class AccountService {
  /**
   * centralized error handling for consistent error responses
   */
  private handleError(error: AxiosError | Error): AccountResponse | AccountsListResponse {
    if (axios.isAxiosError(error)) {
      return error.response?.data || { 
        success: false, 
        message: error.message || 'Network error',
        errors: { network: 'Failed to connect to server' }
      }
    }
    
    return { 
      success: false, 
      message: 'An unexpected error occurred',
      errors: { general: error.message }
    }
  }

  /**
   * request handler to reduce code duplication
   */
  private async makeRequest<T>(
    requestFn: () => Promise<any>
  ): Promise<T> {
    try {
      const response = await requestFn()
      return response.data
    } catch (error: any) {
      return this.handleError(error) as T
    }
  }

  /**
   * get all accounts for the authenticated user
   */
  async getAccounts(): Promise<AccountsListResponse> {
    return this.makeRequest<AccountsListResponse>(
      () => axios.get('/accounts')
    )
  }

  /**
   * create a new account
   */
  async createAccount(data: CreateAccountData): Promise<AccountResponse> {
    return this.makeRequest<AccountResponse>(
      () => axios.post('/accounts', data)
    )
  }

  /**
   * get a specific account by ID
   */
  async getAccount(id: number): Promise<AccountResponse> {
    if (!id || id <= 0) {
      return {
        success: false,
        message: 'Invalid account ID provided',
        errors: { id: 'Account ID must be a positive number' }
      }
    }

    return this.makeRequest<AccountResponse>(
      () => axios.get(`/accounts/${id}`)
    )
  }

  /**
   * delete an account
   */
  async deleteAccount(id: number): Promise<AccountResponse> {
    if (!id || id <= 0) {
      return {
        success: false,
        message: 'Invalid account ID provided',
        errors: { id: 'Account ID must be a positive number' }
      }
    }

    return this.makeRequest<AccountResponse>(
      () => axios.delete(`/accounts/${id}`)
    )
  }
}

export default new AccountService()
