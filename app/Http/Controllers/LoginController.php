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
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        return view('Login/index');
    }

    public function login(Request $request)
    {

        if ($request->isMethod('get')) {
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
                    $user = Auth::user();
                    
                    if($user->is_activated == 1){ 
                        Auth::logout();
                        return response()->json([
                            'credentials_error' => 'Your account is currently disabled . Please contact your administrator.'
                        ]);
                    }
                    elseif($user->status == 0){
                        Auth::logout();
                        return response()->json([
                            'credentials_error' => 'Your account is currently disabled. Please contact your administrator.'
                        ]);

                     }

                    // ✅ Regenerate session and return success
                    $request->session()->regenerate();
                    return response()->json(['success' => 'Login Successfully']);
                }else {
                  return response()->json(['credentials_error' => 'The provided credentials do not match our records.']);
              }
        }
    }

    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->password_flag = 0;
        $user->save();

        Auth::logout();

        return redirect()->route('login')->with('message', 'Your password has been successfully changed.');
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
       
        if ($recipient) {
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                ['token' => $token, 'created_at' => Carbon::now()]
            );
           
           
            $resetLink = url('/reset/password/' . $token . '?email=' . urlencode($email));
         
            Mail::send('emailtemplates.password_reset', ['resetLink' => $resetLink, 'user' => $recipient], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Reset Your Password');
            });

            return redirect()->back()->with('message', 'Password reset link has been sent to your email.');
        } else {
            return redirect()->back()->with('error', 'Email address not found.'); 
            // return response()->json([
            //     'message' => 'Email address not found.'
            // ]);
        }
        return redirect()->back()->with('message', 'We have mailed your password reset link!');
    }

    public function resetPassword($token)
    {

        $email = request()->input('email');

        return view('auth.reset-password', ['token' => $token, 'email' => $email]);
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

        if (!$updatePassword) {
            return back()->withInput()->with('error', 'Invalid token!');
        }
        $user = User::where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();

        return redirect('/')->with('message', 'Your password has been changed!');
    }
    public function send_notification()
    {
       $otp = "123456";
       $user = "Lovepreet";
   
       // Send OTP to user's email (make sure the mail system is configured)
       $user = Auth::user();
   
           Mail::send('email.send_notifications', ['otp' => $otp, 'user' => $user], function ($message) use ($user) {
               $message->to($user->email)->subject('Send Notifications');
           });
   
       
    }
}
