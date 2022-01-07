---
layout: default
title: The Bounds enum
---

# The Bounds enum

`Period` instances are bounded date endpoint interval. An included datepoint means that the boundary datepoint itself 
is included in the interval as well, while an excluded datepoint means that the boundary datepoint is not included 
in the interval. `Period` instances supports included and excluded datepoint, thus, the following Enum is provided:

```php
enum Bounds: string
{
    case INCLUDE_START_EXCLUDE_END = '[)';
    case INCLUDE_ALL = '[]';
    case EXCLUDE_START_INCLUDE_END = '(]';
    case EXCLUDE_ALL = '()';
}
```

Apart from the regular methods exposed by PHP enums the `Period\Bounds`  exposes extra features.

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
public Bounds::replaceStartWith(Bounds $other): Bounds
public Bounds::replaceEndWith(Bounds $other): Bounds
~~~

#### Examples

~~~php
use League\Period\Bounds;

Bounds::INCLUDE_ALL->excludeStart() === Bounds::EXCLUDE_START_INCLUDE_END; // return true;
Bounds::EXCLUDE_ALL->includeStart() == Bounds::INCLUDE_START_EXCLUDE_END; // return true;
Bounds::EXCLUDE_ALL->replaceEndWith(Bounds::INCLUDE_ALL) === Bounds::INCLUDE_START_EXCLUDE_END; // return true;
Bounds::EXCLUDE_ALL->replaceEndWith(Bounds::EXCLUDE_ALL) === Bounds::EXCLUDE_ALL; // return true;
~~~
