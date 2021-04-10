<?php

namespace App\Http\Controllers\Auth;
  
use App\Http\Controllers\Controller;
use Socialite;
use Auth;
use Exception;
use App\Models\User;
use App\Models\Team;
    
    class GoogleController extends Controller
    {
      /**
       * Create a new controller instance.
       *
       * @return void
       */
        public function redirectToGoogle()
        {
            return Socialite::driver('google')->redirect();
        }
        
      /**
       * Create a new controller instance.
       *
       * @return void
       */
      public function handleGoogleCallback()
      {
          try {
            //create a user using socialite driver google
            $user = Socialite::driver('google')->stateless()->user();
          }

          catch (exception $e) {
              dd ($e);
          }
          
              
              // if the user exits, use that user and login
              $finduser = User::where('google_id', $user->id)->first();
              if($finduser){
                  //if the user exists, login and show dashboard
                  Auth::login($finduser);
                  return redirect('/dashboard');
                }
                else {
                    //user is not yet created, so create first
                    $newUser = User::create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'google_id'=> $user->id,
                        'password' => encrypt('NnPWYJn1D22w')
                  ]);

                    //every user needs a team for dashboard/jetstream to work.
                    //create a personal team for the user
                    $newTeam = Team::forceCreate([
                        'user_id' => $newUser->id,
                        'name' => explode(' ', $user->name, 2)[0]."'s Team",
                        'personal_team' => true,
                    ]);
                    // save the team and add the team to the user.
                    $newTeam->save();
                    $newUser->current_team_id = $newTeam->id;
                    $newUser->save();
                    //login as the new user
                    Auth::login($newUser);
                    return redirect('/dashboard');
                    }
      }
  }
  