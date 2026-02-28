<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('name', 100);
            $table->string('provider', 100)->nullable();
            $table->json('config');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Seed existing Mpesa config from .env (shortcode is always set even when keys are empty)
        if (env('MPESA_SHORTCODE')) {
            \DB::table('integrations')->insert([
                'type' => 'mpesa',
                'name' => 'Main Paybill',
                'provider' => 'Safaricom',
                'config' => json_encode([
                    'shortcode' => env('MPESA_SHORTCODE', ''),
                    'consumer_key' => env('MPESA_CONSUMER_KEY', ''),
                    'consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),
                    'passkey' => env('MPESA_LNMO_PASSKEY', ''),
                    'stk_url' => env('MPESA_STK_URL', 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'),
                    'oauth_url' => env('MPESA_OAUTH_URL', 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'),
                    'c2b_register_url' => env('MPESA_C2B_REGISTER_URL', 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl'),
                    'callback_base_url' => env('MPESA_CALLBACK_BASE_URL', ''),
                    'account_ref' => env('MPESA_ACCOUNT_REF', 'Church Donation'),
                ]),
                'is_active' => true,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed existing SMS config from .env (uses AdvantaSMS: apikey, partnerID, shortcode, mobile)
        if (env('SMS_API_KEY')) {
            \DB::table('integrations')->insert([
                'type' => 'sms',
                'name' => 'AdvantaSMS',
                'provider' => 'AdvantaSMS',
                'config' => json_encode([
                    'url' => env('SMS_URL', ''),
                    'api_key' => env('SMS_API_KEY', ''),
                    'partner_id' => env('SMS_PARTNER_ID', ''),
                    'short_code' => env('SMS_SHORT_CODE', ''),
                ]),
                'is_active' => true,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed existing Email config from .env
        if (env('MAIL_HOST')) {
            $encryption = env('MAIL_ENCRYPTION');
            // .env has MAIL_ENCRYPTION=null as a literal string
            if ($encryption === 'null' || $encryption === null) {
                $encryption = '';
            }
            \DB::table('integrations')->insert([
                'type' => 'email',
                'name' => 'Default Mailer',
                'provider' => env('MAIL_MAILER', 'smtp'),
                'config' => json_encode([
                    'host' => env('MAIL_HOST', ''),
                    'port' => env('MAIL_PORT', '465'),
                    'username' => env('MAIL_USERNAME', ''),
                    'password' => env('MAIL_PASSWORD', ''),
                    'encryption' => $encryption,
                    'from_address' => env('MAIL_FROM_ADDRESS', ''),
                    'from_name' => config('app.name', env('MAIL_FROM_NAME', '')),
                ]),
                'is_active' => true,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('integrations');
    }
};
