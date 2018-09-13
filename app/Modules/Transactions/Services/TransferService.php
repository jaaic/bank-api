<?php

namespace App\Modules\Transactions\Services;

use App\Core\Constants;
use App\Exceptions\BadRequestException;
use App\Exceptions\ServerException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

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
     * Generate reference numbers for sender and receiver
     *
     * @param string $date
     *
     * @return array
     */
    public function generateRefs(string $date = ''): array
    {
        $senderHashAttributes = [
            'from'      => $this->senderAccount,
            'to'        => $this->receiverAccount,
            'amount'    => $this->amount * (-1),
            'timestamp' => $date,
        ];

        $senderRef = md5(json_encode($senderHashAttributes));


        $receiverHashAttributes = [
            'from'      => $this->senderAccount,
            'to'        => $this->receiverAccount,
            'amount'    => $this->amount,
            'timestamp' => $date,
        ];

        $receiverRef = md5(json_encode($receiverHashAttributes));

        return [
            'sender'   => $senderRef,
            'receiver' => $receiverRef,
        ];

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
            $balance = DB::select(DB::raw("SELECT balance FROM balances WHERE account_nr = :accountNr FOR UPDATE "),
                ['accountNr' => $this->senderAccount]);

            if (!empty($balance)) {
                $senderBalance = $balance[0]->balance;
            }

            if (empty($balance) || empty($senderBalance)) {
                DB::rollBack(); // release lock

                return (new BadRequestException('Sender Account not found', 'Account not found'))->toArray();
            }

            // check sender balance
            if ($senderBalance < $this->amount) {
                DB::rollBack(); // release lock

                return (new BadRequestException('Amount cannot be transferred', 'Invalid Amount'))->toArray();
            }

            $newSenderBalance = round($senderBalance - $this->amount, 3);

        } catch (Exception $exception) {
            DB::rollBack(); // release lock

            return (new ServerException('Error reading sender balance ', 'DB error', $exception->getMessage()))->toArray();
        }

        // receiver process
        try {
            $balance = DB::select(DB::raw("SELECT balance FROM balances WHERE account_nr = :accountNr FOR UPDATE "),
                ['accountNr' => $this->receiverAccount]);

            if (!empty($balance)) {
                $receiverBalance = $balance[0]->balance;
            }

            if (empty($balance) || empty($receiverBalance)) {
                DB::rollBack(); // release lock

                return (new BadRequestException('Receiver Account not found', 'Account Not Found'))->toArray();
            }

            $newReceiverBalance = round($receiverBalance + $this->amount, 3);

        } catch (Exception $exception) {
            DB::rollBack(); // release lock

            return (new ServerException('Error reading receiver balance ', 'DB error', $exception->getMessage()))->toArray();
        }

        // update tables
        try {
            $date        = date(Constants::DATE_FORMAT);
            $references  = $this->generateRefs($date);
            $senderRef   = $references['sender'] ?? '';
            $receiverRef = $references['receiver'] ?? '';

            // check de dupe
            if ($this->isCached($senderRef)) {
                DB::rollBack();

                return (new BadRequestException('Please try again after sometime', 'Current transaction already in process ..'))->toArray();
            }

            // update balances
            DB::statement("UPDATE balances SET balance = :newBal, updated_at = :updatedDate WHERE account_nr = :accNr",
                [
                    'newBal'      => $newSenderBalance,
                    'updatedDate' => $date,
                    'accNr'       => $this->senderAccount,
                ]);


            DB::statement("UPDATE balances SET balance = :newBal, updated_at = :updatedDate
                                  WHERE account_nr = :accNr",
                [
                    'newBal'      => $newReceiverBalance,
                    'updatedDate' => $date,
                    'accNr'       => $this->receiverAccount,
                ]);

            // log transactions
            $senderDetail   = "Amount $this->amount paid to $this->receiverAccount";
            $receiverDetail = "Amount $this->amount received from $this->senderAccount";

            DB::insert("INSERT INTO transactions (reference, amount, account_nr, details, created_at, updated_at)
                                  VALUES (?, ?, ?, ?, ?, ?)", [$senderRef, $this->amount, $this->senderAccount, $senderDetail, $date, $date]);

            DB::insert("INSERT INTO transactions (reference, amount, account_nr, details, created_at, updated_at)
                                  VALUES (?, ?, ?, ?, ?, ?)", [$receiverRef, $this->amount, $this->receiverAccount, $receiverDetail, $date, $date]);

            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();

            return (new ServerException('Error updating db ', 'DB error', $exception->getMessage()))->toArray();
        }

        DB::statement('SET AUTOCOMMIT=1');

        return [
            'status'        => '200',
            'responseState' => 'success',
            'id'            => $senderRef,
            'from'          => ['id'      => $this->senderAccount,
                                'balance' => $newSenderBalance,],
            'to'            => ['id'      => $this->receiverAccount,
                                'balance' => $newReceiverBalance,],
            'transferred'   => $this->amount,
        ];

    }

    /**
     * Check if value exists in caches
     *
     * @param string $reference
     *
     * @return bool
     */
    public function isCached(string $reference): bool
    {
        try {
            $value = Cache::get($reference);

            if (empty($value)) {
                Cache::put($reference, 1, Constants::REDIS_CACHE_VALID_MINUTES);

                try {
                    $value = DB::select(DB::raw("SELECT reference FROM transactions WHERE reference = :ref"),
                        ['ref' => $reference]);
                } catch (Exception $exception) {
                    Log::error('Error getting reference from db -' . $exception->getMessage());

                }
            }
        } catch (Exception $exception) {
            Log::error('Error reading and writing to cache- ' . $exception->getMessage());
            $value = '';
        }

        return (empty($value) ? false : true);
    }
}