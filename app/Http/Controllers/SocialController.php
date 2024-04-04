<?php

namespace App\Http\Controllers;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SocialController extends Controller
{
    public function doLogin(Request $request)
    {
        $referer = (isset($_SERVER["HTTP_REFERER"])) ? $_SERVER["HTTP_REFERER"] : ($request->has('rpath') ? $request->get('rpath') :'');
        session()->put('referer',$referer);
       
        return Socialite::driver('keycloak')->redirect();
    }

    public function authCallback(Request $request)
    {
        $userKeycloack = Socialite::driver('keycloak')->user();
        $user = User::where('keycloack_id', $userKeycloack->id)->first();

        if (! $user) {
            //store user data
            $user = User::create([
                'keycloack_id' => $userKeycloack->id,
                'username' => $userKeycloack->nickname,
            ]);
        }

        $auth = Auth::loginUsingId($user->id);
        $token = $user->createToken(env('APP_NAME'))->accessToken;
        
        $refresh_token = $userKeycloack->accessTokenResponseBody['refresh_token'];
        $access_token = $userKeycloack->accessTokenResponseBody['access_token'];
        $expires_in_sec = $userKeycloack->accessTokenResponseBody['expires_in'];
        $expires_at = date('Y-m-d H:i:s',strtotime("+$expires_in_sec seconds"));
        $state_id =  $userKeycloack->accessTokenResponseBody['session_state'];
        session()->put('access_token',$access_token);

//         $keycloakUrl = "http://your-keycloak-server/auth";
// $realmName = "your-realm";
// $clientId = "your-client-id";
// $clientSecret = "your-client-secret";

//         $sessionId = "session-id-to-update";
//         $sessionInfoUrl = "$keycloakUrl/admin/realms/$realmName/sessions/$sessionId";
//         $response = Http::withHeaders([
//             "Authorization" => "Bearer $adminToken",
//             "Content-Type" => "application/json"
//         ])->get($sessionInfoUrl);
//         $sessionInfo = $response->json();

//         // Update session (for example, extending session timeout)
//         $updatedMax = $sessionInfo['max'] + 3600; // Extend session by 1 hour
//         $updatePayload = [
//             "max" => $updatedMax
//         ];
//         $updateSessionUrl = "$keycloakUrl/admin/realms/$realmName/sessions/$sessionId";
//         $response = Http::withHeaders([
//             "Authorization" => "Bearer $adminToken",
//             "Content-Type" => "application/json"
//         ])->put($updateSessionUrl, $updatePayload);


        DB::table('personal_access_tokens')
        ->where('id',$token->id)
        ->update(['refresh_token'=>$refresh_token,'access_token'=>$access_token,'expires_at'=>$expires_at,'state_id'=>$state_id]);
       
        $redirect = session('referer').'?token='.$token->token;
        session()->forget('referer');
        session()->flush('referer');
        return redirect($redirect);
    }

    public function logout(Request $request)
    {
        $logoutreferer = (isset($_SERVER["HTTP_REFERER"])) ? $_SERVER["HTTP_REFERER"] : ($request->has('rpath') ? $request->get('rpath') :'');
        //session()->put('referer',$referer);
        Auth::logout();
       // return redirect(Socialite::driver('keycloak')->getLogoutUrl($redirectUri, env('KEYCLOAK_CLIENT_ID'), 'YOUR_ID_TOKEN_HINT'));
        //return redirect(Socialite::driver('keycloak')->getLogoutUrl($referer,env('KEYCLOAK_CLIENT_ID'),session('access_token')));
        return redirect(Socialite::driver('keycloak')->getLogoutUrl($logoutreferer, env('KEYCLOAK_CLIENT_ID')));
    }

    public function getUserByToken(Request $request)
    {

    }
}
