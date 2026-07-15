<?php

namespace Example\Order;

use Example\Order\Process\Postpayment;
use Example\Order\Process\Prepayment;
use PHPUnit\Framework\TestCase;

/**
 * Fährt die Beispiel-Prozesse einmal komplett durch — dieselben Wege wie index.php.
 *
 * Warum es diesen Test gibt: phpunit.xml.dist zeigt seit jeher auf tests/Example/,
 * aber das Verzeichnis war leer. "php vendor/bin/phpunit" meldete "Test directory
 * not found" (Exit 2), Travis lief nicht mehr — also konnte nichts melden, dass das
 * Beispiel nach dem Umstieg auf statemachine v3.0.0 mit einem Fatal Error starb.
 * Die Signaturen von Command::__invoke und ConditionInterface::checkCondition hatten
 * sich geaendert; die Beispielklassen waren von 2015.
 *
 * Der Test prueft deshalb genau das, was drei Monate lang niemand geprueft hat:
 * dass die Klassen ueberhaupt laden und die Automaten durchlaufen.
 */
class OrderTest extends TestCase
{
    private function bestellung(string $nummer, string $prozess): Order
    {
        return new Order(
            $nummer,
            $prozess === ProcessConstants::PROCESS_PREPAYMENT ? new Prepayment() : new Postpayment()
        );
    }

    public function testNeueBestellungIstNeu(): void
    {
        $order = $this->bestellung('PREPAYMENT 1', ProcessConstants::PROCESS_PREPAYMENT);

        $this->assertSame(StateConstants::STATE_NEW, $order->getCurrentStateName());
        $this->assertTrue($order->hasEvent(EventConstants::EVENT_AUTHORIZE));
        $this->assertFalse($order->hasEvent(EventConstants::EVENT_SHIPPING));
    }

    /**
     * Der Weg, den index.php fuer PREPAYMENT 1 nimmt.
     */
    public function testVorkasseLaeuftBisZurRuecksendung(): void
    {
        $order = $this->bestellung('PREPAYMENT 1', ProcessConstants::PROCESS_PREPAYMENT);

        $order->triggerEvent(EventConstants::EVENT_AUTHORIZE);
        $this->assertSame(StateConstants::STATE_PAYMENT_PENDING, $order->getCurrentStateName());

        $order->triggerEvent(EventConstants::EVENT_PAID);
        $this->assertSame(StateConstants::STATE_SHIPPABLE, $order->getCurrentStateName());

        $order->triggerEvent(EventConstants::EVENT_SHIPPING);
        $this->assertSame(StateConstants::STATE_SHIPPED, $order->getCurrentStateName());

        $order->triggerEvent(EventConstants::EVENT_RETURNED);
        $this->assertSame(StateConstants::STATE_RETURNED, $order->getCurrentStateName());
    }

    /**
     * PREPAYMENT 2 ist im Beispiel absichtlich die Bestellung, die durchfaellt:
     * Der Command "Authorize" setzt fuer sie 'authorize result' auf 'failed'.
     * Damit greift die Condition AuthorizedSuccessful nicht.
     */
    public function testVorkasseScheitertAnDerAutorisierung(): void
    {
        $order = $this->bestellung('PREPAYMENT 2', ProcessConstants::PROCESS_PREPAYMENT);

        $order->triggerEvent(EventConstants::EVENT_AUTHORIZE);

        $this->assertSame(StateConstants::STATE_PAYMENT_FAILED, $order->getCurrentStateName());
        $this->assertFalse($order->hasEvent(EventConstants::EVENT_RETURNED));
    }

    public function testEreignisImFalschenZustandWirftEineAusnahme(): void
    {
        $order = $this->bestellung('PREPAYMENT 2', ProcessConstants::PROCESS_PREPAYMENT);
        $order->triggerEvent(EventConstants::EVENT_AUTHORIZE);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('doesn\'t have event');

        $order->triggerEvent(EventConstants::EVENT_RETURNED);
    }

    /**
     * Der Weg, den index.php fuer POSTPAYMENT 1 nimmt: erst liefern, dann zahlen.
     */
    public function testNachkasseLiefertVorDerZahlung(): void
    {
        $order = $this->bestellung('POSTPAYMENT 1', ProcessConstants::PROCESS_POSTPAYMENT);

        $this->assertSame(StateConstants::STATE_NEW, $order->getCurrentStateName());
        $this->assertTrue($order->hasEvent(EventConstants::EVENT_SHIPPING));

        $order->triggerEvent(EventConstants::EVENT_SHIPPING);
        $this->assertSame(StateConstants::STATE_SHIPPED, $order->getCurrentStateName());

        $order->triggerEvent(EventConstants::EVENT_RETURNED);
        $this->assertSame(StateConstants::STATE_RETURNED_AND_CLOSED, $order->getCurrentStateName());
    }

    /**
     * POSTPAYMENT 2 landet im Beispiel in der Mahnung — die Condition
     * ShippingDateGreater14Days greift genau fuer diese Bestellnummer.
     */
    public function testNachkasseLandetInDerMahnung(): void
    {
        $order = $this->bestellung('POSTPAYMENT 2', ProcessConstants::PROCESS_POSTPAYMENT);

        $order->triggerEvent(EventConstants::EVENT_SHIPPING);
        $this->assertSame(StateConstants::STATE_DUNNING, $order->getCurrentStateName());

        $order->triggerEvent(EventConstants::EVENT_RETURNED);
        $this->assertSame(StateConstants::STATE_RETURNED_AND_CLOSED, $order->getCurrentStateName());
    }
}
