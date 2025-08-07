export interface Account {
  id: number
  user_id: number
  account_number: string
  account_type: 'checking' | 'savings' | 'investment'
  balance: number
  is_active?: boolean // optional since API might (probably won't) not return this field
  created_at: string
  updated_at: string
}

export interface CreateAccountData {
  account_type: 'checking' | 'savings' | 'investment'
}

export interface AccountResponse {
  success: boolean
  message: string
  data?: {
    account: Account
  }
  errors?: Record<string, string | string[]>
}

export interface AccountsListResponse {
  success: boolean
  message: string
  data?: Account[] | {
    accounts: Account[]
  }
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