<?php

namespace App\Modules\Transactions\Controllers;

use App\Modules\Transactions\Request\TransferRequest;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

/**
 * Class TransferController
 *
 * @package App\Modules\Transactions\Controllers
 * @author  Jaai Chandekar
 */
class TransferController extends Controller
{
    /** @var \Illuminate\Http\Request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Make transfer request
     *
     * @throws \Exception
     */
    public function transfer(): array
    {
        $request = new TransferRequest();

        $response = $request->load($this->request->all())
                            ->validate()
                            ->process();

        return $response;
    }
}