---
layout: default
title: Concepts and arguments
---

# Definitions

## Concepts

- **interval** - `Period` is a PHP implementation of a datetime interval which consists of:
	- two datepoints;
	- the duration between them;
	- a bound type. 


- **datepoint** - A position in time expressed as a `DateTimeImmutable` object. The starting datepoint is always less than or equal to the ending datepoint.
- **duration** - The continuous portion of time between two datepoints expressed as a `DateInterval` object. The duration cannot be negative.
- **bound type** - An included datepoint means that the boundary datepoint itself is included in the interval as well, while an excluded datepoint means that the boundary datepoint is not included in the interval.  
The package supports included and excluded datepoint, thus, the following bounds are supported:
	- included starting datepoint and excluded ending datepoint: `[start, end)`;
	- included starting datepoint and included ending datepoint : `[start, end]`;
	- excluded starting datepoint and included ending datepoint : `(start, end]`;
	- excluded starting datepoint and excluded ending datepoint : `(start, end)`;

<p class="message-warning">infinite or unbounded intervals are not supported.</p>

## Arguments

Since this package relies heavily on `DateTimeImmutable` and `DateInterval` objects and because it is sometimes complicated to get your hands on such objects the package comes bundled with:

- Two classes:
	- [League\Period\DatePoint](/5.0/datepoint/);
	- [League\Period\Duration](/5.0/duration/);
