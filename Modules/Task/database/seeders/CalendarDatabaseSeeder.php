<?php

namespace Modules\Task\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Database Seeder cho Calendar
 * 
 * Seeder này chạy các seeders khác để tạo dữ liệu mẫu cho Calendar
 * Tuân thủ Clean Architecture: chỉ chứa seeding logic, không chứa business logic phức tạp
 */
class CalendarDatabaseSeeder extends Seeder
{
    /**
     * Chạy database seeds
     */
    public function run(): void
    {
        // $this->call([]);
    }
}
