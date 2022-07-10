<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Twiml;
use Twilio\TwiML\VoiceResponse;
use Twilio\Rest\Client;
use App\Models\User;
use App\Models\IVRCall;


class IvrController extends Controller
{
    //
    public function __construct()
    {
        $this->_thankYouMessage = 'Welcome to Extreme Agile.';
    }


    /**
     * Responds with a welcome message and ask name of caller
     *
     * @return \Illuminate\Http\Response
     */
    public function showWelcome()
    {

        $response = new Twiml();
        $response = new VoiceResponse();

        $response->say('Welcome to Extreme Agile IVR System.');
        $response->say('Please say your name after the beep. Press the star key when finished.');

        // $response->record(['finishOnKey' => '*','transcribe' => 'true','maxLength' => 10,'transcribeCallback'=>'http://ivr.mysudocode.com/saveRecording', 'method' => 'get']);

        $response->record(
                            ['maxLength' => '10',
                             'method' => 'GET',
                             'transcribe' => 'true',
                             'action' => route('showGenderOption', [], false),                     
                            ]
                          ); 

        $response->say('We didn\'t receive any input. Goodbye!');

        return $response;
    } 

    /*
    * Show gender options
    *
    */

    public function showGenderOption(Request $request)
    {
        $response = new Twiml();

        // $TranscriptionText = $request->input('TranscriptionText'); 
        $recording_url = $request->input('RecordingUrl'); 

        $gather = $response->gather(
            [
                'numDigits' => 1,
                'action' => route('showInterest', ['recording_url'=>$recording_url], false)
            ]
        );

       $gather->say(
            'Now Choose your gender' .            
            ' Press 1 for Male. ' .
            ' Press 2 for Female. ',
            ['voice' => 'alice','timeout' => 10, 'language' => 'en-US']
        );

        $response->say('We didn\'t receive any input. Goodbye!',['voice' => 'alice','language' => 'en-US']);
        
        return $response;
    } 

    // ask for interest 
    public function showInterest(Request $request)
    {
        $selectedOption = $request->input('Digits');
        $recording_url = $request->input('recording_url');

        $userGender = "";
        if($selectedOption == 1){
            $userGender = "Male";
        }
        if($selectedOption == 2){
            $userGender = "Female";
        }

        $response = new Twiml();
        
        switch ($selectedOption) {            
            case 1:                
                $response = new Twiml();  

                $gather = $response->gather([
                    'numDigits' => 1,
                    'action' => route('saveInterest', ['userGender'=>$userGender,'recording_url'=>$recording_url], false)
                ]);

                $gather->say(
                    'Are you interested in Female' .            
                    ' Press 1 for Yes. ' .
                    ' Press 2 for No. ',
                    ['voice' => 'alice','timeout' => 10, 'language' => 'en-US']);

                return $response;

            case 2: 
                $gather = $response->gather([
                    'numDigits' => 1,
                    'action' => route('saveInterest', [], false)
                ]);

                $gather->say(
                    'Are you interested in Male' .            
                    ' Press 1 for Yes. ' .
                    ' Press 2 for No. ',
                ['voice' => 'alice','timeout' => 10, 'language' => 'en-US']);
                
                return $response;

            default:           
             $response->say('Sorry, you entered wrong input, try again');
             $response->redirect(route('welcome', [], false));
            return $response;
        }   

        $response->say(
            'Returning to the main menu',
            ['voice' => 'alice']);

        $response->redirect(route('welcome', [], false));
        
        return $response;
    }

    
    /*
    * Save selected options
    *
    */
    public function saveInterest(Request $request)
    {
        $response = new Twiml();

        $selectedOption = $request->input('Digits');

        $userGender = $request->input('userGender'); 
        $recording_url = $request->input('recording_url'); 
        $caller_number = $request->input('From'); 

        $choiceYesNo = "";
        if($selectedOption == 1){
            $choiceYesNo = "Yes";
        }
        if($selectedOption == 2){
            $choiceYesNo = "No";
        }

        //set title based on gender
        $intrestedIn=$title = "";

        if($userGender == 'Male'){
            $title = "Mr.";
            $intrestedIn= "intrested In Female: ".$choiceYesNo;
        }
        if($userGender == 'Female'){
            $intrestedIn= "intrested In Male: ".$choiceYesNo;
        }

        // DB::table('user_inputs')->insert(['gender'=>$userGender,'choice'=>$intrestedIn,'call_from'=>$caller_number,'text_log'=>$recording_url]);

        $userData = new IVRCall;

        $userData->gender = $userGender;
        $userData->choice = $intrestedIn;
        $userData->call_from = $caller_number;
        $userData->recording_url = $recording_url;
 
        $userData->save();

        $response->say(
            'Thank you for submitting the info!'. $title,
            ['voice' => 'alice','language' => 'en-US']);
        
        return $response;
    } 

    /*
    * Save selected options
    *
    */
    public function saveRecording(Request $request)
    {
        $response = new Twiml();
        $caller_number = $request->input('From'); 
        $recording_url = $request->input('TranscriptionText'); 

        DB::table('user_inputs')->insert(['call_from'=>$caller_number,'text_log'=>$recording_url]);

        $response->say(
            'Your data has been saved',
            ['voice' => 'alice','language' => 'en-US']);
        
        return $response;
    } 


}
