---
layout: default
title: the Period object as an immutable value object
---

# Modifying Period objects

You can manipulate a `Period` object according to their datepoints or durations.

`Period` **is an immutable value object** which means that any change returns a new `Period` object.

<p class="message-warning">If no <code>Period</code> object can be created the modifying methods throw a <code>Period\Exception</code> exception.</p>

## Using datepoints

### Period::startingOn

#### Description

~~~php
<?php

public Period::startingOn(mixed $startDate): Period
~~~

Returns a new `Period` object with `$startDate` as the new **starting included datepoint**.

#### Example

~~~php
<?php

use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->startingOn('2014-02-01');
$period->getStartDate(); //returns DateTimeImmutable('2014-03-01');
$newPeriod->getStartDate(); //returns DateTimeImmutable('2014-02-01');
// $period->getEndDate() equals $newPeriod->getEndDate();
~~~

### Period::endingOn

#### Description

~~~php
<?php

public Period::endingOn(mixed $endDate): Period
~~~

Returns a new `Period` object with `$endDate` as the new **ending excluded datepoint**.

#### Example

~~~php
<?php

use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->EndingOn('2014-03-16');
$period->getEndDate(); //returns DateTimeImmutable('2014-04-01');
$newPeriod->getEndDate(); //returns DateTimeImmutable('2014-03-16');
// $period->getStartDate() equals $newPeriod->getStartDate();
~~~

## Using durations

### Period::withDurationAfterStart

#### Description

~~~php
<?php

public Period::withDurationAfterStart(mixed $duration): Period
~~~

Returns a new `Period` object by updating its duration. Only the excluded ending datepoint is updated.

### Example

~~~php
<?php

use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->withDurationAfterStart('2 WEEKS');
$period->getEndDate();    //returns DateTimeImmutable('2014-04-01');
$newPeriod->getEndDate(); //returns DateTimeImmutable('2014-03-16');
// $period->getStartDate() equals $newPeriod->getStartDate();
~~~

### Period::withDurationBeforeEnd

#### Description

~~~php
<?php

public Period::withDurationBeforeEnd(mixed $duration): Period
~~~

Returns a new `Period` object by updating its duration. Only the includate starting datepoint is updated.

### Example

~~~php
<?php

use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->withDurationBeforeEnd('2 DAYS');
$period->getStartDate();    //returns DateTimeImmutable('2014-03-01');
$newPeriod->getStartDate(); //returns DateTimeImmutable('2014-03-30');
// $period->getEndDate() equals $newPeriod->getEndDate();
~~~

### Period::move

#### Description

~~~php
<?php

public Period::move(mixed $duration): Period
~~~

Returns a new `Period` object where the endpoints are moved forward or backward simultaneously by a given interval.

### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromMonth(2014, 3);
$newPeriod = $period->move('1 MONTH');
$period->getStartDate()     //returns DateTimeImmutable('2014-03-01');
$period->getEndDate();      //returns DateTimeImmutable('2014-04-01');
$newPeriod->getStartDate(); //returns DateTimeImmutable('2014-04-01');
$newPeriod->getEndDate();   //returns DateTimeImmutable('2014-05-01');
~~~

### Period::moveStartDate

#### Description

~~~php
<?php

public Period::moveStartDate(mixed $duration): Period
~~~

Returns a new `Period` object where the starting endpoint is moved forward or backward by a given interval.

### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromMonth(2014, 3);
$newPeriod = $period->moveStartDate('-1 MONTH');
$period->getStartDate()     //returns DateTimeImmutable('2014-03-01');
$period->getEndDate();      //returns DateTimeImmutable('2014-04-01');
$newPeriod->getStartDate(); //returns DateTimeImmutable('2014-02-01');
$newPeriod->getEndDate();   //returns DateTimeImmutable('2014-04-01');
~~~

### Period::moveEndDate

#### Description

~~~php
<?php

public Period::moveEndDate(mixed $duration): Period
~~~

Returns a new `Period` object where the ending endpoint is moved forward or backward by a given interval.

### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromMonth(2014, 3);
$newPeriod = $period->moveEndtDate('1 MONTH');
$period->getStartDate()     //returns DateTimeImmutable('2014-03-01');
$period->getEndDate();      //returns DateTimeImmutable('2014-04-01');
$newPeriod->getStartDate(); //returns DateTimeImmutable('2014-03-01');
$newPeriod->getEndDate();   //returns DateTimeImmutable('2014-05-01');
~~~

### Period::expand

#### Description

~~~php
<?php

public Period::expand(mixed $duration): Period
~~~

Returns a new `Period` object where the given interval is:

- substracted from the starting endpoint
- added to the ending endpoint

### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromMonth(2014, 3);
$newPeriod = $period->expand('1 MONTH');
$period->getStartDate()     //returns DateTimeImmutable('2014-03-01');
$period->getEndDate();      //returns DateTimeImmutable('2014-04-01');
$newPeriod->getStartDate(); //returns DateTimeImmutable('2014-02-01');
$newPeriod->getEndDate();   //returns DateTimeImmutable('2014-05-01');
~~~

*If you need to shrink the time range you can simply use a __inverted__ `DateInterval` object*

~~~php
<?php

use League\Period\Period;

$period = Period::createFromMonth(2014, 3);
$newPeriod = $period->expand('-1 DAY');
$period->getStartDate();     //returns DateTimeImmutable('2014-03-01');
$period->getEndDate();      //returns DateTimeImmutable('2014-04-01');
$newPeriod->getStartDate(); //returns DateTimeImmutable('2014-03-02');
$newPeriod->getEndDate();   //returns DateTimeImmutable('2014-03-31');
~~~

## Using another Period object

### Period::merge

#### Description

~~~php
<?php

public Period::merge(Period ...$period): Period
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
