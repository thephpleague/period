---
layout: default
title: The Bounds enum
---

# The Bounds Enum

`Period` instances are bounded date endpoint interval. An included datepoint means that the boundary datepoint itself 
is included in the interval as well, while an excluded datepoint means that the boundary datepoint is not included 
in the interval. `Period` instances supports included and excluded datepoint, thus, the following Enum is provided:

```php
enum Bounds
{
    case INCLUDE_START_EXCLUDE_END;
    case INCLUDE_ALL;
    case EXCLUDE_START_INCLUDE_END;
    case EXCLUDE_ALL;
}
```

Apart from the regular methods exposed by PHP enums the `Period\Bounds`  exposes extra features.

## Instantiating Bounds from notation

Bounds string notation comes in two flavour. They can use the ISO format based on `[, ], (, )` usage or
use the Boubarki notation that only rely on `[, ]`. 

The `Bounds` Enum supports both notation when using the `fromNotation` named constructor.

#### Examples

~~~php
use League\Period\Bounds;

Bounds::EXCLUDE_ALL === Bounds::fromNotation('()'); // ISO notation
Bounds::EXCLUDE_ALL === Bounds::fromNotation(']['); // Boubarki notation
~~~

## Formatting interval from Bounds

~~~php
use League\Period\Bounds;

Bounds::toIso80000(string $interval): string;
Bounds::toBourbaki(string $interval): string;
~~~

On the opposite, the `Bounds` enum can format an interval by decorating the interval string representation.
You can specify which format you want to use, the ISO or the Boubarki one throught the dedicated method.

#### Examples

~~~php
use League\Period\Bounds;

Bounds::EXCLUDE_ALL->toBourbaki('foobar'); // returns ']foobar['
Bounds::EXCLUDE_ALL->toIso80000('foobar'); // returns '(foobar)'
~~~

<p class="message-notice">The formatting does not try to validate or sanitize its input format as long as it is a string.</p>

## Expose bounds inclusion in the interval

It will return the state of each bound if whether it is included or not.

~~~php
public Bounds::isStartIncluded(): bool
public Bounds::isEndIncluded(): bool
~~~

#### Examples

~~~php
use League\Period\Bounds;

Bounds::INCLUDE_ALL->isStartIncluded(); // return true;
Bounds::EXCLUDE_ALL->isStartIncluded(); // return false;
~~~

## Tells whether bounds share the same endpoints

It will returns `true` if both `Bounds` object share the same bound type.

~~~php
public Bounds::equalsStart(Bound $other): bool
public Bounds::equalsEnd(Bound $other): bool
~~~

#### Examples

~~~php
use League\Period\Bounds;

Bounds::INCLUDE_ALL->equalsStart(Bounds::INCLUDE_START_EXCLUDE_END); // return true;
Bounds::EXCLUDE_ALL->equalsStart(Bounds::INCLUDE_START_EXCLUDE_END); // return false;
Bounds::INCLUDE_ALL->equalsEnd(Bounds::INCLUDE_START_EXCLUDE_END); // return false;
Bounds::EXCLUDE_ALL->equalsEnd(Bounds::INCLUDE_START_EXCLUDE_END); // return true;
~~~

## Modify the enum

The `Bounds` enum also exposes features to modify and generate it.

~~~php
public Bounds::includeStart(): Bounds
public Bounds::includeEnd(): Bounds
public Bounds::excludeStart(): Bounds
public Bounds::excludeEnd(): Bounds
public Bounds::replaceStart(Bounds $other): Bounds
public Bounds::replaceEnd(Bounds $other): Bounds
~~~

#### Examples

~~~php
use League\Period\Bounds;

Bounds::INCLUDE_ALL->excludeStart() === Bounds::EXCLUDE_START_INCLUDE_END; // return true;
Bounds::EXCLUDE_ALL->includeStart() == Bounds::INCLUDE_START_EXCLUDE_END; // return true;
Bounds::EXCLUDE_ALL->replaceEnd(Bounds::INCLUDE_ALL) === Bounds::INCLUDE_START_EXCLUDE_END; // return true;
Bounds::EXCLUDE_ALL->replaceEnd(Bounds::EXCLUDE_ALL) === Bounds::EXCLUDE_ALL; // return true;
~~~
