<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The following that belong to the user 
     */
     public function following(){
         return $this->belongsToMany("App\User", 'followers', 'follower_user_id', 'user_id')->withTimestamps();
     }

     public function isFollowing(User $user){
         return !is_null($this->following()->where('user_id', $user->id)->first());
     }

     /**
      * The followers that belong to the user
      */ 
     public function followers(){
         return $this->belongsToMany("App\User", "followers", "user_id", "follower_user_id")->withTimestamps();
     }

     /**
      * Get the tweets for the user
      */
     public function tweets(){
         return $this->hasMany('App\Tweet', 'user_id', 'id');
     }

     /**
      * Get Timeline
      */
     public function timeline(){
         $following = $this->following()->with(['tweets' => function($query){
                $query->orderBy('created_at', 'desc');
                $query->orderBy('id', 'desc');
                $query->paginate(10);
         }])->get();

         $timeline = $following->flatMap(function($values){
             return $values->tweets;
         });

         $sorted = $timeline->sortByDesc(function($tweet){
             return $tweet->created_at;
         });

         return $sorted->values()->all();
     }

}
