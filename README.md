# toy-wp-event-dispatcher

WIP

## Warning

This package is currently in development and is subject to breaking changes without notice until v1.0 has been tagged.

It is one in a series of [WordPress toys](https://github.com/ssnepenthe?tab=repositories&q=topic%3Atoy+topic%3Awordpress&type=&language=&sort=) I have been working on with the intention of exploring ways to modernize the feel of working with WordPress.

As the label suggests, it should be treated as a toy.

## Installation

WIP

## Overview

This package introduces the event dispatcher abstraction over WordPress hooks.

Event dispatchers (`WpEventDispatcher\EventDispatcherInterface`) encourage the encapsulation of events into dedicated event classes and event handlers into dedicated subscriber classes.

## Usage

The event dispatcher is intended to be used in place of direct calls to the various action and filter function provided by WordPress.

Event dispatchers do away with the idea of actions and filters altogether. Events are wrapped up in standalone event classes. If you want to "filter" a value, do so using typed properties or setters and getters on the event class. Event handlers can be standalone callables, but should preferably be wrapped in subscriber classes.

This is roughly modeled after the Symfony event dispatcher component, but uses WordPress hooks behind the scenes.

The primary methods you will use are `dispatch`, `addListener`, and `addSubscriber`.

By default `dispatch` uses the FQCN of the event class as the event name. If an event needs a dynamic name it should implement the `WpEventDispatcher\NamedEventInterface` interface.

Event dispatchers might require a bit extra boilerplate code, but provide a degree of type-safety and autocompletion you don't get with standard hooks.

Below is a simple example of how you might replace a WordPress action:

Before:

```php
add_action('my_plugin_initialized', function () {
    // ...
});

do_action('my_plugin_initialized');
```

After:

```php
$eventDispatcher = new EventDispatcher();
$eventDispatcher->addListener(MyPluginInitialized::class, function (MyPluginInitialized $event) {
    // ...
});

class MyPluginInitialized
{
}

$eventDispatcher->dispatch(new MyPluginInitialized());
```

And an example of how you might replace a filter:

Before:

```php
add_filter('my_plugin_filtered_value', function ($value) {
    if (is_string($value)) {
        $value = modifyValue($value);
    }

    return $value;
});

$default = 'some string';
$value = apply_filters('my_plugin_filtered_value', $default);

if (! is_string($value)) {
    $value = $default;
}
```

After:

```php
$eventDispatcher = new EventDispatcher();
$eventDispatcher->addListener(MyPluginFilteredValue::class, function (MyPluginFilteredValue $event) {
    $event->value = modifyValue($event->value);
});

class MyPluginFilteredValue
{
    public function __construct(public string $value)
    {
    }
}

$event = new MyPluginFilteredValue('some string');
$eventDispatcher->dispatch($event);
$value = $event->value;
```

Event dispatchers also allow for event handlers to be grouped logically within a subscriber class.

Subscribers should implement the `WpEventDispatcher\SubscriberInterface` interface.

This interface has a single method - `getSubscribedEvents`. It should return an array in any of the following formats:

Array keys are hook tag names, values are method names on this subscriber instance to use as handlers.

```php
return [
    'the_content' => 'onTheContent',
];
```

Array keys are hook tag names, values are arrays with method names at index 0 and optional priority at index 1.

```php
return [
    'the_content' => ['onTheContent', 20],
];
```

Array keys are hook tag names, values are arrays of array with method names at index 0 and optional priority at index 1.

```php
return [
    'the_content' => [
        ['onTheContent', 20],
        ['alsoOnTheContent'],
    ],
];
```

For (a very contrived) example:

```php
class PostContentSubscriber implements SubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [
            'the_content' => [
                ['appendSomething'],
                ['prependAnotherThing', 20],
            ],
        ];
    }

    public function appendSomething($content)
    {
        return $content . ' something';
    }

    public function prependAnotherThing($content)
    {
        return 'another thing ' . $content;
    }
}
```

Initialize the subscriber with the `addSubscriber` method on the event dispatcher:

```php
$eventDispatcher->addSubscriber(new PostContentSubscriber());
```
