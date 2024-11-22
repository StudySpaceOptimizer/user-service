<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->string('email')->primary(); // 將 email 設為主鍵
            $table->boolean('is_in')->default(false);
            $table->integer('point')->default(0);
            $table->string('name')->nullable();
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->string('ban_reason')->nullable();
            $table->timestamp('ban_end_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
}
