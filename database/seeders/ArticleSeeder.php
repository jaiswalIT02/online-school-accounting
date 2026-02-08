<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleSeeder extends Seeder
{
    public function run()
    {
        DB::table('articles')->insert([
            ['id' => 1, 'name' => 'Food/Lodging per child per month', 'acode' => '1.6.4.23.1.4.1', 'status' => 1],
            ['id' => 2, 'name' => 'Stipend per girl per month', 'acode' => '1.6.4.23.1.4.2', 'status' => 1],
            ['id' => 3, 'name' => 'Supplementary TLM Stationery and other educational material', 'acode' => '1.6.4.23.1.4.3', 'status' => 1],
            ['id' => 4, 'name' => 'Warden', 'acode' => '1.6.4.23.1.4.4', 'status' => 1],
            ['id' => 5, 'name' => 'Part time teachers', 'acode' => '1.6.4.23.1.4.5', 'status' => 1],
            ['id' => 6, 'name' => 'Full time Accountant', 'acode' => '1.6.4.23.1.4.6', 'status' => 1],
            ['id' => 7, 'name' => 'Support Staff - Accountant / Assistant Peon Chowkidar', 'acode' => '1.6.4.23.1.4.7', 'status' => 1],
            ['id' => 8, 'name' => 'Head Cook', 'acode' => '1.6.4.23.1.4.8', 'status' => 1],
            ['id' => 9, 'name' => 'Assistant Cook', 'acode' => '1.6.4.23.1.4.9', 'status' => 1],
            ['id' => 10, 'name' => 'Head Teacher/Principal', 'acode' => '1.6.4.23.1.4.10', 'status' => 1],
            ['id' => 11, 'name' => 'Full Time Teachers/Lecturer', 'acode' => '1.6.4.23.1.4.11', 'status' => 1],
            ['id' => 12, 'name' => 'Specific Skill Training', 'acode' => '1.6.4.23.1.4.12', 'status' => 1],
            ['id' => 13, 'name' => 'Electricity / Water Charges', 'acode' => '1.6.4.23.1.4.13', 'status' => 1],
            ['id' => 14, 'name' => 'Medical care / Contingencies', 'acode' => '1.6.4.23.1.4.14', 'status' => 1],
            ['id' => 15, 'name' => 'Maintenance', 'acode' => '1.6.4.23.1.4.15', 'status' => 1],
            ['id' => 16, 'name' => 'PTA Meet', 'acode' => '1.6.4.23.1.4.18', 'status' => 1],
            ['id' => 17, 'name' => 'Capacity Building for Teachers', 'acode' => '1.6.4.23.1.4.20', 'status' => 1],
            ['id' => 18, 'name' => 'Self Defence Training', 'acode' => '1.6.4.23.1.4.21', 'status' => 1],
            ['id' => 19, 'name' => 'Examination Fees', 'acode' => '1.6.4.23.1.4.22', 'status' => 1],
            ['id' => 20, 'name' => 'Text Books Class I - II', 'acode' => '1.6.4.7.1.1', 'status' => 1],
            ['id' => 21, 'name' => 'Gunotsav', 'acode' => '1.6.4.9.6', 'status' => 1],
            ['id' => 22, 'name' => 'Office Expenses / Contingencies for School Existing', 'acode' => '1.6.5.22.2.6', 'status' => 1],
            ['id' => 23, 'name' => 'Rani Laxmibai Atma Raksha Prashikshan', 'acode' => '1.6.5.20.2', 'status' => 1],
        ]);
    }
}
