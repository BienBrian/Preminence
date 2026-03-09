<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\Funds;
use App\Models\Integration;
use App\Models\MissingMpesaPhone;
use App\Models\MpesaPhone;
use App\Models\MpesaTransaction;
use App\Models\Tenant;
use App\Services\IntegrationService;
use App\Services\MpesaContactSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MpesaAPIController extends Controller
{
    protected $site_settings;
    protected IntegrationService $integrations;

    public function __construct(IntegrationService $integrations)
    {
        $this->integrations  = $integrations;
        $this->site_settings = \DB::table("settings")->first();
        \View::share('site_settings', $this->site_settings);
    }

    public function lipaNaMpesaPassword()
    {
        return $this->integrations->lipaNaMpesaPassword();
    }
    /**
     * Lipa na M-PESA STK Push method
     * */
    public function customerMpesaSTKPush(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            "phone"=>"required|numeric",
            "firstname"=>"string|required",
            "lastname"=>"string|required",
            "amount"=>"numeric|required",
            "account"=>"string|required",
        ]);
        if($validator->passes()){
            $mpesaConfig = $this->integrations->getMpesaConfig();
            $phone = "254".intval($request->phone);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $mpesaConfig['stk_url']);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$this->generateAccessToken()));
            $shortcode = $mpesaConfig['shortcode'];
            $curl_post_data = [
                'BusinessShortCode' => $shortcode,
                'Password'          => $this->lipaNaMpesaPassword(),
                'Timestamp'         => Carbon::rawParse('now')->format('YmdHms'),
                'TransactionType'   => 'CustomerPayBillOnline',
                'Amount'            => intval($request->amount),
                'PartyA'            => $phone,
                'PartyB'            => $shortcode,
                'PhoneNumber'       => $phone,
                'CallBackURL'       => $mpesaConfig['callback_base_url'].'/api/stk/confirmation',
                'AccountReference'  => $mpesaConfig['account_ref'],
                'TransactionDesc'   => "Church donation via mpesa"
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
        return $this->integrations->generateMpesaAccessToken();
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
        \Log::info("STK PUSH callback received");
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
        Log::info("M-Pesa confirmation callback received");
        $content = json_decode($request->getContent());

        // ── Phase 3: Resolve tenant by BusinessShortCode ───────────────────────
        $shortcode = $content->BusinessShortCode ?? null;
        if ($shortcode) {
            $mpesaIntegration = $this->integrations->resolveMpesaIntegrationByShortcode((string)$shortcode);
            if ($mpesaIntegration) {
                $tenant = Tenant::withoutGlobalScopes()->find($mpesaIntegration->tenant_id);
                if ($tenant) {
                    app()->instance('tenant', $tenant);
                    config(['app.tenant_id' => $tenant->id]);
                    // Refresh integration service cache for this tenant context
                    $this->integrations = app(IntegrationService::class);
                    Log::info("M-Pesa callback routed to tenant #{$tenant->id} ({$tenant->name}) for shortcode {$shortcode}");
                } else {
                    Log::warning("M-Pesa callback: integration found for shortcode {$shortcode} but tenant #{$mpesaIntegration->tenant_id} not found");
                }
            } else {
                Log::warning("M-Pesa callback: no integration found for shortcode {$shortcode}");
            }
        }
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

        // Build message from DB settings or fallback to default
        $mpesaSettings = \DB::table('mpesa_message_settings')->first();
        if ($mpesaSettings && $mpesaSettings->active && $mpesaSettings->message) {
            $message = str_replace(
                ['{{NAME}}', '{{AMOUNT}}', '{{ACCOUNT}}'],
                [$content->FirstName, number_format(doubleval($content->TransAmount), 2), strtoupper($content->BillRefNumber)],
                $mpesaSettings->message
            );
        } else {
            $message = "Dear, ".$content->FirstName." Thank you for honouring the Lord with your finances (Proverbs 3:9). Your support of Ksh. " .number_format(doubleval($content->TransAmount), 2). " through ".strtoupper($content->BillRefNumber). " account will support the ministry in great ways. Be blessed. \r\nGod loves a cheerful giver II Cor 9:7. \r\n#2026:Year of Growth. \r\n For Prayers call Reverend Hosea (0721895977).";
        }

        // Try to match the phone number to a contact
        $user = null;
        $actualPhone = null;

        // First: try direct phone lookup (works when MSISDN is plain number like 254712345678)
        if (strlen($content->MSISDN) <= 15 && is_numeric($content->MSISDN)) {
            $user = \DB::table("contacts")->where("phone", "0".substr($content->MSISDN, 3))->first();
            $actualPhone = $content->MSISDN;
        }

        // Second: try mpesa_phones hash lookup (works when MSISDN is hashed)
        // Use withoutTenantScope() because this is an unauthenticated webhook — tenant context
        // may not yet be resolved if ShortCode lookup fails (no integrations row for this shortcode).
        $mpesaPhone = MpesaPhone::withoutTenantScope()->where('phone_hash', $content->MSISDN)->first();
        if ($user == null && $mpesaPhone != null) {
            $user = \DB::table("contacts")->where("phone", "0".substr($mpesaPhone->phone, 3))->first();
            $actualPhone = $mpesaPhone->phone;
        }

        // Create funds record
        $funds = new Funds();
        $funds->amount = doubleval($content->TransAmount);
        $funds->description = $message;
        $funds->source = 1;
        $funds->user_id = $user != null ? $user->user_id : 0;
        $funds->mode = 2;
        $funds->save();

        // Send SMS and track phone
        $smsPhone = null;
        if ($mpesaPhone != null) {
            $smsPhone = $mpesaPhone->phone;
        } elseif ($actualPhone != null) {
            $smsPhone = $actualPhone;
        } else {
            // Phone not recognized - add to missing phones
            $missingMpesaPhone = new MissingMpesaPhone();
            $missingMpesaPhone->name = $content->FirstName;
            $missingMpesaPhone->trans_id = $content->TransID;
            $missingMpesaPhone->phone_hash = $content->MSISDN;
            $missingMpesaPhone->trans_date = Carbon::parse($content->TransTime);
            $missingMpesaPhone->save();
        }

        if ($smsPhone) {
            $smsResponse = $this->send($smsPhone, $message);
            $smsSentOk = false;

            // Check if SMS API returned success
            if (is_array($smsResponse)) {
                $responseCode = $smsResponse['response-code'] ?? $smsResponse['response_code'] ?? $smsResponse['code'] ?? null;
                $smsSentOk = ($responseCode === null || (int)$responseCode === 200 || (int)$responseCode === 0);
            } elseif ($smsResponse !== null && $smsResponse !== false) {
                $smsSentOk = true;
            }

            $tid = config('app.tenant_id', 1);
            if ($smsSentOk) {
                // Log to sms + sms_recipients tables
                $mid = \DB::table('sms')->insertGetId([
                    'tenant_id' => $tid,
                    'people_id' => 0,
                    'message'   => $message,
                    'category'  => 'mpesa',
                    'sent'      => Carbon::now(),
                ]);
                // Link to user if we found one
                if ($user && $user->user_id > 0) {
                    \DB::table('sms_recipients')->insert([
                        'tenant_id'  => $tid,
                        'recipients' => $user->user_id,
                        'sms_id'     => $mid,
                        'sent'       => Carbon::now(),
                    ]);
                }
            } else {
                // SMS API failed (e.g. out of credits) — queue for retry
                Log::warning("MPESA SMS failed for {$smsPhone}, queueing for retry. Response: " . json_encode($smsResponse));
                \DB::table('pending_sms')->insert([
                    'tenant_id'      => $tid,
                    'phone'          => $smsPhone,
                    'message'        => $message,
                    'transaction_id' => $content->TransID ?? null,
                    'category'       => 'mpesa',
                    'attempts'       => 0,
                    'status'         => 'pending',
                    'created_at'     => Carbon::now(),
                    'updated_at'     => Carbon::now(),
                ]);
            }
        }

        // ── Contact Sync: Ensure phone hash is recorded for future transactions ─
        // This ensures no hash is skipped and enables automatic SMS delivery
        try {
            $syncService = app(MpesaContactSyncService::class);
            $syncResult = $syncService->processTransactionPhone(
                $content->MSISDN,
                $content->FirstName,
                $content->TransID,
                doubleval($content->TransAmount)
            );
            
            Log::info("M-Pesa contact sync completed", [
                'trans_id' => $content->TransID,
                'status' => $syncResult['status'],
                'contact_found' => $syncResult['contact_found'],
                'mpesa_phone_created' => $syncResult['mpesa_phone_created'],
            ]);
        } catch (\Exception $e) {
            // Don't fail the transaction if sync fails - log and continue
            Log::error("M-Pesa contact sync failed", [
                'trans_id' => $content->TransID,
                'error' => $e->getMessage(),
            ]);
        }

        // Responding to the confirmation request
        $response = new Response();
        $response->headers->set("Content-Type","text/xml; charset=utf-8");
        $response->setContent(json_encode(["C2BPaymentConfirmationResult"=>"Success"]));
        return $response;
    }

    public function mpesaRegisterUrls()
    {
        $mpesaConfig = $this->integrations->getMpesaConfig();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $mpesaConfig['c2b_register_url']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Bearer '. $this->generateAccessToken()));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        $baseUrl = $mpesaConfig['callback_base_url'];
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
            'ShortCode'       => $mpesaConfig['shortcode'],
            'ResponseType'    => 'Completed',
            'ConfirmationURL' => $baseUrl."/api/transaction/confirmation",
            'ValidationURL'   => $baseUrl."/api/validation"
        )));
        $curl_response = curl_exec($curl);
        return response()->json(json_decode($curl_response, true) ?? ['error' => 'Invalid response']);
    }

    public function testSMS(Request $request){
        $validator = Validator::make($request->all(), ['phone'=>'digits:12|required', 'message'=>'required|string|max:160|min:1']);
        if($validator->fails()){
            return response()->json(['error'=>$validator->messages()], 400);
        }
        return $this->send($request->phone, $request->message);
    }
    public function send($number, $message){
        return $this->integrations->sendSms($number, $message);
    }
}
