<?php
namespace Example\Order\Command;

use Metabor\Statemachine\Command;
use Example\Order\Order;
use ArrayAccess;

class Authorize extends Command
{
    /**
     */
    public function __invoke(mixed ...$args): void
    {
        /** @var Order $order */
        /** @var ArrayAccess $context */
        [$order, $context] = $args;
        if ($order->getNumber() != 'PREPAYMENT 2') {
            $context['authorize result'] = 'successful';
        } else {
            $context['authorize result'] = 'failed';
        }
        echo 'Command "Authorize" was executed. Result: '.$context['authorize result'].PHP_EOL;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'Authorize';
    }
}
