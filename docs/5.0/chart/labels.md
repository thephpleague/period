---
layout: default
title: Adding labels to the generated charts
---

# Generating interval labels

By default you are required to provide a label for each item present in a `Dataset` object.
The package provides a `League\Period\Chart\LabelGenerator` interface that ease generating labels for stroking.

A `LabelGenerator` implementing class is needed for the `Dataset::fromItems` named constructor, to create a new instance from a collection of `Period` and/or `Sequence` objects;

<p class="message-notice">By default when using <code>Dataset::fromItems</code> if no <code>LabelGenerator</code> class is supplied the <code>LatinLetter</code> label generator will be used.</p>

The current package comes bundle with the following `LabelGenerator` implementing class:

## LatinLetter

Generates labels according the the latin alphabet.

~~~php
<?php

use League\Period\Chart\Dataset;
use League\Period\Chart\GanttChart;
use League\Period\Chart\LatinLetter;
use League\Period\Period;
use League\Period\Sequence;

$dataset = Dataset::fromItems(
    new Sequence(
        Period::fromNotation('!Y-m-d', '[2018-01-01, 2018-02-01)'), 
        Period::fromNotation('!Y-m-d', '[2018-01-15, 2018-02-01)')
    ),
    new LatinLetter('aa')
);
(new GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 aa [-----------------------------------)
 ab [----------)
~~~

The `LatinLetter` also exposes the following methods:

~~~php
<?php

public function LatinLetter::startingAt(): string; //returns the first letter to be used
public function LatinLetter::startsWith(): self;  //returns a new object with a new starting letter
~~~

## DecimalNumber

Generates labels according to the decimal number system.

~~~php
<?php

use League\Period\Chart\Dataset;
use League\Period\Chart\DecimalNumber;
use League\Period\Chart\GanttChart;
use League\Period\Period;
use League\Period\Sequence;

$dataset = Dataset::fromItems(
    new Sequence(
        Period::fromNotation('!Y-m-d', '[2018-01-01, 2018-02-01)'), 
        Period::fromNotation('!Y-m-d', '[2018-01-15, 2018-02-01)')
    ),
    new DecimalNumber(42)
);
(new GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 42 [-----------------------------------)
 43 [----------)
~~~

The `DecimalNumber` also exposes the following methods:

~~~php
<?php

public function DecimalNumber::startingAt(): string; //returns the first decimal number to be used
public function DecimalNumber::startsWith(): self;  //returns a new object with a new starting decimal number
~~~

## RomanNumber

Uses the `DecimalNumber` label generator class to generate Roman number labels.

~~~php
<?php

use League\Period\Chart\Dataset;
use League\Period\Chart\DecimalNumber;
use League\Period\Chart\GanttChart;
use League\Period\Chart\RomanNumber;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new RomanNumber(new DecimalNumber(5), RomanNumber::LOWER);

$dataset = Dataset::fromItems(
    new Sequence(
        Period::fromNotation('!Y-m-d', '[2018-01-01, 2018-02-01)'),
        Period::fromNotation('!Y-m-d', '[2018-01-15, 2018-02-01)')
    ),
    $labelGenerator
);
(new GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 v  [-----------------------------------)
 vi [----------)
~~~

The `RomanNumber` also exposes the following methods:

~~~php
<?php
const RomanNumber::UPPER = 1;
const RomanNumber::LOWER = 2;
public function RomanNumber::startingAt(): string; //returns the first decimal number to be used
public function RomanNumber::startsWith(): self;  //returns a new object with a new starting decimal number
public function RomanNumber::withLetterCase(int $lettercase): self;  //returns a new object with a new letter casing
public function RomanNumber::isUpper(): bool;  //Tells whether the roman letter is upper cased.
public function RomanNumber::isLower(): bool;  //Tells whether the roman letter is lower cased.
~~~

## AffixLabel

Uses any `labelGenerator` implementing class to add prefix and/or suffix string to the generated labels.

~~~php
<?php

use League\Period\Chart\AffixLabel;
use League\Period\Chart\Dataset;
use League\Period\Chart\DecimalNumber;
use League\Period\Chart\GanttChart;
use League\Period\Chart\RomanNumber;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new AffixLabel(
    new RomanNumber(new DecimalNumber(5), RomanNumber::LOWER),
    '*', //prefix
    '.)'    //suffix
);
$dataset = Dataset::fromItems(
    new Sequence(
        Period::fromNotation('!Y-m-d', '[2018-01-01, 2018-02-01)'),
        Period::fromNotation('!Y-m-d', '[2018-01-15, 2018-02-01)')
    ),
    $labelGenerator
);
(new GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 * v .)  [-----------------------------------)
 * vi .) [----------)
~~~

The `AffixLabel` also exposes the following methods:

~~~php
<?php

public function AffixLabel::prefix(): string; //returns the current prefix
public function AffixLabel::suffix(): string;  //returns the current suffix
public function AffixLabel::withPrefix(string $prefix): self;  //returns a new object with a new prefix
public function AffixLabel::withSuffix(string $suffix): self;  //returns a new object with a new suffix
~~~

## ReverseLabel

Uses any `labelGenerator` implementing class to reverse the generated labels order.

~~~php
<?php

use League\Period\Chart\AffixLabel;
use League\Period\Chart\Dataset;
use League\Period\Chart\DecimalNumber;
use League\Period\Chart\GanttChart;
use League\Period\Chart\ReverseLabel;
use League\Period\Chart\RomanNumber;
use League\Period\Period;
use League\Period\Sequence;

$labelGenerator = new DecimalNumber(5);
$labelGenerator = new RomanNumber($labelGenerator, RomanNumber::LOWER);
$labelGenerator = new AffixLabel($labelGenerator, '', '.');
$labelGenerator = new ReverseLabel($labelGenerator);

$dataset = Dataset::fromItems(
    new Sequence(
        Period::fromNotation('!Y-m-d', '[2018-01-01, 2018-02-01)'),
        Period::fromNotation('!Y-m-d', '[2018-01-15, 2018-02-01)')
    ),
    $labelGenerator
);
(new GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 vi. [-----------------------------------)
 v.  [----------)
~~~

## Custom LabelGenerator

You can create your own label generator by implementing the `LabelGenerator` interface like shown below:

~~~php
<?php

use League\Period\Chart\AffixLabel;
use League\Period\Chart\Dataset;
use League\Period\Chart\GanttChart;
use League\Period\Chart\LabelGenerator;
use League\Period\Period;
use League\Period\Sequence;

$samelabel = new class implements LabelGenerator {
    public function generate(int $nbLabels): array
    {
        return array_fill(0, $nbLabels, $this->format('foobar'));
    }
        
    public function format($str): string
    {
        return (string) $str;
    }
};

$labelGenerator = new AffixLabel($samelabel, '', '.');
$dataset = Dataset::fromItems(
    new Sequence(
        Period::fromNotation('!Y-m-d', '[2018-01-01, 2018-02-01)'),
        Period::fromNotation('!Y-m-d', '[2018-01-15, 2018-02-01)')
    ),
    $labelGenerator
);
(new GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 foobar. [-----------------------------------)
 foobar. [----------)
~~~
