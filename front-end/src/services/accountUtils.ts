import type { Account, AccountValidation, CreateAccountData } from '@/types'

/**
 * format account balance for display
 */
export function formatBalance(balance: number): string {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(balance)
}

/**
 * format account number for display
 */
export function formatAccountNumber(accountNumber: string): string {
  // format: 12345-6
  if (accountNumber.length >= 6) {
    const main = accountNumber.slice(0, -1)
    const digit = accountNumber.slice(-1)
    return `${main}-${digit}`
  }
  return accountNumber
}

/**
 * get account type
 */
export function getAccountTypeLabel(accountType: Account['account_type']): string {
  const labels = {
    checking: 'Conta Corrente',
    savings: 'Poupança',
    investment: 'Investimento'
  }
  return labels[accountType]
}

/**
 * validate account creation data
 */
export function validateCreateAccountData(data: CreateAccountData): AccountValidation {
  const errors: Record<string, string> = {}

  if (!data.account_type) {
    errors.account_type = 'Tipo de conta é obrigatório'
  } else if (!['checking', 'savings', 'investment'].includes(data.account_type)) {
    errors.account_type = 'Tipo de conta inválido'
  }

  return {
    isValid: Object.keys(errors).length === 0,
    errors
  }
}

/**
 * sort accounts by creation date (newest first)
 */
export function sortAccountsByDate(accounts: Account[]): Account[] {
  return [...accounts].sort((a, b) => 
    new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
  )
}

/**
 * total balance across all accounts
 */
export function calculateTotalBalance(accounts: Account[]): number {
  return accounts
    .filter(account => account.is_active !== false)
    .reduce((total, account) => total + account.balance, 0)
}
