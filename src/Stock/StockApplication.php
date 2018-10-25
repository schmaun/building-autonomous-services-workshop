<?php
declare(strict_types=1);

namespace Stock;

use Common\MessageTypes;
use Common\Persistence\Database;
use Common\Render;
use Common\Stream\Stream;

final class StockApplication
{
    public function stockLevelsController(): void
    {
        $stockLevels = $this->calculateStockLevels();

        Render::jsonOrHtml($stockLevels);
    }

    private function calculateStockLevels(): array
    {
        $stockLevels = [];

        /** @var Balance[] $balances */
        $balances = Database::retrieveAll(Balance::class);

        foreach ($balances as $balance) {
            $stockLevels[$balance->id()] = $balance->stockLevel();
        }

        return $stockLevels;
    }

    public function makeStockReservationController(): void
    {
        /** @var Balance $balance */
        $balance = Database::retrieve(Balance::class, $_POST['productId']);

        if ($balance->makeReservation($_POST['reservationId'], (int)$_POST['quantity'])) {
            Database::persist($balance);

            Stream::produce(
                MessageTypes::RESERVATION_ACCEPTED,
                [
                    'reservationId' => $_POST['reservationId'],
                    'productId' => $balance->id(),
                    'quantity' => (int)$_POST['quantity'],
                ]
            );
        } else {
            Stream::produce(
                MessageTypes::RESERVATION_REJECTED,
                [
                    'reservationId' => $_POST['reservationId'],
                    'productId' => $balance->id(),
                    'quantity' => (int)$_POST['quantity'],
                    'missingQuantity' => ($balance->stockLevel() - (int)$_POST['quantity']) * -1,
                ]
            );
        }
    }

    public function commitStockReservationController(): void
    {
        /** @var Balance $balance */
        $balance = Database::retrieve(Balance::class, $_POST['productId']);
        $balance->commitReservation($_POST['reservationId']);
        Database::persist($balance);

    }
}
