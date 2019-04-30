<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Post;

class Post extends Model
{
    protected $table = 'post';

    public static function checkPermission($role, $user_id="", $post_id="")
    {
        if($role == "admin" || $role == "manager")
        {
            return true;
        }
        else if($role == "user")
        {
            if(isset($user_id) && trim($user_id) != "" && isset($post_id) && trim($post_id) != "")
            {
                $check = Post::where('id', $post_id)->where('user_id',$user_id)->count();
                if($check && $check == 1)
                {   return true;   }
                else
                {   return false;   }
            }
            return false;
        }
        return false;
    }

    public static function viewAllPost($role, $user_id)
    {
        $post_data = array();
        if($role == "admin" || $role == "manager")
        {
            $post_data = Post::all();
        }
        else if(isset($user_id) && trim($user_id) != "")
        {
            $post_data = Post::where('user_id',$user_id)->get();
        }
        return $post_data;
    }
    
    public static function viewPost($role, $user_id, $id)
    {
        $post_data = array();
        if($role == "admin" || $role == "manager")
        {
            $post_data = Post::where('id', $id)->first();
        }
        else if(isset($user_id) && trim($user_id) != "" && isset($id) && trim($id) != "")
        {
            $post_data = Post::where('id', $id)->where('user_id',$user_id)->first();
        }
        return $post_data;
    }
    
    
}
