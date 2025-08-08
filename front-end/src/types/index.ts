export interface Account {
  id: number
  user_id: number
  account_number: string
  agency_number: string
  account_type: 'checking' | 'savings' | 'investment'
  balance: number
  created_at: string
  updated_at: string
}

export interface Transaction {
  id: number
  account_id: number
  transaction_type: 'deposit' | 'monthly_correction'
  amount: number
  created_at: string
  updated_at: string
  formatted_amount: string
  formatted_date: string
  transaction_description: string
}

export interface CreateAccountData {
  account_type: 'checking' | 'savings' | 'investment'
}

export interface DepositData {
  amount: number
}

// Account deposit limits
export const ACCOUNT_DEPOSIT_LIMITS = {
  checking: { min: 10.00, max: 50000.00 },
  savings: { min: 5.00, max: 50000.00 },
  investment: { min: 50.00, max: 50000.00 }
} as const

// Helper function to get minimum deposit amount for account type
export const getMinDepositAmount = (accountType: Account['account_type']): number => {
  return ACCOUNT_DEPOSIT_LIMITS[accountType].min
}

// Helper function to get maximum deposit amount for account type
export const getMaxDepositAmount = (accountType: Account['account_type']): number => {
  return ACCOUNT_DEPOSIT_LIMITS[accountType].max
}

export interface AccountResponse {
  success: boolean
  message: string
  data?: Account
  errors?: Record<string, string | string[]>
}

export interface AccountsListResponse {
  success: boolean
  message: string
  data?: Account[]
  errors?: Record<string, string | string[]>
}

export interface DepositResponse {
  success: boolean
  message: string
  data?: {
    new_balance: number
  }
  errors?: Record<string, string | string[]>
}

export interface BalanceResponse {
  success: boolean
  message: string
  data?: {
    balance: number
  }
  errors?: Record<string, string | string[]>
}

export interface TransactionsResponse {
  success: boolean
  message: string
  data?: Transaction[]
  errors?: Record<string, string | string[]>
}

// account type label in pt-br
export const ACCOUNT_TYPE_LABELS: Record<Account['account_type'], string> = {
  checking: 'Conta Corrente',
  savings: 'Poupança',
  investment: 'Investimento'
}

// Utility type for account validation
export interface AccountValidation {
  isValid: boolean
  errors: Record<string, string>
}