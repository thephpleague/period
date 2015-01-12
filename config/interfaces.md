---
layout: default
title: TimeRange Interfaces
---

# Interfaces

<p class="message-notice">Added to <code>Period</code> in version 2.5</p>

To enable a wider use of the package, the `League\Period\Period` class implements three (3) interfaces.

## TimeRange

This is the main interface. All others interfaces just extends it.

The `League\Period\Interfaces\TimeRange` defines a barebone time range. This interface includes methods to get the Time Range endpoints and duration. The interface methods are listed and completely covered in the [properties](/api/properties/) section.

~~~php
function calculReportFor(League\Period\Interfaces\TimeRange $timerange)
{
    //your business logic will come here
    //the timerange calculation is already taken care by $timerange
}
~~~

In this example the developer only cares about the report calculation and does not need more information from the time range object.

## TimeRangeInfo

The `League\Period\Interfaces\TimeRangeInfo` extends the `TimeRange` interface by providing methods to allow comparing objects implementing the `TimeRange` interface. These methods usually will return `true` or `false`.  The interface methods are listed and completely covered in the [comparing](/comparing/) section.

## TimeRangeObject

The `League\Period\Interfaces\TimeRangeObject` extends the `TimeRangeInfo` by providing methods that modify objects implementing the `TimeRange` interface. These methods will usually return a modify `TimeRangeObject`. The interface methods are listed and completely covered in the [modifying](/api/modifying/) section.

~~~php

namespace Agenda\Events;

use League\Period\Interfaces\TimeRangeObject;

abstract class AbstractEvent
{
    protected $event_id;

    protected $time_range;

    public function __construct($event_id, TimeRangeObject $time_range)
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

Now you can easily access the event timerange and manipulate it with ease you can find if it overlaps another event and calculate the intersected time range between them.

<p class="message-notice">The <code>League\Period\Period</code> implements the <code>TimeRangeObject</code> interface but having more interfaces enables you to implement your own classes if needed.</p>

