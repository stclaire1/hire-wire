import type { Account, AccountValidation, CreateAccountData } from '@/types'
import { ACCOUNT_DEPOSIT_LIMITS, getMinDepositAmount, getMaxDepositAmount } from '@/types'

/**
 * get the display name for an account type in pt-br
 */
export const getAccountTypeName = (type: Account['account_type']): string => {
  const typeNames = {
    checking: 'Conta Corrente',
    savings: 'Poupança',
    investment: 'Investimento'
  }
  return typeNames[type]
}

/**
 * get the CSS class for account type icon
 */
export const getAccountIconClass = (type: Account['account_type']): string => {
  const iconClasses = {
    checking: 'bg-blue-100 text-blue-600',
    savings: 'bg-green-100 text-green-600',
    investment: 'bg-purple-100 text-purple-600'
  }
  return iconClasses[type]
}

/**
 * format to real
 */
export const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

/**
 * format account balance
 */
export const formatBalance = (balance: number): string => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(balance)
}

/**
 * format account number
 */
export const formatAccountNumber = (accountNumber: string): string => {
  // format: 12345-6
  if (accountNumber.length >= 6) {
    const main = accountNumber.slice(0, -1)
    const digit = accountNumber.slice(-1)
    return `${main}-${digit}`
  }
  return accountNumber
}

/**
 * get formatted deposit limits for an account type
 */
export const getFormattedDepositLimits = (type: Account['account_type']): { min: string, max: string } => {
  const limits = ACCOUNT_DEPOSIT_LIMITS[type]
  return {
    min: limits.min.toFixed(2).replace('.', ','),
    max: limits.max.toLocaleString('pt-BR', { 
      minimumFractionDigits: 2, 
      maximumFractionDigits: 2 
    })
  }
}

/**
 * validate deposit amount for a specific account type
 */
export const validateDepositAmount = (
  amount: number, 
  accountType: Account['account_type']
): { isValid: boolean, error?: string } => {
  const minAmount = getMinDepositAmount(accountType)
  const maxAmount = getMaxDepositAmount(accountType)
  
  if (isNaN(amount) || amount <= 0) {
    return { isValid: false, error: 'O valor deve ser maior que zero' }
  }
  
  if (amount < minAmount) {
    return { 
      isValid: false, 
      error: `O valor mínimo para depósito neste tipo de conta é R$ ${minAmount.toFixed(2).replace('.', ',')}` 
    }
  }
  
  if (amount > maxAmount) {
    return { 
      isValid: false, 
      error: `O valor máximo para depósito é R$ ${maxAmount.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` 
    }
  }
  
  return { isValid: true }
}

/**
 * get account type specific information
 */
export const getAccountTypeInfo = (type: Account['account_type']) => {
  const limits = getFormattedDepositLimits(type)
  return {
    name: getAccountTypeName(type),
    iconClass: getAccountIconClass(type),
    minDeposit: limits.min,
    maxDeposit: limits.max,
    minDepositValue: getMinDepositAmount(type),
    maxDepositValue: getMaxDepositAmount(type)
  }
}

/**
 * validate account creation data
 */
export const validateCreateAccountData = (data: CreateAccountData): AccountValidation => {
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
export const sortAccountsByDate = (accounts: Account[]): Account[] => {
  return [...accounts].sort((a, b) => 
    new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
  )
}

/**
 * total balance across all accounts
 */
export const calculateTotalBalance = (accounts: Account[]): number => {
  return accounts
    .reduce((total, account) => total + account.balance, 0)
}
