---
layout: homepage
---

# Features

~~~php
<?php

date_default_timezone_set('UTC');

use League\Period\Period;

$period = Period::createFromDuration('2014-10-03 08:12:37', 3600);
$start = $period->getStartDate();
$end   = $period->getEndDate();
$duration  = $period->getDateInterval();
$altPeriod = $period->endingOn('2014-12-03');
$period->contains($altPeriod); //return false;
$altPeriod->durationGreaterThan($period); //return true;

~~~