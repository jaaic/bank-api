<?php

namespace App\Exceptions;

/**
 * Class ServerException
 *
 * @package App\Exceptions
 * @author  Jaai Chandekar
 */
class ServerException extends BaseException
{
    /** @var string */
    protected $status = '500';

    /** @var string */
    protected $title = 'Server Error';

    /** @var string */
    protected $detail = '';
}