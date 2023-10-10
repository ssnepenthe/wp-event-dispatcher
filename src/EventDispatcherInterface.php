<?php

namespace WpEventDispatcher;

// @todo Revisit return types - in particular consider a bool return on remove methods.
interface EventDispatcherInterface
{
    public function addListener(string $event, callable $listener, int $priority = Priority::DEFAULT): void;
    public function addSubscriber(SubscriberInterface $subscriber): void;
    public function addSubscribers(array $subscribers): void;
    public function dispatch(object $event): void;
    public function removeListener(string $event, callable $listener, int $priority = Priority::DEFAULT): void;
    public function removeSubscriber(SubscriberInterface $subscriber): void;
    public function removeSubscribers(array $subscribers): void;
}
