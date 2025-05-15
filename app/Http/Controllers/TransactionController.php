<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    // POST /api/transfer
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'sender_account_number' => 'required|exists:accounts,account_number',
            'receiver_account_number' => 'required|exists:accounts,account_number|different:sender_account_number',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $sender = Account::where('account_number', $validated['sender_account_number'])->first();
        $receiver = Account::where('account_number', $validated['receiver_account_number'])->first();
        $amount = $validated['amount'];

        // Optionnel : protéger pour que l'utilisateur ne puisse transférer que depuis ses comptes
        if (Auth::check() && $sender->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => "Vous ne pouvez transférer que depuis vos propres comptes."], 403);
        }

        if ($sender->balance < $amount) {
            return response()->json(['success' => false, 'message' => "Solde insuffisant."], 422);
        }

        DB::beginTransaction();
        try {
            $sender->balance -= $amount;
            $sender->save();

            $receiver->balance += $amount;
            $receiver->save();

            $transaction = Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'transaction' => $transaction], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // GET /api/accounts/{accountNumber}/transactions
    // GET /api/accounts/{accountNumber}/transactions
    public function history($accountNumber)
    {
        $account = Account::where('account_number', $accountNumber)->firstOrFail();
        $transactions = Transaction::where('sender_id', $account->id)
            ->orWhere('receiver_id', $account->id)
            ->with(['sender', 'receiver'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($t) {
                return [
                    'sender_account_number' => $t->sender->account_number,
                    'receiver_account_number' => $t->receiver->account_number,
                    'amount' => $t->amount,
                    'created_at' => $t->created_at,
                ];
            });
        return response()->json($transactions);
    }
}
