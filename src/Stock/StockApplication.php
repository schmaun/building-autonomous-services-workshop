<?php
declare(strict_types=1);

namespace Stock;

use Common\Persistence\Database;
use Common\Render;

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
}
