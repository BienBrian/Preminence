<?php

namespace App\Imports;

use App\Models\Pledge;
use App\Models\PledgeSms;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\Mpesa;
use App\Models\Vehicle;
use App\Models\Transaction;

class PledgesImport implements WithChunkReading, ToCollection
{
    protected $activity_id = 0;
    protected $thankYouMessage = "";
    public function __construct($activityId, $thank_you_message)
    {
        $this->activity_id = $activityId;
        $this->thankYouMessage = $thank_you_message;
    }
    public function chunkSize(): int
    {
        return 100; //number of rows to read per chunk
    }

    public function collection(Collection $collection)
    {
        $count = 0;
        $merchant = "";
        foreach ($collection as $row) {
            //\Log::info($count.". ".$row[0].','.$row[1].','.$row[2]." Activity ID".$this->activity_id);
            if (strlen($row[1]) > 0) {
                $phone = "";
                if (strlen($row[1]) >= 9) {
                    $phones = explode("/", $row[1]);
                    if (count($phones) > 1) {
                        foreach ($phones as $p) {
                            //create user here
                            $phone = '254' . $p;
                            $user = User::where('phone', $phone)->first();
                            if ($user == null) {
                                $name = explode(" ", $row[0]);
                                $user = new User();
                                $user->phone = $phone;
                                $user->firstname = $name[0];
                                if (count($name) > 1) {
                                    $user->lastname = $name[1];
                                }
                                if (count($name) > 2) {
                                    $user->surname = $name[2];
                                }
                                $user->password = Hash::make('12345');
                                $user->status = true;
                                $user->save();
                            }
                            if ($user != null) {
                                //save pledges here
                                $pledge = new Pledge();
                                $pledge->activity = $this->activity_id;
                                $pledge->groups = 0;
                                $pledge->user_id = $user->id;
                                $pledge->paid = $row[2];
                                $pledge->amount = $row[2];
                                $pledge->status = true;
                                if($pledge->save()){
                                    $pledgeSms = new PledgeSms();
                                    $pledgeSms->pledge_id = $pledge->id;
                                    $pledgeSms->message = $this->thankYouMessage;
                                    $pledgeSms->save();
                                }
                            }
                            //\Log::info(json_encode($user));
                        }
                    }
                }


            }
            /*if ($count == 1) {
                $merchant = $row[1];
            }
            if ($count >= 7) {

                $msisdn = 0;

                $str = $row[10];

                if ($str != "") {
                    $details = explode("-", $str);

                    $msisdn = $details[0];
                    //\Log::info($details[1]);
                    $name = explode(" ", $details[1]);
                    $firstname = !empty($name[1]) ? $name[1] : '';
                    $middlename = !empty($name[2]) ? $name[2] : '';
                    $lastname = !empty($name[3]) ? $name[3] : '';
                    $transtype = "";
                    $transtime = Carbon::parse($row[1]);

                    $transamount = doubleval($row[5]);
                    //\Log::info('Amount:' . $transamount);

                    if ((Mpesa::where("TransID", $row[0])->count() == 0) && ($transamount > 0)) {
                        $mpesa = new Mpesa;
                        $mpesa->TransID = $row[0];
                        $mpesa->MSISDN = $msisdn;
                        $mpesa->TransAmount = $transamount;
                        $mpesa->TransTime = Carbon::parse($row[1]);
                        $mpesa->FirstName = $firstname;
                        $mpesa->LastName = $lastname;
                        $mpesa->MiddleName = $middlename;
                        $mpesa->BusinessShortCode = $merchant;
                        $mpesa->TransactionType = $transtype;
                        $mpesa->ThirdPartyTransID = "";
                        $mpesa->InvoiceNumber = "";
                        $mpesa->BillRefNumber = "";
                        if ($mpesa->save()) {
                            $vehicle = Vehicle::where("merchant_short_code", $merchant)->first();
                            $transaction = new Transaction;
                            $transaction->mpesa_id = $mpesa->id;
                            if($vehicle != null){
                                $transaction->vehicle_id = $vehicle->id;
                            }
                            $transaction->amount = $transamount;
                            $transaction->trans_date = $transtime;
                            $transaction->save();
                        }
                    }
                }
            }*/

            $count++;
        }

    }
}
