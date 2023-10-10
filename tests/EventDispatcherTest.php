<?php

namespace WpEventDispatcher\Tests;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use WpEventDispatcher\EventDispatcher;
use WpEventDispatcher\NamedEventInterface;
use WpEventDispatcher\Priority;
use WpEventDispatcher\SubscriberInterface;

class EventDispatcherTest extends TestCase
{
    public function testAddListener()
    {
        $callback = function () {};

        Filters\expectAdded('some_hook')->with($callback, Priority::DEFAULT, 999);
        Filters\expectAdded('another_hook')->with($callback, 20, 999);

        $dispatcher = new EventDispatcher();

        $dispatcher->addListener('some_hook', $callback);
        $dispatcher->addListener('another_hook', $callback, 20);
    }

    public function testRemoveListener()
    {
        $callback = function () {};

        Filters\expectRemoved('some_hook')->with($callback, Priority::DEFAULT);
        Filters\expectRemoved('another_hook')->with($callback, 20);

        $dispatcher = new EventDispatcher();

        $dispatcher->removeListener('some_hook', $callback);
        $dispatcher->removeListener('another_hook', $callback, 20);
    }

    public function testAddSubscriber()
    {
        $subscriber = new TestSubscriber();

        Filters\expectAdded('one')->with([$subscriber, 'a'], Priority::DEFAULT, 999);
        Filters\expectAdded('two')->with([$subscriber, 'b'], 15, 999);
        Filters\expectAdded('three')->with([$subscriber, 'c'], Priority::DEFAULT, 999);
        Filters\expectAdded('three')->with([$subscriber, 'd'], 20, 999);

        $dispatcher = new EventDispatcher();

        $dispatcher->addSubscriber($subscriber);
    }

    public function testRemoveSubscriber()
    {
        $subscriber = new TestSubscriber();

        Filters\expectRemoved('one')->with([$subscriber, 'a'], Priority::DEFAULT);
        Filters\expectRemoved('two')->with([$subscriber, 'b'], 15);
        Filters\expectRemoved('three')->with([$subscriber, 'c'], Priority::DEFAULT);
        Filters\expectRemoved('three')->with([$subscriber, 'd'], 20);

        $dispatcher = new EventDispatcher();

        $dispatcher->removeSubscriber($subscriber);
    }

    public function testDispatch()
    {
        $event = new TestEvent();
        $namedEvent = new TestNamedEvent();

        Actions\expectDone(TestEvent::class)->with($event);
        Actions\expectDone(TestNamedEvent::$name)->with($namedEvent);

        $dispatcher = new EventDispatcher();

        $dispatcher->dispatch($event);
        $dispatcher->dispatch($namedEvent);
    }
}

class TestSubscriber implements SubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [
            'one' => 'a',
            'two' => ['b', 15],
            'three' => [
                ['c'],
                ['d', 20],
            ],
        ];
    }

    public function a() {}
    public function b() {}
    public function c() {}
    public function d() {}
}

class TestEvent
{
}

class TestNamedEvent implements NamedEventInterface
{
    public static string $name = 'test_named_event';

    public function getName(): string
    {
        return self::$name;
    }
}
