<?php

namespace App\Http\Controllers;

use App\Models\Post;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\SendNewPostEmail;
use Illuminate\Support\Facades\Mail;

class blogPostController extends Controller
{
    //

    public function search($term){
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
            return $posts;

        //return Post::where('title','LIKE', '%' .$term. '%')->orWhere('body','LIKE','%'. $term . '%')->with('user:id,username,avatar')->get();
    }

    public function actuallyUpdate(Post $post, Request $request){
        $field = $request->validate([
            'title'=>'required',
            'body'=>'required',
        ]);


        $field['title'] = strip_tags($field['title']);
        $field['body'] = strip_tags($field['body']);

        $post->update($field);

        return back()->with('success','post successly updated');
        //come to the url previously

    }


    public function showEditForm(Post $post){
        //blade template will has this post
        return view('edit-post',['post'=>$post]);
    }


        public function storeNewPostApi(Request $request){
        $field =$request->validate([
            'title'=>'required',
            'body'=>'required'
        ]);

        $field['title'] = strip_tags($field['title']);
        $field['body'] = strip_tags($field['body']);
        $field['user_id'] =auth()->id();



        $newpost = Post::create($field);

        dispatch(new SendNewPostEmail(['sendTo' => auth()->user()->email, 'name' => auth()->user()->username, 'title' => $newpost->title]));

        return $newpost->id;
    }



    public function storeNewPost(Request $request){
        $field =$request->validate([
            'title'=>'required',
            'body'=>'required'
        ]);

        $field['title'] = strip_tags($field['title']);
        $field['body'] = strip_tags($field['body']);
        $field['user_id'] =auth()->id();



        $newpost = Post::create($field);

        dispatch(new SendNewPostEmail(['sendTo' => auth()->user()->email, 'name' => auth()->user()->username, 'title' => $newpost->title]));


        return redirect("/post/{$newpost->id}")->with('success','your post successfully created.');
    }

    public function showCreateForm(){
        if(!auth()->check()){
            return redirect('/');
        }

        return view('create-post');
    }

    public function viewSinglePost(Post $post){

        if($post->user_id=== auth()->user()->id){
           // return "true author";
        }
        //return "yuou are not author";
        $myhtml = Str::markdown($post->body);
        //this code will allows p, ul ...
        //    $myhtml = strip_tags(Str::markdown($post->body), '<p><ul>);
        $post['body'] = $myhtml;
        return view('single-post', ['post'=>$post]);
    }

    public function delete(Post $post){
        if(auth()->user()->cannot('delete', $post)){
            return 'you cant do that';
        }
        $post->delete();
        return redirect('/profile/'. auth()->user()->username)->with('success','successfully deleted');
    }


        public function deleteApi(Post $post){
        if(auth()->user()->cannot('delete', $post)){
            return 'you cant do that';
        }
        $post->delete();
        return 'true';
    }
}
