---
layout: default
title: The Bounds enum
---

# The Bounds Enumeration

`Period` instances are bounded date endpoint interval. An included datepoint means that the boundary datepoint itself 
is included in the interval as well, while an excluded datepoint means that the boundary datepoint is not included 
in the interval. `Period` instances supports included and excluded datepoint, thus, the following Enum is provided:

```php
enum Bounds
{
    case IncludeStartExcludeEnd;
    case IncludeAll;
    case ExcludeStartIncludeEnd;
    case ExcludeAll;
}
```

Apart from the regular methods exposed by PHP enums the `Period\Bounds`  exposes extra features.

## Parsing interval notation

Bounds string notation comes in two flavours. They can use the ISO format based on `[, ], (, )` usage or
use the Boubarki notation that only rely on `[, ]`. 

The `Bounds` Enumeration supports both notation through 2 parsing methods `parseIso80000` and  `parseBourbaki`.
The parsers return the same array representation of the interval with the following keys:

- `start`: the start or lower bound as a string
- `end`: the end or upper bound as a string
- `bounds`: the end or upper bound as a `Bounds` Enumeration

#### Examples

~~~php
use League\Period\Bounds;

Bounds::parseIso80000('(3, 5)'); 
// returns [ 'start' => '3', 'end' => '5', 'bounds' => Bounds::ExcludeAll]
Bounds::parseBourbaki(']2022-03-05,2022-03-09[');
// returns [ 'start' => '2022-03-05', 'end' => '2022-03-09', 'bounds' => Bounds::ExcludeAll]
~~~

## Formatting interval from Bounds

~~~php
use League\Period\Bounds;

Bounds::buildIso80000(string $lowerBound, string $upperBound): string;
Bounds::buildBourbaki(string $lowerBound, string $upperBound): string;
~~~

On the opposite, the `Bounds` enum can format an interval by decorating the interval string representation.
You can specify which format you want to use, the ISO or the Boubarki one through the dedicated method.

#### Examples

~~~php
use League\Period\Bounds;

Bounds::ExcludeAll->buildBourbaki('3', '4'); // returns ']3, 4['
Bounds::ExcludeAll->buildIso80000('3', '4'); // returns '(3, 4)'
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

Bounds::IncludeAll->isStartIncluded(); // return true;
Bounds::ExcludeAll->isStartIncluded(); // return false;
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

Bounds::IncludeAll->equalsStart(Bounds::IncludeStartExcludeEnd); // return true;
Bounds::ExcludeAll->equalsStart(Bounds::IncludeStartExcludeEnd); // return false;
Bounds::IncludeAll->equalsEnd(Bounds::IncludeStartExcludeEnd); // return false;
Bounds::ExcludeAll->equalsEnd(Bounds::IncludeStartExcludeEnd); // return true;
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

Bounds::IncludeAll->excludeStart() === Bounds::ExcludeStartIncludeEnd; // return true;
Bounds::ExcludeAll->includeStart() == Bounds::IncludeStartExcludeEnd; // return true;
Bounds::ExcludeAll->replaceEnd(Bounds::IncludeAll) === Bounds::IncludeStartExcludeEnd; // return true;
Bounds::ExcludeAll->replaceEnd(Bounds::ExcludeAll) === Bounds::ExcludeAll; // return true;
~~~
