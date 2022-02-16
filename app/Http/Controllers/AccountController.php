<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
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
        ]);

        Account::create([
            'user_id' => Auth::id(),
            'card_number' => $fields['card_number'],
            'password' => $fields['password'],
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
            'deposit' => 'required|numeric'
        ]);

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
        // generate random digits for payment number
        $paymentNumber = ''.join(Arr::random([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], 6));

        $url = "https://apibeta.finnotech.ir//oak/v2/clients/xxxxxyyyyyyzzzzzzzz/transferTo?trackId=uuid111122223333444455556666777788889999";

        $response = Http::accept('application/json')->post($url, [
            'amount' => $amount,
            'description' => "Making a tranfer transaction",
            'destinationFirstname' => $source_account->user->firstname,
            'destinationLastname' => $source_account->user->lastname,
            'destinationNumber' => $destination_account->card_number,
            'paymentNumber' => $paymentNumber,
            'deposit' => $fields['deposit'],
            'sourceFirstName' => $destination_account->user->firstname,
            'sourceLastName' => $destination_account->user->lastname,
            'reasonDescription' => "1",
        ]);

        $json = $response->json();
        $status = $json['status'];
        if ($status == 'FAILED') {
            return response([
                'error' => 'Transaction failed'
            ], 400);
        } else if ($status == 'DONE') {
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