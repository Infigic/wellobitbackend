<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropUnique('users_provider_id_unique');

            $table->unique(
                ['platform', 'provider_id'],
                'users_platform_provider_unique'
            );
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropUnique('users_platform_provider_unique');

            $table->unique('provider_id', 'users_provider_id_unique');
        });
    }

};
