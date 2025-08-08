import accountService from './accountService'
import type { DepositData, DepositResponse, BalanceResponse, TransactionsResponse } from '@/types'

class DepositService {
  /**
   * make a deposit to a specific account
   */
  async makeDeposit(accountId: string | number, data: DepositData): Promise<DepositResponse> {
    return accountService.makeDeposit(accountId, data)
  }

  /**
   * get balance for a specific account
   */
  async getBalance(accountId: string | number): Promise<number> {
    try {
      const response: BalanceResponse = await accountService.getAccountBalance(accountId)
      if (response.success && response.data) {
        return response.data.balance
      }
      return 0
    } catch (error: any) {
      console.error('Error fetching balance:', error)
      return 0
    }
  }

  /**
   * get transaction history for a specific account
   */
  async getTransactionHistory(accountId: string | number): Promise<any[]> {
    try {
      const response: TransactionsResponse = await accountService.getAccountTransactions(accountId)
      if (response.success && response.data) {
        return response.data
      }
      return []
    } catch (error: any) {
      console.error('Error fetching transactions:', error)
      return []
    }
  }
}

export default new DepositService()