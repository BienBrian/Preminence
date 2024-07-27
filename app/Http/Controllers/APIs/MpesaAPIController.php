<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\Funds;
use App\Models\MissingMpesaPhone;
use App\Models\MpesaPhone;
use App\Models\MpesaTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MpesaAPIController extends Controller
{
    public function __construct()
    {
        $this->site_settings = \DB::table("settings")->first();
        \View::share('site_settings', $this->site_settings);
    }

    public function lipaNaMpesaPassword()
    {
        $lipa_time = Carbon::rawParse('now')->format('YmdHms');
        $passkey = "43ea7e68502a5aa0a4c29c1cc2b1eb0ea164863d9f12d3faf4010574405d4179";
       // $passkey = env('MPESA_LNMO_PASSKEY');
        $BusinessShortCode = 186903;
        $timestamp =$lipa_time;
        $lipa_na_mpesa_password = base64_encode($BusinessShortCode.$passkey.$timestamp);
        return $lipa_na_mpesa_password;
    }
    /**
     * Lipa na M-PESA STK Push method
     * */
    public function customerMpesaSTKPush(Request $request)
    {
        //return json_encode($request->all());
        $validator = \Validator::make($request->all(), [
            "phone"=>"required|numeric",
            "firstname"=>"string|required",
            "lastname"=>"string|required",
            "amount"=>"numeric|required",
            "account"=>"string|required",
        ]);
        if($validator->passes()){
            //return $request->phone;
            $phone = "254".intval($request->phone);
            $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$this->generateAccessToken()));
            $curl_post_data = [
                //Fill in the request parameters with valid values
                'BusinessShortCode' => 186903,
                'Password' => $this->lipaNaMpesaPassword(),
                'Timestamp' => Carbon::rawParse('now')->format('YmdHms'),
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => intval($request->amount),
                'PartyA' => $phone, // replace this with your phone number
                'PartyB' => 186903,
                'PhoneNumber' => $phone, // replace this with your phone number
                'CallBackURL' => 'https://happychurchruiru.org/api/stk/confirmation?firstname='
                .$request->firstname."&lastname=".$request->lastname."&account=".$request->account,
                'AccountReference' => "Church Donation",
                'TransactionDesc' => "Church donation via mpesa"
            ];
            $data_string = json_encode($curl_post_data);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            $curl_response = curl_exec($curl);
            return $curl_response;
        }else{
            return response()->json(["errors"=>$validator->messages()], 400);
        }
    }

    public function generateAccessToken()
    {
        $consumer_key="nCqpncqiIOazyXdpMqaMCxoa2fMnAtp8";
        $consumer_secret="KgcGBsfCKPzcdFMR";
        $credentials = base64_encode($consumer_key.":".$consumer_secret);
        $url = "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic ".$credentials));
        curl_setopt($curl, CURLOPT_HEADER,false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        $access_token=json_decode($curl_response);
        return $access_token->access_token;
    }

        /**
     * J-son Response to M-pesa API feedback - Success or Failure
     */
    public function createValidationResponse($result_code, $result_description){
        $result=json_encode(["ResultCode"=>$result_code, "ResultDesc"=>$result_description]);
        $response = new Response();
        $response->headers->set("Content-Type","application/json; charset=utf-8");
        $response->setContent($result);
        return $response;
    }
    /**
     *  M-pesa Validation Method
     * Safaricom will only call your validation if you have requested by writing an official letter to them
     */
    public function mpesaValidation(Request $request)
    {
        $result_code = "0";
        $result_description = "Accepted validation request.";
        return $this->createValidationResponse($result_code, $result_description);
    }

    /*stk callback url*/
    public function stkResponse(Request $request){
        \Log::info("STK PUSH NAME:".$request->firstname." ".$request->lastname);
        \Log::info($request->getContent());
        /*$content=json_decode($request->getContent());
        if($content->Body->stkCallback->ResultCode == 0) {
            //\Log::info(json_encode($content->Body->stkCallback->ResultDesc));
            //\Log::info(json_encode($content->Body->stkCallback->CallbackMetadata);
            $items = $content->Body->stkCallback->CallbackMetadata->Item;
            $amount = 0.0;
            $transid = "";
            $transdate = "";
            $phone = "";
            foreach ($items as $item){
                $name = (string)$item->Name;
                if($name != "Balance"){
                    $amount = $name == "Amount"?(double)$item->Value:$amount;
                    $transid = $name == "MpesaReceiptNumber"?(string)$item->Value:$transid;
                    $transdate = $name == "TransactionDate"?"".substr((string) $item->Value,0,4)."-".substr((string) $item->Value,4,2)."-".substr((string) $item->Value,6,2)." ".substr((string) $item->Value,8,2).":".substr((string) $item->Value,10,2).":".substr((string) $item->Value,12,2):$transdate;
                    $phone = $name == "PhoneNumber"?(string) $item->Value:$phone;
                }
            }
            //save here
            $mpesa_transaction = new MpesaTransaction();
            $mpesa_transaction->TransactionType = "STK PUSH";
            $mpesa_transaction->TransID = $transid;
            $mpesa_transaction->TransTime = $transdate;
            $mpesa_transaction->TransAmount = $amount;
            $mpesa_transaction->BusinessShortCode = "186903";
            $mpesa_transaction->BillRefNumber = $request->account;
            $mpesa_transaction->InvoiceNumber = "";
            $mpesa_transaction->ThirdPartyTransID = "";
            $mpesa_transaction->MSISDN = $phone;
            $mpesa_transaction->FirstName = $request->firstname;
            $mpesa_transaction->MiddleName = "";
            $mpesa_transaction->LastName = $request->lastname;
            $mpesa_transaction->save();

            $user = \DB::table("contacts")->where("phone", "0".substr($phone, 3))->first();
            $gender = "";
            $message = "Thank you ".$request->firstname." for supporting the ministry, May peace be within your walls and prosperity in your palaces.  \nYours, \nRev Hosea 0721895977";
            if($user != null){
                //insert
                $funds = new Funds;
                $funds->amount = $amount;
                $funds->description = $message;
                $funds->source = 1;
                $funds->user_id = $user->user_id;
                $funds->mode = 2;
                $funds->save();
            }else{
                $funds = new Funds;
                $funds->amount = $amount;
                $funds->description = $message;
                $funds->source = 1;
                $funds->user_id = 0;
                $funds->mode = 2;
                $funds->save();
            }

            $this->send($phone, $message);

            //\Log::info("Amount: ".$amount.", Transid: ".$transid.", Transdate: ".$transdate.", Phone:".$phone);

        }*/

    }
    /**
     * M-pesa Transaction confirmation method, we save the transaction in our databases
     */
    public function mpesaConfirmation(Request $request)
    {
        Log::info($request->getContent());
        $content=json_decode($request->getContent());
        $mpesa_transaction = new MpesaTransaction();
        $mpesa_transaction->TransactionType = $content->TransactionType;
        $mpesa_transaction->TransID = $content->TransID;
        $mpesa_transaction->TransTime = $content->TransTime;
        $mpesa_transaction->TransAmount = $content->TransAmount;
        $mpesa_transaction->BusinessShortCode = $content->BusinessShortCode;
        $mpesa_transaction->BillRefNumber = $content->BillRefNumber;
        $mpesa_transaction->InvoiceNumber = $content->InvoiceNumber;
        $mpesa_transaction->OrgAccountBalance = $content->OrgAccountBalance;
        $mpesa_transaction->ThirdPartyTransID = $content->ThirdPartyTransID;
        $mpesa_transaction->MSISDN = $content->MSISDN;
        $mpesa_transaction->FirstName = $content->FirstName;
        $mpesa_transaction->MiddleName = $content->MiddleName;
        $mpesa_transaction->LastName = $content->LastName;
        $mpesa_transaction->save();

        $user = \DB::table("contacts")->where("phone", "0".substr($content->MSISDN, 3))->first();
        $gender = "";
        $message = "Dear, ".$content->FirstName." Thank you for honouring the LORD with your income (Proverbs 3:9). Your support of Ksh. " .number_format(doubleval($content->TransAmount), 2). " through ".strtoupper($content->BillRefNumber). " account will support the ministry in different ways. I wish you and your family a Merry Christmas and a blessed 2024 . \r\n Reverend Hosea. \r\n For Prayers call 0721895977.";
        if($user != null){
            /*$gender = $user->gender == null?"":$user->gender==0?"Bro":"Sis";
            //save funds
            $message = "Praise God ".$gender." ".$content->FirstName.", Your tithe amounting Ksh ".number_format(doubleval($content->TransAmount), 2).
            " has been received with gratitude. May God bless you abudantly. Regards. Rev Hosea. 0721895977.";
            */
            //insert
            $funds = new Funds();
            $funds->amount = doubleval($content->TransAmount);
            $funds->description = $message;
            $funds->source = 1;
            $funds->user_id = $user->user_id;
            $funds->mode = 2;
            $funds->save();
        }else{
            $funds = new Funds;
            $funds->amount = doubleval($content->TransAmount);
            $funds->description = $message;
            $funds->source = 1;
            $funds->user_id = 0;
            $funds->mode = 2;
            $funds->save();
        }
        $mpesaPhone = MpesaPhone::where('phone_hash', $content->MSISDN)->first();
        if($mpesaPhone != null){
            $this->send($mpesaPhone->phone, $message);
        }else{
            $missingMpesaPhone = new MissingMpesaPhone();
            $missingMpesaPhone->name = $content->FirstName;
            $missingMpesaPhone->trans_id = $content->TransID;
            $missingMpesaPhone->phone_hash = $content->MSISDN;
            $missingMpesaPhone->trans_date = Carbon::parse($content->TransTime);
            $missingMpesaPhone->save();
        }
        // Responding to the confirmation request

        $response = new Response();
        $response->headers->set("Content-Type","text/xml; charset=utf-8");
        $response->setContent(json_encode(["C2BPaymentConfirmationResult"=>"Success"]));
        return $response;
    }

    public function mpesaRegisterUrls()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Bearer '. $this->generateAccessToken()));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
            'ShortCode' => "186903",
            'ResponseType' => 'Completed',
            'ConfirmationURL' => "https://happychurchruiru.org/api/transaction/confirmation",
            'ValidationURL' => "https://happychurchruiru.org/api/validation"
        )));
        $curl_response = curl_exec($curl);
        echo $curl_response;
    }

    public function send($number, $message){
        /*$username = "newhappychurch"; //username for your bulk sms account
        $password = "Middle6224"; //password for your bulk sms account
        $apiKey = "5efdc9bf6f824"; //apikey for your bulk sms account
        $shortcode = "HappyChurch"; //"22136" for demo; //assigned sender ID
        $method = 'sendsms'; // method to invoke{sendsms - to send SMS | balance - to check credit balance}

        $site_settings = $this->site_settings;
        $appname = $site_settings != null?"".$site_settings->name:"CHURCH APP";

        $finalURL = "http://bulkapi.mobitechtechnologies.com/?username=" . urlencode($username) . "&password=" . urlencode($password) . "&apiKey=" . urlencode($apiKey) . "&message=" . urlencode($message) . "&senderID=".$shortcode."&msisdn=".$number."&method=".$method;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $finalURL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
            return false;//return "cURL Error #:" . $err;
        } else {
            return true; //return $response;
        }*/
         $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://sms.tenasms.com/api/services/sendsms',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => 'apikey=190004e4d0958537ecb550eaa57eb9af&partnerID=9802&message=' . urlencode($message) . '&shortcode=HappyCRuiru&mobile='.$number,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        )
        );

        $curl_response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($curl_response, true);
        //\Log::info(json_encode($response).'NUMBER: '.$number);
        return $response;
    }
}
