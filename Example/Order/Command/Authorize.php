<?php
namespace Example\Order\Command;

use Metabor\Statemachine\Command;
use Example\Order\Order;
use ArrayAccess;
use InvalidArgumentException;

class Authorize extends Command
{
    /**
     */
    public function __invoke(mixed ...$args): void
    {
        [$order, $context] = $args + [null, null];

        if (!$order instanceof Order) {
            throw new InvalidArgumentException('Subject has to be an Order!');
        }
        if (!$context instanceof ArrayAccess) {
            throw new InvalidArgumentException('Context has to be an ArrayAccess!');
        }
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
