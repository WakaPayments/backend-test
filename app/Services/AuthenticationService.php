<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Encryption\DecryptException;


    class AuthenticationService
    {
        protected $event="AuthenticationService";
        protected $class="AuthenticationService";

        public function registration(Request $request)
        {
            $input=[
                'name'=>$request->name,
                'email'=>$request->email,
                'dob'=>$request->dob,
                'msisdn'=>$request->msisdn,
                'country'=>$request->country,
                'password'=>base64_decode($request->password),

            ];

            $rules=[
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'dob' => 'required|date',
                'msisdn' => 'required',
                'country' => 'required',
                'password' => ['required', Password::min(10)->letters()->mixedCase()->numbers()->symbols()],
            ];

            $res=validate_input($input,$rules,$this->event,$request);
            if ($res["status"] == "01" || $res["status"] == "540")
            return json_encode($res);

                // Check if email or msisdn already exists
            $userExists = User::where('email', $request->email)->orWhere('msisdn', $request->msisdn)->exists();
            if (!$userExists){
                $user = new User;
                $user->name=$request->name;
                $user->email=$request->email;
                $user->dob=$request->dob;
                $user->msisdn =$request->msisdn;
                $user->country=$request->country;
                $user->password = Hash::make(base64_decode($request->password));
                $user->save();

                Auth::login($user);
                $token = $user->createToken("wakapay-customer", ['customer'])->accessToken;

                $res = getResponse(
                    "00",
                    [
                        "user" => $user,
                        "token" => $token,
                        "user_exist" => 0
                    ],
                    "You have successfully registered a user",
                    $request,
                    $this->event,
                    "registration"
                );
                return response()->json($res);

            }
            else{
                $res = getResponse(
                    "01",
                    [
                        "user_exist" => 1
                    ],
                    "Registration failed user exists",
                    $request,
                    $this->event,
                    "registration"
                );

                return response()->json($res);
            }
        }
        public function login($request)
        {
            $input = [
                'msisdn' => $request->msisdn,
                'password' => base64_decode($request->password),
            ];
            $rules = [
                'msisdn' => 'required',
                'password' => 'required',
            ];

            $res = validate_input($input, $rules, $this->event, $request);
            if ($res["status"] == "01" || $res["status"] == "540")
            return json_encode($res);

            $userExists = User::where('msisdn', $request->msisdn)->exists();
            if (!$userExists) {
            $res = getResponse(
                "01",
                [],
                "Phone number entered does not match available records",
                $request,
                $this->event,
                "login"
            );
            return json_encode($res);
        } else {
            try {
                $login_input = ["msisdn" => format_mobile_number($request->msisdn), "password" => base64_decode($request->password)];

               return json_encode($login_input);
               
                if (Auth::attempt($login_input)) {
                    // $token = Auth::user()->createToken('wakapay-customer', ['customer'])->accessToken;
                    // $user = User::find(Auth::user()->id);
                    $user = User::find(Auth::user()->id);
                    $token = $user->createToken('wakapay-customer', ['customer'])->accessToken;
                    $user->last_logon = now();
                    $user->success_logon_IP = $request->ip();
                    $user->save();

                    $res = getResponse(
                        "00",
                        [
                            'token' => $token
                        ],
                        'customer login succesfully, Use token to authenticate.',
                        $request,
                        $this->event,
                        "login"
                    );
                    return response()->json($res, 200);
                } else {

                    $res = getResponse(
                        "01",
                        [],
                        'customer authentication failed due to mismatch in user id or password',
                        $request,
                        $this->event,
                        "login"
                    );
                    return response()->json($res, 401);
                }
            } catch (DecryptException $e) {

                $res = getResponse(
                    "540",
                    [],
                    'Encryption error ' . $e->getMessage(),
                    $request,
                    $this->event,
                    "login"
                );

                return response()->json($res, 500);
            }
        }
    }


    }
