---
layout: default
title: Period cheat sheet
redirect_from: /api/
---

# Definitions

## Date and time references

- **datepoint** - A period consists of a continuous portion of time between two positions in time called datepoints. This library assumes that the starting datepoint is included into the period. Conversely, the ending datepoint is excluded from the specified period. The starting datepoint is always less than or equal to the ending datepoint. The datepoints are defined as `DateTimeImmutable` objects.

- **duration** - The continuous portion of time between datepoints is called the duration. This duration is defined as a `DateInterval` object. The duration cannot be negative.

## Arguments

Unless stated otherwise:

- Whenever a datepoint is expected you can provide:
    - a `DateTimeInterface` implementing object;
    - a string parsable by the `DateTime` constructor.

- Whenever a duration is expected you can provide:
    - a `DateInterval` object;
    - a string parsable by the `DateInterval::createFromDateString` method.
    - an integer interpreted as the interval expressed in seconds.
