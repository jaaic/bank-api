<?php

namespace App\Modules\Transactions\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Balance (Used in Api tests)
 *
 * @package App\Modules\Transactions\Models
 * @author  Jaai Chandekar
 */
class Balance extends Model
{
    /**
     * Table associated with the model
     *
     * @var string
     */
    protected $table = 'balances';
}