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
        $bookingId = $params['account']['book_id'];


        if (empty($params['account'])) {
            $response = [
                'error' => [
                    'code' => -31050,
                    'message' => "Недостаточно привилегий для выполнения метода"
                ]
            ];
            return $response;
        } else {
            $booking = Booking::query()->find($bookingId);
            if (empty($booking)) {
                $response = [
                    'error' => [
                        'code' => -31050,
                        'message' => [
                            "uz" => "Buyurtma topilmadi",
                            "ru" => "Заказ не найден",
                            "en" => "Book not found"
                        ]
                    ]
                ];
                return $response;
            } else if ($booking->price * 100 != $amount) {
                $response = [
                    'error' => [
                        'code' => -31001,
                        'message' => [
                            "uz" => "Notogri summa",
                            "ru" => "Неверная сумма",
                            "en" => "Incorrect amount"
                        ]
                    ]
                ];
                return $response;
            }
        }
        $response = [
            'result' => [
                'allow' => true,
            ]
        ];
        return $response;
    }

    public function createTransaction($params): array
    {
        $amount = $params['amount'];
        $bookingId = $params['account']['book_id'];


        if (empty($params['account'])) {
            $response = [
                'error' => [
                    'code' => -32504,
                    'message' => "Недостаточно привилегий для выполнения метода"
                ]
            ];
            return $response;
        } else {
            $booking = Booking::query()->find($bookingId);
            $transaction = Transaction::where('book_id', $bookingId)->where('state', 1)->get();

            if (empty($booking)) {
                $response = [
                    'error' => [
                        'code' => -31050,
                        'message' => [
                            "uz" => "Buyurtma topilmadi",
                            "ru" => "Заказ не найден",
                            "en" => "Book not found"
                        ]
                    ]
                ];
                return $response;
            } else if ($booking->price * 100 != $amount) {
                $response = [
                    'error' => [
                        'code' => -31001,
                        'message' => [
                            "uz" => "Notogri summa",
                            "ru" => "Неверная сумма",
                            "en" => "Incorrect amount"
                        ]
                    ]
                ];
                return $response;
            } elseif (count($transaction) == 0) {

                $transaction = new Transaction();
                $transaction->paycom_transaction_id = $params['id'];
                $transaction->paycom_time = strval($params['time']);
                $transaction->paycom_time_datetime = now();
                $transaction->amount = $amount;
                $transaction->state = 1;
                $transaction->book_id = $bookingId;
                $transaction->save();

                return [
                    "result" => [
                        'create_time' => $params['time'],
                        'transaction' => strval($transaction->id),
                        'state' => $transaction->state
                    ]
                ];
            } elseif ((count($transaction) == 1) and ($transaction->first()->paycom_time == $params['time']) and ($transaction->first()->paycom_transaction_id == $params['id'])) {
                $response = [
                    'result' => [
                        "create_time" => $params['time'],
                        "transaction" => "{$transaction[0]->id}",
                        "state" => intval($transaction[0]->state)
                    ]
                ];

                return $response;
            } else {
                $response = [
                    'error' => [
                        'code' => -31099,
                        'message' => [
                            "uz" => "Buyurtma tolovi hozirda amalga oshrilmoqda",
                            "ru" => "Оплата заказа в данный момент обрабатывается",
                            "en" => "Book payment is currently being processed"
                        ]
                    ]
                ];
                return $response;
            }
        }
    }

    public function performTransaction($param): array
    {
        $transactionId = $param['id'];
        $transaction = Transaction::query()->where('paycom_transaction_id', $transactionId)->first();
        $ldate = date('Y-m-d H:i:s');
        if (empty($transaction)) {
            $response = [
                'error' => [
                    'code' => -31003,
                    'message' => "Транзакция не найдена "
                ]
            ];
            return $response;
        } else if ($transaction->state == 1) {
            $currentMillis = intval(microtime(true) * 1000);
            $transaction = Transaction::where('paycom_transaction_id', $transactionId)->first();
            $transaction->state = 2;
            $transaction->perform_time = $ldate;
            $transaction->perform_time_unix = str_replace('.', '', $currentMillis);
            $transaction->update();
            $completed_book = Booking::where('id', $transaction->book_id)->first();
            $completed_book->status = 'paid';
            $completed_book->update();
            $response = [
                'result' => [
                    'transaction' => "{$transaction->id}",
                    'perform_time' => intval($transaction->perform_time_unix),
                    'state' => intval($transaction->state)
                ]
            ];
            return $response;
        } else if ($transaction->state == 2) {
            $response = [
                'result' => [
                    'transaction' => strval($transaction->id),
                    'perform_time' => intval($transaction->perform_time_unix),
                    'state' => intval($transaction->state)
                ]
            ];
            return $response;
        }
    }

    public function cancelTransaction($params): array
    {
        $ldate = date('Y-m-d H:i:s');
        $transaction = Transaction::where('paycom_transaction_id', $params['id'])->first();
        if (empty($transaction)) {
            $response = [
                'error' => [
                    "code" => -31003,
                    "message" => "Транзакция не найдена"
                ]
            ];
            return $response;
        } else if ($transaction->state == 1) {
            $currentMillis = intval(microtime(true) * 1000);
            $transaction->reason = $params['reason'];
            $transaction->cancel_time = str_replace('.', '', $currentMillis);
            $transaction->state = -1;
            $transaction->update();

            $booking = Booking::query()->find($transaction->book_id);
            $booking->status = 'canceled';
            $response = [
                'result' => [
                    "state" => intval($transaction->state),
                    "cancel_time" => intval($transaction->cancel_time),
                    "transaction" => strval($transaction->id)
                ]
            ];
            return $response;
        } else if ($transaction->state == 2) {
            $currentMillis = intval(microtime(true) * 1000);
            $transaction->reason = $params['reason'];
            $transaction->cancel_time = str_replace('.', '', $currentMillis);
            $transaction->state = -2;
            $transaction->update();

            $booking = Booking::query()->find($transaction->book_id);
            $booking->status = 'canceled';
            $response = [
                'result' => [
                    "state" => intval($transaction->state),
                    "cancel_time" => intval($transaction->cancel_time),
                    "transaction" => strval($transaction->id)
                ]
            ];
            return $response;
        } elseif (($transaction->state == -1) or ($transaction->state == -2)) {
            $response = [
                'result' => [
                    "state" => intval($transaction->state),
                    "cancel_time" => intval($transaction->cancel_time),
                    "transaction" => strval($transaction->id)
                ]
            ];

            return $response;
        }
    }

    public function checkTransaction($params): array
    {
        $transaction = Transaction::where('paycom_transaction_id', $params['id'])->first();

        $ldate = date('Y-m-d H:i:s');

        if (empty($transaction)) {
            $response = [
                'error' => [
                    'code' => -31003,
                    'message' => "Транзакция не найдена."
                ]
            ];
            return $response;
        } else if ($transaction->state == 1) {
            $response = [
                "result" => [
                    'create_time' => intval($transaction->paycom_time),
                    'perform_time' => intval($transaction->perform_time_unix),
                    'cancel_time' => 0,
                    'transaction' => strval($transaction->id),
                    "state" => (int)$transaction->state,
                    "reason" =>  (int)$transaction->reason == 0 ? null : (int)$transaction->reason
                ]
            ];
            return $response;
        } else if ($transaction->state == 2) {
            $response = [
                "result" => [
                    'create_time' => intval($transaction->paycom_time),
                    'perform_time' => intval($transaction->perform_time_unix),
                    'cancel_time' => 0,
                    'transaction' => strval($transaction->id),
                    "state" => (int)$transaction->state,
                    "reason" => (int)$transaction->reason == 0 ? null : (int)$transaction->reason
                ]
            ];
            return $response;
        } else if ($transaction->state == -1) {
            $response = [
                "result" => [
                    'create_time' => intval($transaction->paycom_time),
                    'perform_time' => intval($transaction->perform_time_unix),
                    'cancel_time' => intval($transaction->cancel_time),
                    'transaction' => strval($transaction->id),
                    "state" => (int)$transaction->state,
                    "reason" => (int)$transaction->reason == 0 ? null : (int)$transaction->reason
                ]
            ];
            return $response;
        } else if ($transaction->state == -2) {
            $response = [
                "result" => [
                    'create_time' => intval($transaction->paycom_time),
                    'perform_time' => intval($transaction->perform_time_unix),
                    'cancel_time' => intval($transaction->cancel_time),
                    'transaction' => strval($transaction->id),
                    "state" => (int)$transaction->state,
                    "reason" => (int)$transaction->reason == 0 ? null : (int)$transaction->reason
                ]
            ];
            return $response;
        }
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
