---
layout: default
title: the Period object as an immutable value object
---

# Modifying Period objects

You can manipulate a `Period` object according to their datepoints or durations.

`Period` **is an immutable value object** which means that any change returns a new `Period` object.

<p class="message-warning">If no <code>Period</code> object can be created the modifying methods throw a <code>LogicException</code> exception.</p>

## Using datepoints

### Period::startingOn

#### Description

~~~php
<?php

public function startingOn(mixed $startDate): Period
~~~

Returns a new `Period` object with `$startDate` as the new **starting included datepoint**.

#### Example

~~~php
<?php

use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->startingOn('2014-02-01');
$period->getStartDate(); //returns DateTime('2014-03-01');
$newPeriod->getStartDate(); //returns DateTime('2014-02-01');
// $period->getEndDate() equals $newPeriod->getEndDate();
~~~

### Period::endingOn

#### Description

~~~php
<?php

public function endingOn(mixed $endDate): Period
~~~

Returns a new `Period` object with `$endDate` as the new **ending excluded datepoint**.

#### Example

~~~php
<?php

use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->EndingOn('2014-03-16');
$period->getEndDate(); //returns DateTime('2014-04-01');
$newPeriod->getEndDate(); //returns DateTime('2014-03-16');
// $period->getStartDate() equals $newPeriod->getStartDate();
~~~

## Using durations

### Period::withDuration

#### Description

~~~php
<?php

public function withDuration(mixed $duration): Period
~~~

Returns a new `Period` object by updating its duration. Only the excluded datepoint is updated.

### Example

~~~php
<?php

use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->withDuration('2 WEEKS');
$period->getEndDate(); //returns DateTime('2014-04-01');
$newPeriod->getEndDate(); //returns DateTime('2014-03-16');
// $period->getStartDate() equals $newPeriod->getStartDate();
~~~

### Period::add

#### Description

~~~php
<?php

public function add(mixed $duration): Period
~~~

Returns a new `Period` object by adding an interval to the current ending excluded datepoint.

### Example

~~~php
<?php

use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->add('2 WEEKS');
// $period->getStartDate() equals $newPeriod->getStartDate();
~~~

### Period::sub

#### Description

~~~php
<?php

public function sub(mixed $duration): Period
~~~

Returns a new `Period` object by substracting an interval to the current ending excluded datepoint.

#### Example

~~~php
<?php

use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->sub('2 WEEKS');
// $period->getStartDate() equals $newPeriod->getStartDate();
~~~

### Period::next

#### Description

~~~php
<?php

public function next(mixed $duration = null): Period
~~~

Returns a new `Period` object adjacent to the current `Period` and starting with its ending datepoint.

#### Parameter

If no interval is provided, the new `Period` object will be created using the current `Period` duration.

#### Example

~~~php
<?php

use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->next('1 MONTH');
// $period->getEndDate() equals $newPeriod->getStartDate();
~~~

<p class="message-warning">When no <code>$duration</code> is provided to the method the new <code>Period</code> duration may vary. See below for a concrete example</p>

~~~php
<?php

use League\Period\Period;

$january  = Period::createFromMonth(2012, 1); //January 2012
$february = $period->next();
$march    = $newPeriod->next();
$january->sameDurationAs($february); //return false;
$january->sameDurationAs($march); //return false;

echo $january;  // 2012-01-01T00:00:00+0100/2012-02-01T00:00:00+0100
echo $february; // 2012-02-01T00:00:00+0100/2012-03-01T00:00:00+0100
echo $march;    // 2012-03-01T00:00:00+0100/2012-03-30T00:00:00+0200

// $march does not represents the full month
// since the ending datepoint is excluded from the period!!
~~~

<p class="message-info">To remove any ambiguity, it is recommended to always provide a <code>$duration</code> when using <code>Period::next</code></p>

### Period::previous

#### Description

~~~php
<?php

public function previous(mixed $duration = null): Period
~~~

Complementary to `Period::next`, the created `Period` object is adjacent to the current `Period` **but** its ending datepoint is equal to the starting datepoint of the current object.

#### Example

~~~php
<?php

use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->previous('1 WEEK');
// $period->getEndDate() equals $newPeriod->Start();
$period->durationGreaterThan($newPeriod); //return true
~~~

The method must be used with the same arguments and warnings as `Period::next`.

`Period::next` and `Period::previous` methods allow to easily create adjacent Periods as shown in the graph below

![](/media/period-adjacents.png "$previous and $next are adjacent to the $period object")

~~~php
<?php

use League\Period\Period;

$current = Period::createFromMonth(2012, 1);
$prev    = $current->previous('1 MONTH');
$next    = $curent->next('1 MONTH');
~~~

## Using another Period object

### Period::merge

#### Description

~~~php
<?php

public function merge(Period ...$period): Period
~~~

Merges two or more `Period` objects by returning a new `Period` object which englobes all the submitted objects.

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromSemester(2012, 1);
$alt    = Period::createFromWeek(2013, 4);
$other  = Period::createFromDuration('2012-03-07 08:10:27', 86000*3);
$newPeriod = $period->merge($alt, $other);
// $newPeriod->getStartDate() equals $period->getStartDate();
// $newPeriod->getEndDate() equals $altPeriod->getEndDate();
~~~
