---
layout: default
title: Adding labels to the generated charts
---

# Generating interval labels

By default, you are required to provide a label for each item present in a `Dataset` object.
The package provides a `League\Period\Chart\LabelGenerator` interface that ease generating labels for stroking.

A `LabelGenerator` implementing class is needed for the `Dataset::fromItems` named constructor, to create a new instance from a collection of `Period` and/or `Sequence` objects;

<p class="message-notice">By default when using <code>Dataset::fromItems</code> if no <code>LabelGenerator</code> class is supplied the <code>LatinLetter</code> label generator will be used.</p>

The current package comes bundle with the following `LabelGenerator` implementing class:

## LatinLetter

Generate labels according the latin alphabet.

~~~php
<?php

use League\Period\Chart;
use League\Period\Period;
use League\Period\Sequence;

$dataset = Chart\Dataset::fromItems(
    new Sequence(
        Period::fromIso80000('!Y-m-d', '[2018-01-01, 2018-02-01)'), 
        Period::fromIso80000('!Y-m-d', '[2018-01-15, 2018-02-01)')
    ),
    new Chart\LatinLetter('aa')
);
(new Chart\GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 aa [-----------------------------------)
 ab [----------)
~~~

The `LatinLetter` also exposes the following methods:

~~~php
<?php

public readonly string LatinLetter::startLabel;  //returns the first letter to be used
~~~

## DecimalNumber

Generate labels according to the decimal number system.

~~~php
<?php

use League\Period\Chart;
use League\Period\Period;
use League\Period\Sequence;

$dataset = Chart\Dataset::fromItems(
    new Sequence(
        Period::fromIso80000('!Y-m-d', '[2018-01-01, 2018-02-01)'), 
        Period::fromIso80000('!Y-m-d', '[2018-01-15, 2018-02-01)')
    ),
    new Chart\DecimalNumber(42)
);
(new Chart\GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 42 [-----------------------------------)
 43 [----------)
~~~

The `DecimalNumber` also exposes the following methods:

~~~php
<?php

public readonly string DecimalNumber::startLabel; //returns the first decimal number to be used
~~~

## RomanNumber

Uses the `DecimalNumber` label generator class to generate Roman number labels.

~~~php
<?php

use League\Period\Chart;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new Chart\RomanNumber(new Chart\DecimalNumber(5), Chart\LetterCase::Lower);

$dataset = Chart\Dataset::fromItems(
    new Sequence(
        Period::fromIso80000('!Y-m-d', '[2018-01-01, 2018-02-01)'),
        Period::fromIso80000('!Y-m-d', '[2018-01-15, 2018-02-01)')
    ),
    $labelGenerator
);
(new Chart\GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 v  [-----------------------------------)
 vi [----------)
~~~

The `RomanNumber` also exposes the following methods:

~~~php
<?php
use League\Period\Chart
public readonly DecimalNumber RomanNumber::decimalNumber; //returns the decimal number generator
public readonly LetterCase RomanNumber::letterCase; //returns the letter casing used
~~~

## AffixLabel

Uses any `labelGenerator` implementing class to add prefix and/or suffix string to the generated labels.

~~~php
<?php

use League\Period\Chart;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new Chart\AffixLabel(
    new Chart\RomanNumber(new Chart\DecimalNumber(5), Chart\LetterCase::Lower),
    '*', //prefix
    '.)'    //suffix
);
$dataset = Chart\Dataset::fromItems(
    new Sequence(
        Period::fromIso80000('!Y-m-d', '[2018-01-01, 2018-02-01)'),
        Period::fromIso80000('!Y-m-d', '[2018-01-15, 2018-02-01)')
    ),
    $labelGenerator
);
(new Chart\GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 * v .)  [-----------------------------------)
 * vi .) [----------)
~~~

The `AffixLabel` also exposes the following methods:

~~~php
<?php

public readonly LabelGenerator ReverseLabel::labelGenerator; //returns the decorated LabelGenerator
public readonly string AffixLabel::labelPrefix; //returns the current prefix
public readonly string AffixLabel::labelSuffix; //returns the current suffix
~~~

## ReverseLabel

Uses any `labelGenerator` implementing class to reverse the generated labels order.

~~~php
<?php

use League\Period\Chart;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new Chart\DecimalNumber(5);
$labelGenerator = new Chart\RomanNumber($labelGenerator, Chart\LetterCase::Lower);
$labelGenerator = new Chart\AffixLabel($labelGenerator, '', '.');
$labelGenerator = new Chart\ReverseLabel($labelGenerator);

$dataset = Chart\Dataset::fromItems(
    new Sequence(
        Period::fromIso80000('!Y-m-d', '[2018-01-01, 2018-02-01)'),
        Period::fromIso80000('!Y-m-d', '[2018-01-15, 2018-02-01)')
    ),
    $labelGenerator
);
(new Chart\GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 vi. [-----------------------------------)
 v.  [----------)
~~~

The `ReverseLabel` also exposes the following methods:

~~~php
<?php

public readonly LabelGenerator ReverseLabel::labelGenerator; //returns the decorated LabelGenerator
~~~

## Custom LabelGenerator

You can create your own label generator by implementing the `LabelGenerator` interface like shown below:

~~~php
<?php

use League\Period\Chart;
use League\Period\Period;
use League\Period\Sequence;

$sameLabel = new class implements Chart\LabelGenerator {
    public function generate(int $nbLabels): array
    {
        return array_fill(0, $nbLabels, $this->format('foobar'));
    }
        
    public function format($str): string
    {
        return (string) $str;
    }
};

$labelGenerator = new Chart\AffixLabel($sameLabel, '', '.');
$dataset = Chart\Dataset::fromItems(
    new Sequence(
        Period::fromIso80000('!Y-m-d', '[2018-01-01, 2018-02-01)'),
        Period::fromIso80000('!Y-m-d', '[2018-01-15, 2018-02-01)')
    ),
    $labelGenerator
);
(new Chart\GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 foobar. [-----------------------------------)
 foobar. [----------)
~~~
