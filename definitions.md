---
layout: default
title: Terminology
permalink: definitions/
---

# Terminology

## Definitions

- **endpoint** - A period consists of a continuous portion of time between two positions in time called endpoints. This library assumes that the starting endpoint is included into the period. Conversely, the ending endpoint is excluded from the specified period. The starting endpoint is always less than or equal to the ending endpoint. The endpoints are defined as DateTime objects.

- **duration** - The continuous portion of time between endpoints is called the duration. This duration is defined as a DateInterval object. The duration can not be negative.

~~~php
use League\Period\Period;

 //create a time range of 1 month for April 1983
$period = new Period(new DateTime('1983-04-01'), new DateTime('1983-05-01'));
$period->contains($period->getStart()); //returns true;
$period->contains($period->getEnd()); //returns false;
$period->getDuration(); //return the equivalent of DateInterval('1 MONTH');

~~~