<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use App\Events\OurExampleEvent;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    //

    public function loginApi(Request $request){
        $incomingfield=$request->validate([
            'username'=> 'required',
            'password'=> 'required'
        ]);

        if(auth()->attempt($incomingfield)){
            $user = User::where('username', $incomingfield['username'])->first();
            $token = $user->createToken('ourapptoken')->plainTextToken;
            return $token;
        }

        return '';
    }

    public function storeAvatar(Request $request){
        $request->validate([
            'avatar'=>"required|image|max:3000"
        ]);

        $user = auth()->user();

        $filename = $user->id . '-'. uniqid().'.jpg';
        //$request->file('avatar')->store('public/avatars');
        $imgData = Image::make($request->file('avatar'))->fit(120)->encode('jpg');
        //return a loaded/road data saved to the computer

        Storage::put("public/avatars/".$filename,$imgData);

        $oldAvatar = $user->avatar;
            $user->avatar =$filename;
            $user->save();
        if($oldAvatar !="/fallback-avatar.jpg"){
            Storage::delete(str_replace("/storage/", "public/", $oldAvatar));
        }

            return back()->with('success',"congrats! on new avatar");

    }

    public function showAvatarForm(){
        return view('avatar-form');
    }







    private function getSharedData($user){
        $currentFollow = 0;
        if(auth()->check())
        {
            $currentFollow =  Follow::where([
                ['user_id', "=", auth()->user()->id],
                ['followeduser',"=",$user->id]])->count();
        }

        View::share('sharedData', [
            'currentFollow'=>$currentFollow,
            'username'=> $user->username,
            'posts'=>$user->posts()->latest()->get(),
            'postCount'=> $user->posts()->count(),
            'followerCount'=> $user->followers()->count(),
            'followingCount'=> $user->followingTheseUsers()->count(),
            'avatar'=>$user->avatar
        ]);
        //share variable

    }

    public function profile(User $user){
        $this->getSharedData($user);
        return view('profile-post', [
            'posts'=>$user->posts()->latest()->get(),
        ]);
    }


    public function profileRaw(User $user){
        return response()->json(['theHTML' => view('profile-post-only', ['posts'=>$user->posts()->latest()->get()] )->render(), 'doctitle'=>$user->username."'s Profile"]);
    }


    public function profileFollower(User $user){
        $this->getSharedData($user);
        return view('profile-follower', [
            'followers'=>$user->followers()->latest()->get(),
        ]);
    }

    public function profileFollowerRaw(User $user){
        return response()->json(['theHTML' => view('profile-follower-only', ['followers'=>$user->followers()->latest()->get()] )->render(), 'doctitle'=>$user->username."'s follower"]);

    }

    public function profileFollowingRaw(User $user){
        return response()->json(['theHTML' => view('profile-following-only', ['following'=>$user->followingTheseUsers()->latest()->get()] )->render(), 'doctitle'=>$user->username."'s following"]);

    }

    public function profileFollowing(User $user){
        $this->getSharedData($user);
        return view('profile-following', [
            'following'=>$user->followingTheseUsers()->latest()->get(),
        ]);
    }




    public function logout(){
        event(new OurExampleEvent(['username' => auth()->user()->username, 'action'=>'logout']));
            auth()->logout();

        return redirect('/')->with('success', 'you are now logged out.');

    }

    public function showCorrectHomePage(){
        if( auth()->check()) {
            return view('homepage-feed', ['posts' => auth()->user()->feedposts()->latest()->paginate(5)]);
        }
        else {
            $postCount = Cache::remember('postCount',20, function (){
                sleep(5);
                return Post::count();
            });
            return view('homepage', ['postCount' => $postCount]);
        }
       //return true / false

    }

    public function register(Request $request){

        $field = $request->validate([
            'username'=>['required', "min:3", "max:30",Rule::unique('users','username')],
            'email'=>['required','email', Rule::unique('users','email')],
            'password'=>['required','min:8','confirmed'],
        ]);

        $field['password'] = bcrypt($field['password']);
        //this user belongs to model
        $user = User::create($field);
        // create in database
        auth()->login($user);
        //automatic login the new user

        return redirect('/')->with('success','thank you for creating account.');
    }

    public function login(Request $request) {
        $field = $request -> validate([
            'loginusername' => 'required',
            'loginpassword' => 'required'

        ]);

        if(auth()->attempt([
            'username'=> $field['loginusername'],
            'password'=> $field['loginpassword']
        ])){
            $request->session()->regenerate();
            event(new OurExampleEvent(['username' => auth()->user()->username, 'action'=>'login']));
            return redirect('/')->with('success','You have successfully logged in.');

        }else {
            return redirect('/')->with('failure','invalid login');
        }
    }
}
