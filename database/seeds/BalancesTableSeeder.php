<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Core\Constants;

class BalancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('balances')->insert([
            'balance'    => 1000.50,
            'account_nr' => uniqid('A'),
            'created_at' => date(Constants::DATE_FORMAT),
            'updated_at' => date(Constants::DATE_FORMAT),
        ]);

        DB::table('balances')->insert([
            'balance'    => 20000,
            'account_nr' => uniqid('A'),
            'created_at' => date(Constants::DATE_FORMAT),
            'updated_at' => date(Constants::DATE_FORMAT),
        ]);

        DB::table('balances')->insert([
            'balance'    => 30,
            'account_nr' => uniqid('A'),
            'created_at' => date(Constants::DATE_FORMAT),
            'updated_at' => date(Constants::DATE_FORMAT),
        ]);

        DB::table('balances')->insert([
            'balance'    => 1,
            'account_nr' => uniqid('A'),
            'created_at' => date(Constants::DATE_FORMAT),
            'updated_at' => date(Constants::DATE_FORMAT),
        ]);

        DB::table('balances')->insert([
            'balance'    => 600000,
            'account_nr' => uniqid('A'),
            'created_at' => date(Constants::DATE_FORMAT),
            'updated_at' => date(Constants::DATE_FORMAT),
        ]);

        DB::table('balances')->insert([
            'balance'    => 100.5,
            'account_nr' => uniqid('A'),
            'created_at' => date(Constants::DATE_FORMAT),
            'updated_at' => date(Constants::DATE_FORMAT),
        ]);

        DB::table('balances')->insert([
            'balance'    => 450.78,
            'account_nr' => uniqid('A'),
            'created_at' => date(Constants::DATE_FORMAT),
            'updated_at' => date(Constants::DATE_FORMAT),
        ]);

        DB::table('balances')->insert([
            'balance'    => 99.99,
            'account_nr' => uniqid('A'),
            'created_at' => date(Constants::DATE_FORMAT),
            'updated_at' => date(Constants::DATE_FORMAT),
        ]);

        DB::table('balances')->insert([
            'balance'    => 5000,
            'account_nr' => uniqid('A'),
            'created_at' => date(Constants::DATE_FORMAT),
            'updated_at' => date(Constants::DATE_FORMAT),
        ]);

        DB::table('balances')->insert([
            'balance'    => 60.7,
            'account_nr' => uniqid('A'),
            'created_at' => date(Constants::DATE_FORMAT),
            'updated_at' => date(Constants::DATE_FORMAT),
        ]);

        DB::table('balances')->insert([
            'balance'    => 0,
            'account_nr' => uniqid('A'),
            'created_at' => date(Constants::DATE_FORMAT),
            'updated_at' => date(Constants::DATE_FORMAT),
        ]);

        // accounts used in api tests
        DB::table('balances')->insert([
            'balance'    => 1000,
            'account_nr' => 'test-acc-1',
            'created_at' => date(Constants::DATE_FORMAT),
            'updated_at' => date(Constants::DATE_FORMAT),
        ]);

        DB::table('balances')->insert([
            'balance'    => 50,
            'account_nr' => 'test-acc-2',
            'created_at' => date(Constants::DATE_FORMAT),
            'updated_at' => date(Constants::DATE_FORMAT),
        ]);
    }
}
