---
layout: default
title: Terminology
---

# Terminology

## Definitions

- **datepoint** - A period consists of a continuous portion of time between two positions in time called datepoints. This library assumes that the starting datepoint is included into the period. Conversely, the ending datepoint is excluded from the specified period. The starting datepoint is always less than or equal to the ending datepoint. The datepoints are defined as `DateTime` objects.

- **duration** - The continuous portion of time between datepoints is called the duration. This duration is defined as a `DateInterval` object. The duration cannot be negative.

## Arguments

Unless stated otherwise:

- Whenever a `DateTime` object is expected you can provide:
    - a `DateTimeInterface` object (ie: `DateTimeImmutable` since PHP 5.5+);
    - a `DateTime` object;
    - a string parsable by the `DateTime` constructor.

- Whenever a `DateInterval` object is expected you can provide:
    - a `DateInterval` object;
    - a string parsable by the `DateInterval::createFromDateString` method.
    - an integer interpreted as the interval expressed in seconds.