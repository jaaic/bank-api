<?php

namespace Tests\ApiTests;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/********************************************
 * Ensure below commands have been run only once
 *
 * 1.  php artisan migrate
 *
 * 2. php artisan db:seed
 *
 *********************************************/
class TransferApiTest extends TestCase
{
    /**
     * Test transfer endpoint and the results
     *
     * @return void
     */
    public function testTransferSuccess()
    {
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
     * Test clicking transfer twice within secs accidentally
     *
     * @return void
     */
    public function testDedupe()
    {
        $this->call('POST', '/transfer', [
            'from'   => 'test-acc-1',
            'to'     => 'test-acc-2',
            'amount' => 10,
        ]);

        $response2 = $this->call('POST', '/transfer', [
            'from'   => 'test-acc-1',
            'to'     => 'test-acc-2',
            'amount' => 10,
        ]);

        $result2 = json_decode($response2->getContent(), true);
        $this->assertEquals(400, $result2['status']);
        $this->assertEquals('Current transaction already in process ..', $result2['title']);
        $this->assertEquals('Please try again after sometime', $result2['detail']);
    }

    /**
     * Clean up to run after each test
     *
     * @return void
     */
    public function tearDown(): void
    {
        // clear redis cache
        Artisan::call('cache:clear');
        parent::tearDown();
    }
}