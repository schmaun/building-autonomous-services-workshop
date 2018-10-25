<?php
declare(strict_types=1);

use Common\MessageTypes;
use Common\Persistence\KeyValueStore;
use Common\Stream\Stream;
use Common\Web\HttpApi;
use Symfony\Component\Debug\Debug;

require __DIR__.'/../../vendor/autoload.php';

Debug::enable();

/*
 * This is a demo projector which consumes every message from the stream,
 * starting *at a given index*. It uses the `KeyValueStore` to remember the
 * index at which to start consuming when the consumer has to be restarted
 * (possibly after a crash). This effectively makes this process manager
 * consume every message *only once*. The usual reason for this is that a
 * process manager produces side-effects; it may send a request to some other
 * service, or produce new events, which shouldn't happen again during a
 * restart of the service.
 *
 * If you want to visually keep track of the stream, run:
 *
 *     make logs
 */

/*
 * Every process manager should store its current index under a unique key
 * in the `KeyValueStore`, otherwise it would inherit the "start at index"
 * value from another consumer.
 */
$startAtIndexKey = $startAtIndexKey = basename(__DIR__).'_start_at_index';

$startAtIndex = KeyValueStore::get($startAtIndexKey) ?: 0;
echo 'Start consuming at index: '.(string)$startAtIndex;

// start consuming at the given index, and keep consuming incoming messages
Stream::consume(
    function (string $messageType, $data) use ($startAtIndexKey) {
        if (!in_array(
            $messageType,
            [MessageTypes::SALES_ORDER_CREATED, MessageTypes::RESERVATION_ACCEPTED],
            true
        )) {
            return false;
        }

        if ($messageType === MessageTypes::SALES_ORDER_CREATED) {
            echo json_encode($data);

            $result = HttpApi::postFormData(
                'http://stock_web/makeStockReservation',
                [
                    'reservationId' => $data['salesOrderId'],
                    'productId' => $data['productId'],
                    'quantity' => $data['quantity'],
                ]
            );
            echo json_encode($result);
        }

        if ($messageType === MessageTypes::RESERVATION_ACCEPTED) {
            HttpApi::postFormData(
                'http://sales_web/deliverSalesOrder',
                [
                    'salesOrderId' => $data['reservationId'],
                ]
            );
        }

        // do something with the message, or decide to ignore it based on its type
        echo $messageType.': '.json_encode($data)."\n";

        /*
         * After processing the message successfully, we need to increase the
         * "start at index value". If an exception occurs, the process manager
         * will die, and when restarted will try to process the same message
         * again.
         */

        KeyValueStore::incr($startAtIndexKey);
    },
    $startAtIndex
);
