<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use MasyukAI\Jnt\Enums\CancellationReason;
use MasyukAI\Jnt\Events\OrderCancelledEvent;

test('OrderCancelledEvent → it exposes cancellation data', function () {
    $response = ['code' => 1, 'msg' => 'Order cancelled successfully'];

    $event = new OrderCancelledEvent(
        orderId: 'ORDER123',
        reason: CancellationReason::OUT_OF_STOCK,
        response: $response,
        trackingNumber: 'TRACK456'
    );

    expect($event->getOrderId())->toBe('ORDER123')
        ->and($event->getReason())->toBe(CancellationReason::OUT_OF_STOCK)
        ->and($event->getReasonDescription())->toBe('Product is out of stock')
        ->and($event->getTrackingNumber())->toBe('TRACK456')
        ->and($event->hasTrackingNumber())->toBeTrue()
        ->and($event->getResponse())->toBe($response);
});

test('OrderCancelledEvent → it handles cancellation without tracking number', function () {
    $event = new OrderCancelledEvent(
        orderId: 'ORDER123',
        reason: CancellationReason::CUSTOMER_CHANGED_MIND,
        response: ['code' => 1],
    );

    expect($event->getTrackingNumber())->toBeNull()
        ->and($event->hasTrackingNumber())->toBeFalse();
});

test('OrderCancelledEvent → it detects successful cancellation', function () {
    $event = new OrderCancelledEvent(
        orderId: 'ORDER123',
        reason: CancellationReason::OUT_OF_STOCK,
        response: ['code' => 1, 'msg' => 'Success'],
    );

    expect($event->wasSuccessful())->toBeTrue()
        ->and($event->getMessage())->toBe('Success');
});

test('OrderCancelledEvent → it detects failed cancellation', function () {
    $event = new OrderCancelledEvent(
        orderId: 'ORDER123',
        reason: CancellationReason::OUT_OF_STOCK,
        response: ['code' => 0, 'msg' => 'Failed'],
    );

    expect($event->wasSuccessful())->toBeFalse()
        ->and($event->getMessage())->toBe('Failed');
});

test('OrderCancelledEvent → it handles response without message', function () {
    $event = new OrderCancelledEvent(
        orderId: 'ORDER123',
        reason: CancellationReason::OUT_OF_STOCK,
        response: ['code' => 1],
    );

    expect($event->getMessage())->toBe('Order cancelled');
});

test('OrderCancelledEvent → it can be dispatched', function () {
    Event::fake();

    $event = new OrderCancelledEvent(
        orderId: 'ORDER123',
        reason: CancellationReason::OUT_OF_STOCK,
        response: ['code' => 1]
    );

    OrderCancelledEvent::dispatch(
        $event->orderId,
        $event->reason,
        $event->response
    );

    Event::assertDispatched(OrderCancelledEvent::class);
});
