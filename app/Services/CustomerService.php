<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
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
}
