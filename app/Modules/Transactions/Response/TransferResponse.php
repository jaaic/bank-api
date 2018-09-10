<?php

namespace App\Modules\Transactions\Response;

use App\Core\Base\Response;

/**
 * Class TransferResponse
 *
 * @package App\Modules\Transactions\Response
 * @author  Jaai Chandekar
 */
class TransferResponse extends Response
{
    /**
     * Response attributes
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'id',
            'from',
            'to',
            'transferred',
        ];
    }
}