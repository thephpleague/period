---
layout: default
title: Interfaces
permalink: interfaces/
---

# Interfaces

<p class="message-notice">Added to <code>Period</code> in version 2.5</p>

To enable a wider use of the package, the `League\Period\Period` class implements three (3) interfaces.

## TimeRangeInterface

This is the main interface. All others interfaces just extends it.

The `League\Period\TimeRangeInterface` defines all basic methods to get information from a time range. This includes methods to get the Time Range endpoints and duration.

~~~php
function calculReportFor(League\Period\TimeRangeInterface $timerange)
{
    //your business logic will come here
    //the timerange calculation is already taken care by $timerange
}
~~~

In this example the developer only cares about the report calculation and does not need more information from the time range object.

## TimeRangeComparisonInterface

The `League\Period\TimeRangeComparisonInterface` extends `League\Period\TimeRangeInterface` by providing methods to allow comparing objects implementing the `League\Period\TimeRangeInterface`. These methods usually will return `true` or `false`.

## TimeRangeMutationInterface

The `League\Period\TimeRangeMutationInterface` extends `League\Period\TimeRangeInterface` by providing methods that modify objects implementing the `League\Period\TimeRangeInterface`. These methods will return a modify `League\Period\TimeRangeMutationInterface` with updated endpoints.

~~~php

namespace Agenda\Events;

use League\Period\TimeRangeMutationInterface;

abstract class AbstractEvent
{
    protected $event_id;

    protected $time_range;

    public function __construct($event_id, TimeRangeMutationInterface $time_range)
    {
        $this->event_id   = $event_id;
        $this->time_range = $time_range;
    }

    public function getTimeRange()
    {
        return $this->time_range;
    }

    public getEventId()
    {
        return $this->event_id;
    }
}
~~~

Now you can easily access the event timerange and manipulate it with ease you can find if it overlaps another event and calculate the intersected timerange between them.

<p class="message-notice">Both examples are covered by the <code>League\Period\Period</code> class but having more interfaces enables you to implement your own classes if needed.</p>

