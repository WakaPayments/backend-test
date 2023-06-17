<?php

use App\Mail\SendMailNotification;
use App\Models\AuditTrail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Carbon\Carbon;


use App\Models\Notification;

use App\Models\PasswordToken;
use App\Models\Transactions;
use Epmnzava\Nextsms\Nextsms;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Auth;

function hide()
{
    return 0;
}

function show()
{
    return 1;
}


function param_missing()
{
    return "Parameter Missing";
}
function method_not_found()
{
    return "Method Not Found";
}



function customer_token($request, $class)
{
    if (!$request->user()->tokenCan('customer')) {
        $res = getResponse("403", ['status' => '403', 'message' => 'You have no access to this resource', 'token' => "app"], "You have no access to this resource", $request, $class, "App Token Error");
        abort(response()->json($res, 403));
    }
}



function app_token($request, $class)
{
    if (!$request->user()->tokenCan('appclient')) {
        $res = getResponse(
            "403",
            [
                'status' => '403',
                'message' => 'You have no access to this resource',
                'token' => "app"
            ],
            "You have no access to this resource",
            $request,
            $class,
            "App Token Error"
        );

        abort(response()->json(
            $res,
            403
        ));
    }
}


function officer_token($request, $class)
{
    if (!$request->user()->tokenCan('delivery_officer')) {
        $res = getResponse(
            "403",
            [
                'status' => '403',
                'message' => 'You have no access to this resource',
                'token' => "app"
            ],
            "You have no access to this resource",
            $request,
            $class,
            "App Token Error"
        );

        abort(response()->json(
            $res,
            403
        ));
    }
}




function agent_token($request, $class)
{
    if (!$request->user()->tokenCan('agent')) {
        $res = getResponse(
            "403",
            [
                'status' => '403',
                'message' => 'You have no access to this resource',
                'token' => "app"
            ],
            "You have no access to this resource",
            $request,
            $class,
            "App Token Error"
        );

        abort(response()->json(
            $res,
            403
        ));
    }
}


/**
 * This function will decrypt a value
 */
if (!function_exists('send_sms')) {
    function send_sms($to, $message, $country_code)
    {
        if ($country_code == "255") {
            $sms = new  Nextsms("https://messaging-service.co.tz",  env('nextsms_username'),  env('nextsms_password'));
            $response = $sms->sendSms($message, env('next_sms_senderid'), $to);
            return $response;
        }
    }
}


function getResponse($status, $data, $message, $request, $event, $category)
{

    $res = [
        'status_code' => $status,
        'data' => $data,
        'message' => $message
    ];
    Log::info("event ::: " . $event . " device :: " . $request->userAgent() . " message ::: " . $message);

    $agent = $request->userAgent();
    if (empty($request->userAgent()))
        $agent = "vpn";

    log_audit($event, $category, $request->all(),     $res, $request->ip(), $request->fullUrl(), $agent, $message);

    return $res;
}

function audit($userid, $event, $category, $request, $message, $response, $admin = "null")
{

    $agent = $request->userAgent();
    if (empty($request->userAgent()))
        $agent = "vpn";

    log_audit($event, $category, $request->except(["password", "password_confirmation"]), $response, $request->ip(), "", $agent, $message, $userid);
}



function validate_input($input, $rules, $event = "VALIDATION", Request $request = null)
{
    try {
        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {

            $res = [
                'status' => "01",
                'status_code' => "01",

                'data' => [
                    'errors' => $validator->errors()

                ],
                'message' => 'Please see errors parameter for all errors.',
            ];

            if (!empty($request)) {

                $agent = $request->userAgent();
                if ($request->userAgent() == null)
                    $agent = "vpn";

                log_audit($event, "validation", $request->all(), $res, $request->ip(), $request->fullUrl(), $agent, 'Please see errors parameter for all errors.');
            }
            return $res;
        }
    } catch (DecryptException $e) {
        $res = [
            'status' => "540",
            'status_code' => "540",
            'data' => [],
            'message' => 'Encryption error  ' . $e->getMessage(),
        ];

        $agent = $request->userAgent();
        if ($request->userAgent() == null)
            $agent = "vpn";

        log_audit($event, "validation", $request->all(), $res, $request->ip(), $request->fullUrl(), $agent, 'Encryption error  ' . $e->getMessage());

        return $res;
    }

    return   [
        'status' => "00",

        'status_code' => "00",
        'data' => [],
        'message' => 'Validation is successful',
    ];
}




// Function to generate OTP
function generateOtp($n)
{

    // Take a generator string which consist of
    // all numeric digits
    $generator = "1357902468";

    // Iterate for n-times and pick a single character
    // from generator and append it to $result

    // Login for generating a random character from generator
    //     ---generate a random number
    //     ---take modulus of same with length of generator (say i)
    //     ---append the character at place (i) from generator to result

    $result = "";

    for ($i = 1; $i <= $n; $i++)
        $result .= substr($generator, (rand() % (strlen($generator))), 1);


    return $result;
}


function add_notification_to_customer($message, $send_to, $type, $subject = "PoaGas")
{

    $notify = new Notification();

    $notify->type = $type;
    $notify->data = $message;
    $notify->sent_to = $send_to;
    $notify->save();

    $user = Customer::find($send_to);


    send_sms(format_mobile_number($user->phone), $message);

    send_email($user, $message, $subject);
}


function add_notification_to_agent($message, $send_to, $type, $subject = "PoaGas")
{

    $notify = new Notification();

    $notify->type = $type;
    $notify->data = $message;
    $notify->sent_to = $send_to;
    $notify->save();

    $user = AgentUser::find($send_to);


    send_sms(format_mobile_number($user->phone), $message);

    send_email($user, $message, $subject);
}






function add_notification($message, $send_to, $type, $subject = "PoaGas", $usertype = "user")
{

    $notify = new Notification();

    $notify->type = $type;
    $notify->data = $message;
    $notify->sent_to = $send_to;
    $notify->save();

    if ($usertype == "customer") {
        $user = Customer::find($send_to);
    } else if ($usertype == "agent") {
        $user = AgentUser::find($send_to);
    } else
        $user = User::find($send_to);



    if (!empty($user->phone))
        send_sms(format_mobile_number($user->phone), $message);


    if (!empty($user->email))
        send_email($user, $message, $subject);
}




if (!function_exists('format_mobile_number')) {


    function format_mobile_number($number,$country_code=255)
    {

        if($country_code=="255"){
        $usernumber = "";
        if (substr($number, 0, 1) == "0") {
            $usernumber = substr($number, 1);

            $usernumber = "255" . $usernumber;
        } else
            $usernumber = $number;


        return $usernumber;
    }
    else{
        return $number;

    }


    }
}



/**
 * This function will send an email
 */
if (!function_exists('send_email')) {
    function send_email($user, $message, $subject)
    {

        Mail::to($user)->send(new SendMailNotification($user, $message, $subject));
    }
}



function curl_get_file_contents($URL)
{
    $c = curl_init();
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_URL, $URL);
    $contents = curl_exec($c);
    curl_close($c);

    if ($contents) return $contents;
    else return FALSE;
}





function getRequest(Request $request)
{
    $req = ["ip" => $request->ip(), "data" => $request->all(), "fullUrl" => $request->fullUrl(), "uri" => $request->path(), 'useragent' => $request->userAgent()];

    return $req;
}


function res($status, $data, $message)
{
    return  [
        "status_code" => $status,
        "data" => $data,
        "message" => $message
    ];
}


// function is_invited($email)
// {


//     if (!PasswordToken::where('email', $email)->exists())
//         return true;

//     if (PasswordToken::where('email', $email)->count() > 0) {
//         if (PasswordToken::where('email', $email)->first()->invited)
//             return true;
//         else
//             return false;
//     } else
//         return true;
// }





/**
 * This function will encrypt a value
 */
if (!function_exists('encrypt')) {
    function encrypt($value)
    {

        return Crypt::encrypt($value);
    }
}






function getCurrentUserId()
{

    return Auth::user()->id;
}


function getCurrentUser()
{

    return Auth::user();
}


/**
 * This function will decrypt a value
 */
if (!function_exists('decrypt')) {
    function decrypt($value)
    {
        return  Crypt::decrypt($value);
    }
}
