<?php

namespace App\Services;

use App\Http\Resources\TransactionResource;
use App\Models\Booking;
use App\Models\Transaction;

class PaycomService
{

    public function checkPerformTransaction($params): array
    {
        $amount = $params['amount'];
        $bookingId = $params['account']['booking_id'];

        $booking = Booking::query()->find($bookingId);

        if (!$booking || $booking->price != $amount) {
            return [
                'error' => [
                    'code' => -31050,
                    'message' => 'Invalid amount or booking not found'
                ]
            ];
        }

        return ['allow' => true];
    }

    public function createTransaction($params): array
    {
        $amount = $params['amount'];
        $bookingId = $params['account']['booking_id'];

        $booking = Booking::query()->find($bookingId);

        if (!$booking || $booking->price != $amount) {
            return [
                'error' => [
                    'code' => -31050,
                    'message' => 'Invalid amount or booking not found'
                ]
            ];
        }

        try {
            $transaction = Transaction::create([
                'paycom_transaction_id' => $params['id'],
                'paycom_time' => $params['time'],
                'paycom_time_datetime' => now(),
                'amount' => $params['amount'],
                'state' => 1,
                'booking_id' => $params['account']['booking_id'],
            ]);

            return [
                'result' => [
                    'create_time' => $transaction->create_time,
                    'transaction' => $transaction->paycom_transaction_id,
                    'state' => $transaction->state
                ]
            ];
        } catch (\Exception $e) {
            return [
                'error' => [
                    'code' => -31008,
                    'message' => 'Unable to create transaction'
                ]
            ];
        }
    }

    public function performTransaction($param): array
    {
        $transactionId = $param['id'];
        $transaction = Transaction::query()->where('paycom_transaction_id', $transactionId)->first();
        if (empty($transaction)) {
            return [
                'error' => [
                    'code' => -31003,
                    'message' => 'Transaction not found'
                ]
            ];
        } else {
            $currentMil = intval(microtime(true) * 1000);
            $transaction->state = 2;
            $transaction->perform_time = date('Y-m-d H:i:s');
            $transaction->perform_time_unix = str_replace('.', '', $currentMil);
            $transaction->update();

            $booking = Booking::query()->find($transaction->book_id);
            $booking->status = 'paid';

            return [
                "result" => [
                    "transaction" => $transaction->id,
                    "perform_time" => intval($transaction->perform_time),
                    "state" => intval($transaction->state)
                ]
            ];
        }
    }

    public function cancelTransaction($params): array
    {
        $transaction = Transaction::where('paycom_transaction_id', $params['id'])->first();

        if (!$transaction) {
            return [
                'error' => [
                    'code' => -31003,
                    'message' => 'Transaction not found'
                ]
            ];
        }
        $currentMil = intval(microtime(true) * 1000);

        $transaction->update([
            'state' => -2,
            'cancel_time' => str_replace('.', '', $currentMil),
            'reason' => $params['reason'],
        ]);

        $booking = Booking::query()->find($transaction->book_id);
        $booking->status = 'canceled';

        return [
            'result' => [
                'transaction' => $transaction->paycom_transaction_id,
                'state' => $transaction->state,
                'cancel_time' => $transaction->cancel_time
            ]
        ];
    }

    public function checkTransaction($params): array
    {
        $transaction = Transaction::where('paycom_transaction_id', $params['id'])->first();

        if (!$transaction) {
            return [
                'error' => [
                    'code' => -31003,
                    'message' => 'Transaction not found'
                ]
            ];
        }

        return [
            'result' => [
                'create_time' => $transaction->create_time,
                'perform_time' => $transaction->perform_time,
                'cancel_time' => $transaction->cancel_time,
                'transaction' => $transaction->paycom_transaction_id,
                'state' => $transaction->state
            ]
        ];
    }

    public function getStatement($param): array
    {
        $from = $param['from'];
        $to = $param['to'];

        $transactions = Transaction::getByTimeRange($from, $to);

        return [
            "result" => [
                "transactions" => TransactionResource::collection($transactions)
            ]
        ];
    }
}
