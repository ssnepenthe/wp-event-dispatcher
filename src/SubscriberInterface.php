<?php

namespace WpEventDispatcher;

interface SubscriberInterface
{
    public function getSubscribedEvents(): array;
}
