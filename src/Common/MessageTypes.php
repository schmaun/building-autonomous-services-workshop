<?php
declare(strict_types=1);

namespace Common;

final class MessageTypes
{
    public const PRODUCT_CREATED = 'product_created';
    public const PURCHASE_ORDER_RECEIVED = 'purchase_order_received';
    public const SALES_ORDER_DELIVERED = 'sales_order_delivered';
    public const SALES_ORDER_CREATED = 'sales_order_created';
    public const STOCK_INCREASED = 'stock_increased';
    public const STOCK_DECREASED = 'stock_decreased';
    public const RESERVE_FOR_SALE = 'reserve_for_sale';
    public const RESERVATION_ACCEPTED = 'reservation_accepted';
}
