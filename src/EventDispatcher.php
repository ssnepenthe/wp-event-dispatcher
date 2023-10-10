<?php

namespace WpEventDispatcher;

final class EventDispatcher implements EventDispatcherInterface
{
    public function addListener(string $event, callable $listener, int $priority = Priority::DEFAULT): void
    {
        add_filter($event, $listener, $priority, 999);
    }

    public function removeListener(string $event, callable $listener, int $priority = Priority::DEFAULT): void
    {
        remove_filter($event, $listener, $priority);
    }

    public function addSubscriber(SubscriberInterface $subscriber): void
    {
        foreach ($subscriber->getSubscribedEvents() as $tag => $args) {
            if (is_string($args)) {
                $this->addListener($tag, [$subscriber, $args]);
            } elseif (is_string($args[0])) {
                $this->addListener($tag, [$subscriber, $args[0]], $args[1] ?? Priority::DEFAULT);
            } else {
                foreach ($args as $subArgs) {
                    $this->addListener($tag, [$subscriber, $subArgs[0]], $subArgs[1] ?? Priority::DEFAULT);
                }
            }
        }
    }

    public function removeSubscriber(SubscriberInterface $subscriber): void
    {
        foreach ($subscriber->getSubscribedEvents() as $tag => $args) {
            if (is_string($args)) {
                $this->removeListener($tag, [$subscriber, $args]);
            } elseif (is_string($args[0])) {
                $this->removeListener($tag, [$subscriber, $args[0]], $args[1] ?? Priority::DEFAULT);
            } else {
                foreach ($args as $subArgs) {
                    $this->removeListener($tag, [$subscriber, $subArgs[0]], $subArgs[1] ?? Priority::DEFAULT);
                }
            }
        }
    }

    public function addSubscribers(array $subscribers): void
    {
        foreach ($subscribers as $subscriber) {
            $this->addSubscriber($subscriber);
        }
    }

    public function removeSubscribers(array $subscribers): void
    {
        foreach ($subscribers as $subscriber) {
            $this->removeSubscriber($subscriber);
        }
    }

    public function dispatch(object $event): void
    {
        $name = $event instanceof NamedEventInterface ? $event->getName() : get_class($event);

        do_action($name, $event);
    }
}
