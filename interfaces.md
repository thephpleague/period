---
layout: default
title: Interfaces
permalink: interfaces/
---

# Interfaces

<p class="message-notice">Added to <code>Period</code> in version 2.5</p>

To enable a wider use of the package, the `League\Period\Period` class implements two interfaces.

Interfaces were split to provide better flexibility when typehinting against them. One developper may only need the `League\Period\TimeRangeInterface` while another may choose to implement the `League\Period\PeriodInterface` interface.

## TimeRangeInterface

The `League\Period\TimeRangeInterface` defines all basic methods to get information from a time range without modifying it. This includes methods to get the TimeRange endpoints and duration as well as methods to perform time range based comparisons.

## PeriodInterface

The `League\Period\PeriodInterface` extends `League\Period\TimeRangeInterface` by providing methods that modify a Time Range object. These methods will return a modify `League\Period\PeriodInterface` with updated endpoints.

