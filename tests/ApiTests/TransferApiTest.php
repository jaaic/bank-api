<?php

namespace Tests\ApiTests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * Class TransferApiTest
 *
 * @package Tests\ApiTests
 * @author  Jaai Chandekar
 */
class TransferApiTest extends TestCase
{
    use DatabaseMigrations;

    public function createMockData()
    {
        factory('App\Modules\Transactions\Models\Balance')->create([
            'account_nr' => 'test-acc-1',
            'balance'    => 1000,
        ]);

        factory('App\Modules\Transactions\Models\Balance')->create([
            'account_nr' => 'test-acc-2',
            'balance'    => 10,
        ]);

        $this->seeInDatabase('balances', ['account_nr' => 'test-acc-1', 'balance' => 1000]);
        $this->seeInDatabase('balances', ['account_nr' => 'test-acc-2', 'balance' => 10]);

    }

    /**
     * Test transfer endpoint and the results
     *
     * @return void
     */
    public function testTransferSuccess()
    {
        $this->createMockData();
        $response = $this->call('POST', '/transfer', [
            'from'   => 'test-acc-1',
            'to'     => 'test-acc-2',
            'amount' => 10,
        ]);

        $result = json_decode($response->getContent(), true);

        $this->assertTrue(array_has($result, ['id', 'from', 'to', 'transferred']));
        $this->assertEquals(990, $result['from']['balance']);
        $this->assertEquals(20, $result['to']['balance']);
        $this->assertEquals(10, $result['transferred']);

        $this->seeInDatabase('transactions', ['reference' => $result['id'], 'account_nr' => 'test-acc-1']);
    }

    /**
     * Test errors in request
     *
     * @return void
     *
     */
    public function testClientValidationErrors()
    {
        $this->createMockData();

        // Negative amount
        $response = $this->call('POST', '/transfer', [
            'from'   => 'test-acc-1',
            'to'     => 'test-acc-2',
            'amount' => -10,
        ]);

        $result = json_decode($response->getContent(), true);
        $this->assertEquals(400, $result['status']);

        // Missing fields
        $response = $this->call('POST', '/transfer', [
            'from' => 'test-acc-1',
            'to'   => 'test-acc-2',
        ]);

        $result = json_decode($response->getContent(), true);
        $this->assertEquals(400, $result['status']);
    }

    /**
     * Test database validations
     *
     * @return void
     *
     */
    public function testClientErrors()
    {
        $this->createMockData();

        // Unknown Account numbers
        $response = $this->call('POST', '/transfer', [
            'from'   => 'test-acc-3',
            'to'     => 'test-acc-4',
            'amount' => 10,
        ]);

        // Insufficient balance
        $response = $this->call('POST', '/transfer', [
            'from'   => 'test-acc-2',
            'to'     => 'test-acc-1',
            'amount' => 500,
        ]);

        $result = json_decode($response->getContent(), true);
        $this->assertEquals(400, $result['status']);

    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     *
     */
    public function tearDown(): void
    {
        Artisan::call('migrate:reset');
        DB::connection('mysql')->table('migrations')->truncate();
        parent::tearDown();

    }
}