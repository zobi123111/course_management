<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function index()
    {
        return view('Login/index');
    }

    public function login(Request $request)
    {

        if($request->isMethod('get')) {
            $userId = request()->user()->id ?? null;
            if ($userId) {               
                return redirect()->route('dashboard');
            } else {
                return view('Login.index');
            }
        }
  
        if ($request->isMethod('post')) {

            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);
         
            if (Auth::attempt($credentials)) {
                $user = User::where('email', $credentials['email'])->first();
                    if (Auth::attempt($credentials)) {
                        $request->session()->regenerate();
                      //  return redirect()->intended('dashboard');
                        return response()->json(['success' => 'Login Successfully']);
                    }else {
                        Auth::logout();
                        return back()->withErrors([
                            'credentials_error' => 'Your status has been inactive recently. Please contact your administrator.',
                        ])->onlyInput('email');
                    }
                }
                else{
                    return response()->json(['credentials_error' => 'The provided credentials do not match our records.']);
                
                }
        
  
    
    }
       
    }

    public function logOut()
    {
         Session::flush();
         Auth::logout();
         return Redirect('/');
    }

    public function forgotPasswordView()
    {

        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        //----------------------------------------------
        //dummy data
        $email = $request->email;
         $recipient = User::where('email', $email)->first();

        if($recipient){
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                ['token' => $token, 'created_at' => Carbon::now()]
            );
            //  $resetUrl = '/reset/password/' . $token . '?email=' . urlencode($email);
            //  dd($resetUrl);
            $resetUrl = url('/reset/password/' . $token . '?email=' . urlencode($email));
        
            //  print_r($resetUrl);
            //  $messages = [
            //     'subject' => 'Reset Your Password ' ,
            //     'greeting-text' => 'Dear ' .ucfirst($recipient->first_name). ',',
            //     'url-title' => 'Reset Password',
            //     'url' => $resetUrl,
            //     'lines_array' => [
            //         'body-text' => 'We received a request to reset your account password. To reset your password, please click on the link below:',
            //         'info' => "If you didn't request this password reset or believe it's a mistake, you can ignore this email. Your password will not be changed until you access the link above and create a new password.",
            //         'expiration' => "This password reset link is valid for the next 24 hours. After that, you'll need to request another password reset.",
            //     ],
            //     'thanks-message' => 'Thank you for using our application!',
            // ];

            //  $recipient->notify(new CommonEmailNotification($messages));


        }
        else{
            return redirect()->back()->with('error', 'Your Email Is Not Registered.');
        }
        return redirect()->back()->with('message', 'We have mailed your password reset link!');
    }

    public function resetPassword($token)
    {

        $email = request()->input('email');

        return view('auth.reset-password', ['token' => $token,'email' => $email]);
    }

    public function submitResetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $updatePassword = DB::table('password_reset_tokens')
        ->where([
          'email' => $request->email,
          'token' => $request->token
        ])
        ->first();

        if(!$updatePassword){
            return back()->withInput()->with('error', 'Invalid token!');
        }
        $user = User::where('email', $request->email)
        ->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where(['email'=> $request->email])->delete();

        return redirect('/')->with('message', 'Your password has been changed!');

    }
   
}
