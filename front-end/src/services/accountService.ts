import axios, { AxiosError } from 'axios'
import type { 
  CreateAccountData, 
  AccountResponse, 
  AccountsListResponse, 
  DepositData, 
  DepositResponse, 
  BalanceResponse, 
  TransactionsResponse 
} from '@/types'

class AccountService {
  /**
   * centralized error handling for consistent error responses
   */
  private handleError(error: AxiosError | Error): any {
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
  async getAccount(id: string | number): Promise<AccountResponse> {
    if (!id) {
      return {
        success: false,
        message: 'Invalid account ID provided',
        errors: { id: 'Account ID is required' }
      }
    }

    return this.makeRequest<AccountResponse>(
      () => axios.get(`/accounts/${id}`)
    )
  }

  /**
   * get account balance
   */
  async getAccountBalance(id: string | number): Promise<BalanceResponse> {
    if (!id) {
      return {
        success: false,
        message: 'Invalid account ID provided',
        errors: { id: 'Account ID is required' }
      }
    }

    return this.makeRequest<BalanceResponse>(
      () => axios.get(`/accounts/${id}/balance`)
    )
  }

  /**
   * make a deposit to an account
   */
  async makeDeposit(id: string | number, data: DepositData): Promise<DepositResponse> {
    if (!id) {
      return {
        success: false,
        message: 'Invalid account ID provided',
        errors: { id: 'Account ID is required' }
      }
    }

    if (!data.amount || data.amount <= 0) {
      return {
        success: false,
        message: 'Invalid deposit amount',
        errors: { amount: 'Amount must be greater than 0' }
      }
    }

    return this.makeRequest<DepositResponse>(
      () => axios.post(`/accounts/${id}/deposit`, data)
    )
  }

  /**
   * get transaction history for an account
   */
  async getAccountTransactions(id: string | number): Promise<TransactionsResponse> {
    if (!id) {
      return {
        success: false,
        message: 'Invalid account ID provided',
        errors: { id: 'Account ID is required' }
      }
    }

    return this.makeRequest<TransactionsResponse>(
      () => axios.get(`/accounts/${id}/transactions`)
    )
  }
}

export default new AccountService()
