<?php

namespace App\Modules\Transactions\Request;

use App\Core\Base\Request;
use App\Core\Constants;
use App\Exceptions\BadRequestException;
use App\Exceptions\ServerException;
use App\Modules\Transactions\Response\ErrorResponse;
use App\Modules\Transactions\Response\TransferResponse;
use App\Modules\Transactions\Services\TransferService;
use Illuminate\Support\Facades\Log;

/**
 * Class TransferRequest
 *
 * @author Jaai Chandekar
 *
 * @property string from   Sender Account number
 * @property string to     Receiver Account number
 * @property double amount Amount to transfer
 */
class TransferRequest extends Request
{
    /**
     * Expected Attributes of the request payload
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'from',
            'to',
            'amount',
        ];

    }

    /**
     * Validation of the request payload
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'from'   => 'required|string',
            'to'     => 'required|string|different:from',
            'amount' => 'required|numeric|max:' . Constants::MAX_TRANSFER_LIMIT,
        ];
    }

    /**
     * Process request
     *
     * @return array
     */
    public function process(): array
    {
        if ($this->getAttribute('amount') <= 0) {
            $this->setErrors((new BadRequestException('Invalid amount'))->toArray());
        }

        // check validation errors
        if (!empty($this->getErrors())) {
            $errors = $this->getErrors();
            Log::error(json_encode($errors));

            return (new ErrorResponse($errors))->transform();
        }

        // call service
        $service = new TransferService($this->getAttributes());

        try {
            $response = $service->processTransfer();
        } catch (\Exception $exception) {
            $response = (new ServerException('Transaction failed please try again'))->toArray();
        }

        if (($response['responseState'] ?? Constants::ERROR_STATE) == Constants::SUCCESS_STATE) {
            $result = (new TransferResponse($response))->transform();
        } else {
            Log::error(json_encode($response));
            $result = (new ErrorResponse($response))->transform();
        }

        return $result;
    }
}