<?php

namespace App\Services;

use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingService
{
    protected $event = "SettingService";
    protected $class = "SettingService";

    public function updateUserSettings(Request $request)
    {
        $id = Auth::user()->id;
        $userSettings = UserSetting::where('user_id', $id)->first();

        if ($userSettings) {
            // User settings exist, update them
            $userSettings->language = $request->language;
            $userSettings->notifyonsms = $request->notifyonsms;
            $userSettings->notifyonemail = $request->notifyonemail;
            $userSettings->save();
        } else {
            // User settings don't exist, create them
            $userSettings = new UserSetting();
            $userSettings->user_id = $id;
            $userSettings->language = $request->language;
            $userSettings->notifyonsms = $request->notifyonsms;
            $userSettings->notifyonemail = $request->notifyonemail;
            $userSettings->save();
        }

        $res = getResponse(
            "00",
            [
                "userSettings" => $userSettings
            ],
            "User settings updated successfully",
            $request,
            $this->event,
            "updateUserSettings"
        );

        return response()->json($res);
    }
}


?>
