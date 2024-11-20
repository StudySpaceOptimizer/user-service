<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserProfile;

class UserProfileSeeder extends Seeder
{
    public function run()
    {
        // 生成 10 筆假資料
        UserProfile::factory(10)->create();
    }
}
