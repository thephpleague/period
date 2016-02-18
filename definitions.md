---
layout: default
title: Terminology
---

# Terminology

## Definitions

- **datepoint** - A period consists of a continuous portion of time between two positions in time called datepoints. This library assumes that the starting datepoint is included into the period. Conversely, the ending datepoint is excluded from the specified period. The starting datepoint is always less than or equal to the ending datepoint. The datepoints are defined as `DateTimeImmutable` objects.

- **duration** - The continuous portion of time between datepoints is called the duration. This duration is defined as a `DateInterval` object. The duration cannot be negative.
