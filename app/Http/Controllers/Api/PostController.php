<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Post;

class PostController extends Controller {

    public function create(Request $request) {
        $valid = validator($request->only('title', 'content'), [
            'title' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        if ($valid->fails()) {
            $jsonError = response()->json($valid->errors()->all(), 400);
            return \Response::json($jsonError);
        }

        $user = $request->user();

        $title = $request['title'];
        $content = $request['content'];

        $post = new Post();
        $post->user_id = $user->id;
        $post->title = $title;
        $post->content = $content;
        $post->save();
        return \Response::json($post);
    }

    public function edit(Request $request) {
        $valid = validator($request->only('title', 'content'), [
            'id' => 'require',
            'title' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        if ($valid->fails()) {
            $jsonError = response()->json($valid->errors()->all(), 400);
            return \Response::json($jsonError);
        }

        $id = $request['id'];
        $user = $request->user();
        $role = $user->role;

        $check = Post::checkPermission($role, $user->id, $id);
        if ($check == false) {
            $msg = array("message" => "You are not Authorized to Access this Post");
            $jsonError = response()->json($msg, 400);
            return \Response::json($jsonError);
        }

        $title = $request['title'];
        $content = $request['content'];

        $post = Post::find($id);        
        $post->title = $title;
        $post->content = $content;
        $post->save();
        return \Response::json($post);
    }

    public function viewAllPost(Request $request) {
        $id = $request['id'];
        $user = $request->user();
        $user_id = $user->id;
        $role = $user->role;

        $postData = Post::viewAllPost($role, $user_id);

        return \Response::json($postData);
    }

    public function viewPost(Request $request)
    {
        $id = $request['id'];
        $user = $request->user();
        $user_id = $user->id;
        $role = $user->role;

        $postData = Post::viewPost($role, $user_id, $id);

        return \Response::json($postData);
    }

    public function delete(Request $request)
    {
        $id = $request['id'];
        $user = $request->user();
        $user_id = $user->id;
        $role = $user->role;

        $check = Post::checkPermission($role, $user_id, $id);
        if($check == false) {
            $msg = array("message" => "You are not Authorized to Access this Post");
            $jsonError = response()->json($msg, 400);
            return \Response::json($jsonError);
        }

        $post_check = Post::where('id', $id)->count();
        if($post_check == 1)
        {
            $post = Post::find($id);
            $post->delete();
            $show_response = array("message" => "Post Successfully Deleted.");
            return \Response::json($show_response);
        }
        else
        {
            $show_response = array("message" => "Post Not Found.");
            return \Response::json($show_response);
        }
    }
}
