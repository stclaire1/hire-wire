<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\DepositRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    // calls the AccountService to handle authentication logic
    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function index(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $accounts = $this->accountService->getUserAccounts($userId);

            return response()->json([
                'success' => true,
                'data' => $accounts->values()->toArray(),
                'message' => 'Contas recuperadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar contas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(CreateAccountRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $account = $this->accountService->createAccount($userId, $request->validated());

            return response()->json([
                'success' => true,
                'data' => $account,
                'message' => 'Conta criada com sucesso'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $account = $this->accountService->getAccountById($id, $userId);

            return response()->json([
                'success' => true,
                'data' => $account,
                'message' => 'Conta encontrada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function deposit(DepositRequest $request, string $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $newBalance = $this->accountService->makeDeposit($id, $userId, $request->amount);

            return response()->json([
                'success' => true,
                'data' => [
                    'new_balance' => $newBalance
                ],
                'message' => 'Depósito realizado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function balance(string $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $balance = $this->accountService->getAccountBalance($id, $userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $balance
                ],
                'message' => 'Saldo recuperado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function transactions(string $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $transactions = $this->accountService->getAccountTransactions($id, $userId);

            return response()->json([
                'success' => true,
                'data' => $transactions->toArray(),
                'message' => 'Histórico de transações recuperado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
