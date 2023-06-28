<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomerService
{
    protected $event = "CustomerService";
    protected $class = "CustomerService";
    public function updateCustomer(Request $request)
    {

        $id = Auth::user()->id;
        $user = User::find($id);
        if (!$user) {
            $res = getResponse(
                "01",
                [],
                "User not found",
                $request,
                $this->event,
                "updateCustomer"
            );
            return response()->json($res, 404);
        }

        // Update the user
        $user->name = $request->name;
        $user->email = $request->email;
        $user->dob = $request->dob;
        $user->msisdn = $request->msisdn;
        $user->country = $request->country;
        $user->save();

               // Check if the user settings exist
            //    $userSettings = UserSetting::where('user_id', $id)->first();

            //    if ($userSettings) {
            //        // User settings exist, update
            //        $userSettings->language = $request->language;
            //        $userSettings->notifyonsms = $request->notifyonsms;
            //        $userSettings->notifyonemail = $request->notifyonemail;
            //        $userSettings->save();
            //    } else {
            //        // User settings don't exist, create
            //        $userSettings = new UserSetting();
            //        $userSettings->user_id = $id;
            //        $userSettings->language = $request->language;
            //        $userSettings->notifyonsms = $request->notifyonsms;
            //        $userSettings->notifyonemail = $request->notifyonemail;
            //        $userSettings->save();
            //    }

        $res = getResponse(
            "00",
            [
                "user" => $user
            ],
            "User information updated successfully",
            $request,
            $this->event,
            "updateCustomer"
        );

        return response()->json($res);
    }

    public function getCustomer(Request $request)
    {
        $id = Auth::user()->id;

        $user = User::find($id);
        if (!$user) {
            $res = getResponse(
                "01",
                [],
                "User not found",
                $request,
                $this->event,
                "getCustomer"
            );
            return response()->json($res, 404);
        }

        $res = getResponse(
            "00",
            [
                "user" => $user
            ],
            "User information retrieved successfully",
            $request,
            $this->event,
            "getCustomer"
        );

        return response()->json($res);
    }
    public function makePayment(Request $request)
    {
        $input = [
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'receipt' => $request->receipt,
        ];

        $rules = [
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'receipt' => 'required|string',
        ];

        $res = validate_input($input, $rules, $this->event, $request);
        if ($res["status"] == "01" || $res["status"] == "540") {
            return json_encode($res);
        }

        $userId = Auth::user()->id;

        // Create a new transaction
        $transaction = new Transaction();
        $transaction->user_id = $userId;
        $transaction->amount = $request->amount;
        $transaction->payment_method = $request->payment_method;
        $transaction->receipt = $request->receipt;
        $transaction->save();

        $res = getResponse(
            "00",
            [
                "transaction" => $transaction
            ],
            "Payment made successfully",
            $request,
            $this->event,
            "makePayment"
        );

        return response()->json($res);
    }

    public function customer_transactions(Request $request)
    {
        $userId = Auth::user()->id;

        // Get all transactions for the customer
        $transactions = Transaction::where('user_id', $userId)->get();

        $res = getResponse(
            "00",
            [
                "transactions" => $transactions
            ],
            "Transactions retrieved successfully",
            $request,
            $this->event,
            "customer_transactions"
        );
        return response()->json($res);
    }

    public function customer_transaction(Request $request)
    {
        $receipt = $request->receipt;
        $userId = Auth::user()->id;

        // Get the transaction for the customer with the given receipt
        $transaction = Transaction::where('user_id', $userId)->where('receipt', $receipt)->first();


        if (!$transaction) {
            $res = getResponse(
                "01",
                [],
                "Transaction not found",
                $request,
                $this->event,
                "customer_transaction"
            );
            return response()->json($res, 404);
        }

        $res = getResponse(
            "00",
            [
                "transaction" => $transaction
            ],
            "Transaction retrieved successfully",
            $request,
            $this->event,
            "customer_transaction"
        );

        return response()->json($res);
    }

}
