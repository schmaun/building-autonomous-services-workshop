<?php
declare(strict_types=1);

use Common\MessageTypes;
use Common\Persistence\Database;
use Common\Stream\Stream;
use Dashboard\Product;
use Symfony\Component\Debug\Debug;

require __DIR__ . '/../../vendor/autoload.php';

Debug::enable();

/*
 * This is a demo projector which consumes every message from the stream.
 * This effectively makes the projector consume every existing message again
 * after a restart (e.g. when running `make restart`).
 *
 * The projector doesn't update a local data store of some sorts; it just
 * prints the message to `stdout`. Hence, if you want to visually keep
 * track of the stream, run:
 *
 *     make logs
 */
Database::deleteAll(Product::class);

Stream::consume(function(string $messageType, $data) {
    /*
     * At this point you can look at the value of `$messageType` and decide
     * if you're going to process this message.
     */
    if ($messageType === MessageTypes::PRODUCT_CREATED) {
        $product = new Product($data['id'], $data['name']);
        Database::persist($product);
    }

    if ($messageType === MessageTypes::STOCK_INCREASED) {
        /** @var Product $product */
        $product = Database::retrieve(Product::class, $data['id']);
        $product->increase($data['quantity']);
        Database::persist($product);
    }

    if ($messageType === MessageTypes::STOCK_DECREASED) {
        /** @var Product $product */
        $product = Database::retrieve(Product::class, $data['id']);
        $product->decrease($data['quantity']);
        Database::persist($product);
    }

    /*
     * `$data` has already been decoded from JSON into an array, so
     * if we want to echo it, we need to encode it again:
     */
    echo $messageType . ': ' . json_encode($data) . "\n";
});
