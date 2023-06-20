<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomerService
{
    protected $event="CustomerService";
    protected $class="CustomerService";

    // public function createCustomer(Request $request)
    // {
    //     // Validate input
    //     $input = $request->all();
    //     $rules = [
    //         'name' => 'required',
    //         'email' => 'required|email|unique:users,email',
    //         'dob' => 'required|date',
    //         'msisdn' => 'required',
    //         'country' => 'required',
    //         'password' => ['required', Password::min(10)->letters()->mixedCase()->numbers()->symbols()],
    //     ];

    //     $res = validate_input($input, $rules, $this->event, $request);
    //     if ($res["status"] == "01" || $res["status"] == "540")
    //         return json_encode($res);

    //     // Check if email or msisdn already exists
    //     $userExists = User::where('email', $request->email)->orWhere('msisdn', $request->msisdn)->exists();
    //     if (!$userExists) {
    //         $user = new User;
    //         $user->name = $request->name;
    //         $user->email = $request->email;
    //         $user->dob = $request->dob;
    //         $user->msisdn = $request->msisdn;
    //         $user->country = $request->country;
    //         $user->password = Hash::make(base64_decode($request->password));
    //         $user->save();

    //         Auth::login($user);
    //         $token = $user->createToken("wakapay-customer", ['customer'])->accessToken;

    //         $res = getResponse(
    //             "00",
    //             [
    //                 "user" => $user,
    //                 "token" => $token,
    //                 "user_exist" => 0
    //             ],
    //             "You have successfully registered a user",
    //             $request,
    //             $this->event,
    //             "registration"
    //         );
    //         return response()->json($res);
    //     } else {
    //         $res = getResponse(
    //             "01",
    //             [
    //                 "user_exist" => 1
    //             ],
    //             "Registration failed user exists",
    //             $request,
    //             $this->event,
    //             "registration"
    //         );

    //         return response()->json($res);
    //     }
    // }

    public function updateCustomer(Request $request, $id)
    {
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

    public function getCustomer(Request $request, $id)
    {
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

}
