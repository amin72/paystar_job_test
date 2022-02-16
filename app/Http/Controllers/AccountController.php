<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Transaction;
use Auth;
use Arr;


class AccountController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'card_number' => 'required|string|unique:accounts,card_number',
            'password' => 'required|string|confirmed',
            'total' => 'numeric',
        ]);

        Account::create([
            'user_id' => Auth::id(),
            'card_number' => $fields['card_number'],
            'password' => $fields['password'],
            'total' => Arr::has($fields, 'total') ? $fields['total'] : 0,
            ]);

        return response([
            'message' => 'Account created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $account = Account::where('user_id', Auth::id())->firstOrFail();
        return response([
            'card_number' => $account->card_number,
            'total' => $account->total,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $fields = $request->validate([
            'card_number' => 'string|unique:accounts,card_number',
            'password' => 'string|confirmed',
        ]);

        $account = Account::where('user_id', Auth::id())->firstOrFail();

        // update card number if set
        if (Arr::has($fields, 'card_number')) {
            $account->card_number = $fields['card_number'];
        }

        // update password if set
        if (Arr::has($fields, 'password')) {
            $account->password = $fields['password'];
        }

        // save account info
        $account->save();
        
        return response([
            'message' => 'account updated successfully',
        ], 200);
    }


    /**
     * Transfer money.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function transfer(Request $request) {
        // fields required to transfer money to another account
        $fields = $request->validate([
            'secondPassword' => 'required|string',
            'destinationNumber' => 'required|string|min:26|max:26',
            'amount' => 'required|numeric',
            'deposite' => 'required|numeric'
        ]);

        // "amount": 1,
        // "description": "شرح تراکنش",
        // "destinationFirstname": "خلیلی  حسینی  بیابانی",
        // "destinationLastname": "سمیه   غز اله  فریماه",
        // "destinationNumber": "IR120620000000302876732005",
        // "paymentNumber": "123456",
        // "deposit": "776700000",
        // "sourceFirstName": "مارتین",
        // "sourceLastName": "اسکورسیزی",
        // "reasonDescription": "1"
   
        $source_account = Account::where('user_id', Auth::id())->firstOrFail();
        
        $destination_account = Account::where(
            'card_number', $fields['destinationNumber'])->first();
        
        if (! $destination_account) {
            return response([
                'error' => 'Destination card number not found.'
            ], 400);
        }

        $amount = $fields['amount'];
        $sourceNumber = $source_account->card_number;
        $destinationNumber = $fields['destinationNumber'];

        if ($source_account->can_transfer_money($amount)) {
            // subtract amount from total of source account
            $source_account->total -= $amount;
            $source_account->save();

            // add amount to total of destination amount
            $destination_account->total += $amount;
            $destination_account->save();

        
            // generate random digits for payment number
            $paymentNumber = ''.join(Arr::random([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], 6));

            // create a transaction object to save current transtion for user who making the transaction
            Transaction::create([
                'user_id' => $source_account->user_id,
                'amount' => $amount,
                'description' => 'Made a tranfer transaction',
                'source_number' => $sourceNumber,
                'destination_number' => $destinationNumber,
                'payment_number' => $paymentNumber,
            ]);

            // create a transaction object to save current transtion for user who receiving the money
            Transaction::create([
                'user_id' => $destination_account->user_id,
                'amount' => $amount,
                'description' => 'Received money',
                'source_number' => $sourceNumber,
                'destination_number' => $destinationNumber,
                'payment_number' => $paymentNumber,
            ]);

            return response([
                'message' => 'Amount transfered to ' . $destination_account->card_number . ' successfully.',
            ], 200);
        } else {
            return response([
                'message' => 'You do not have enought money to make this transaction',
            ], 200);
        }
    }


    /**
     * Fetch all transactions.
     *
     * @return \Illuminate\Http\Response
     */
    public function transactions() {
        $transactions = Transaction::where('user_id', Auth::id())->get();
        return $transactions;
    }
}
