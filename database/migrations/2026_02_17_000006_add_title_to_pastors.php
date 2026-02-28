<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pastors', function (Blueprint $table) {
            $table->string('title', 50)->default('Pastor')->after('user_id');
        });

        // Migrate existing data
        \DB::table('pastors')->where('status', 1)->update(['title' => 'Senior Pastor']);
        \DB::table('pastors')->where('status', 0)->update(['title' => 'Pastor']);

        Schema::table('pastors', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        Schema::table('pastors', function (Blueprint $table) {
            $table->integer('status')->default(0)->after('user_id');
        });

        \DB::table('pastors')->where('title', 'Senior Pastor')->update(['status' => 1]);
        \DB::table('pastors')->where('title', '!=', 'Senior Pastor')->update(['status' => 0]);

        Schema::table('pastors', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};
