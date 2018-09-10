<?php

namespace App\Modules\Transactions\Services;

use App\Exceptions\BadRequestException;
use App\Exceptions\ServerException;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Class TransferService
 *
 * @package App\Modules\Transactions\Services
 * @author  Jaai Chandekar
 */
class TransferService
{
    /** @var string */
    private $senderAccount;

    /** @var  string */
    private $receiverAccount;

    /** @var float */
    private $amount;

    /**
     * TransferService constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->senderAccount   = $attributes['from'] ?? '';
        $this->receiverAccount = $attributes['to'] ?? '';
        $this->amount          = $attributes['amount'] ?? 0.0;

    }

    /**
     * Make transfer
     *
     * @return array
     */
    public function processTransfer(): array
    {
        DB::statement('SET AUTOCOMMIT=0');

        DB::beginTransaction();

        // sender process
        try {
            $senderBalance = 0;
            $balance       = DB::select("SELECT balance FROM balances WHERE account_nr = '$this->senderAccount' FOR UPDATE ");

            if (!empty($balance)) {
                $senderBalance = $balance[0]->balance;
            }

            if (empty($balance) || empty($senderBalance)) {
                DB::rollBack(); // release lock

                return (new BadRequestException('Sender Account not found'))->toArray();
            }

            // check sender balance
            if ($senderBalance < $this->amount) {
                DB::rollBack(); // release lock

                return (new BadRequestException('Amount cannot be transferred'))->toArray();
            }

            $newSenderBalance = round($senderBalance - $this->amount, 3);

        } catch (Exception $exception) {
            DB::rollBack(); // release lock

            return (new ServerException('Error reading sender balance ' . $exception->getMessage()))->toArray();
        }

        // receiver process
        try {
            $receiverBalance = 0;
            $balance         = DB::select(DB::raw("SELECT * FROM balances WHERE account_nr = '$this->receiverAccount' FOR UPDATE "));

            if (!empty($balance)) {
                DB::rollBack(); // release lock

                $receiverBalance = $balance[0]->balance;
            }

            if (empty($balance) || empty($receiverBalance)) {
                DB::rollBack(); // release lock

                return (new BadRequestException('Receiver Account not found'))->toArray();
            }

            $newReceiverBalance = round($receiverBalance + $this->amount, 3);

        } catch (Exception $exception) {
            DB::rollBack(); // release lock

            return (new ServerException('Error reading receiver balance ' . $exception->getMessage()))->toArray();
        }

        // update tables
        try {
            $date = date('Y-m-d H:i:s');

            // update balances
            DB::statement("UPDATE balances SET balance = '$newSenderBalance', updated_at = '$date' 
                                 WHERE account_nr = '$this->senderAccount' ");


            DB::statement("UPDATE balances SET balance = '$newReceiverBalance', updated_at = '$date'
                                  WHERE account_nr = '$this->receiverAccount' ");

            // log transactions
            $senderRef      = uniqid('REF-');
            $receiverRef    = uniqid('REF-');
            $senderDetail   = "Amount $this->amount paid to $this->receiverAccount";
            $receiverDetail = "Amount $this->amount received from $this->senderAccount";

            DB::statement("INSERT INTO transactions (reference, amount, account_nr, details, created_at, updated_at)
                                  VALUES ('$senderRef', '$this->amount', '$this->senderAccount', '$senderDetail', '$date', '$date'); ");

            DB::statement("INSERT INTO transactions (reference, amount, account_nr, details, created_at, updated_at)
                                  VALUES ('$receiverRef', '$this->amount', '$this->receiverAccount', '$receiverDetail', '$date', '$date') ");

            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();

            return (new ServerException('Error updating db ' . $exception->getMessage()))->toArray();
        }

        DB::statement('SET AUTOCOMMIT=1');

        return [
            'responseState' => 'success',
            'id'            => $senderRef,
            'from'          => ['id'      => $this->senderAccount,
                                'balance' => $newSenderBalance,],
            'to'            => ['id'      => $this->receiverAccount,
                                'balance' => $newReceiverBalance,],
            'transferred'   => $this->amount,
        ];

    }
}