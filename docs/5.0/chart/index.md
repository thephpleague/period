---
layout: default
title: Drawing Periods and Sequences instances
---

# Drawing intervals

To improve visualizing multiple `Period` or `Sequence` instances you can use the provided charting feature. 

## Generating a simple graph.

To generate a graph you need to give to the `Dataset` constructor a list of pairs. Each pair is an `array` containing 2 values:

- the value at key `0` represents the label
- the value at key `1` is a `League\Period\Period` or a `League\Period\Sequence` object

~~~php
<?php

use League\Period\Chart;
use League\Period\Period;
use League\Period\Sequence;

$dataset = new Chart\Dataset([
    ['period', Period::fromIso80000('!Y-m-d', '[2018-01-01, 2018-02-01)')],
    ['sequence', new Sequence(
       Period::fromIso80000('!Y-m-d', '[2018-01-15, 2018-01-18)'),
       Period::fromIso80000('!Y-m-d', '[2018-01-20, 2018-02-01)')
    )],
]);
(new Chart\GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 period   [----------------------------------------------------------)
 sequence                            [----)   [----------------------)
~~~

## Appending items to display

If you want to display a `Sequence` and some of its operations. You can append the operation result using the `Dataset::append` method.

~~~php
<?php

use League\Period\Chart;
use League\Period\Period;
use League\Period\Sequence;

$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01, 2018-03-01)'),
    Period::fromIso80000('!Y-m-d', '[2018-05-01, 2018-08-01)')
);
$dataset = new Chart\Dataset();
$dataset->append('A', $sequence[0]);
$dataset->append('B', $sequence[1]);
$dataset->append('GAPS', $sequence->gaps());
(new Chart\GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 A    [-------------)                                                         
 B                               [----------------)
 GAPS               [------------)    
~~~

The `Dataset` implements the `Countable` and the `IteratorAggregate` interface. It also exposes the following methods:

~~~php
<?php
public function Dataset::fromItems($items, ?LabelGenerator $labelGenerator = null): self; //Creates a new Dataset from a collection of Sequence/Periods and a LabelGenerator.
public function Dataset::fromIterable(iterable $iterable): self; //Creates a new Dataset from a generic iterable structure of Sequence/Periods.
public function Dataset::append(string|int $label, Period|Sequence $item): self; //adds multiple pairs at once.
public function Dataset::appendAll(iterable $pairs): self; //adds multiple pairs at once.
public function Dataset::isEmpty(): bool; //Tells whether the collection is empty.
public function Dataset::labels(): array<string>; //the current labels used
public function Dataset::items(): array<Sequence>; //the current objects inside the Dataset
public function Dataset::length(): Period|null; //Returns the collection boundaries or null if it is empty.
public function Dataset::labelMaxLength(): int;  //Returns the label max length.
~~~

## Displaying the Dataset

The `GanttChart` class is responsible for generating the graph from the `Dataset` by implementing the `Graph` interface for the console.

The `GanttChart::stroke` methods expects a `Dataset` object as its unique argument.

If you wish to present the graph on another medium like a web browser or an image, you will need to implement the interface for your implementation.

~~~php
<?php

use League\Period\Chart;
use League\Period\Period;

$graph = new Chart\GanttChart();
$graph->stroke(new Chart\Dataset([
    ['first', Period::fromIso80000('Y-m-d H:i:s', '[2018-01-01 08:00:00, 2018-01-01 12:00:00)')],
    ['last', Period::fromIso80000('Y-m-d H:i:s', '2018-01-01 10:00:00, 2018-01-01 14:00:00)')],
]));
~~~

results:

~~~bash
 first [---------------------------)
 last            [------------------------------)
~~~

### Customizing the graph looks

The `GanttChart` class can be customized by providing a `GanttChartConfig` which defines:

- the output medium via an `Output` implementing class.
- the graph settings. (How the intervals will be stroked)
    - sets the graph width
    - sets the graph colors
    - sets the gap between the labels and the rows
    - sets the label alignment
- the output settings (How the intervals will be created)
    - sets single characters to represent the boundary types
    - sets single characters to represent the body and space
     
You can easily create a `Output` implementing class with libraries like `League CLImate` or `Symfony Console` 
to output the resulting graph. If you don't, the package ships with a minimal `StreamOutput` class which is used
 if you do not provide you own implementation.

The `GanttChartConfig` class exposes the following additional constants and methods:

~~~php
<?php
use League\Period\Chart;

public static function GanttChartConfig::fromStream(resource $stream = STDOUT, Chart\Terminal $terminal = Chart\Terminal::Posix);
public static function GanttChartConfig::fromOutput(Output $output = new Chart\StreamOutput(STDOUT, Chart\Terminal::Posix));
public static function GanttChartConfig::fromRandomColor(Output $output = new Chart\StreamOutput(STDOUT, Chart\Terminal::Posix)): self
public static function GanttChartConfig::fromRainbow(Output $output = new Chart\StreamOutput(STDOUT, Chart\Terminal::Posix)): self
public readonly Output GanttChartConfig::output;                 //Returns the Output instance.
public readonly string GanttChartConfig::startExcludedCharacter; //Returns the excluded start block character.
public readonly string GanttChartConfig::startIncludedCharacter; //Returns the included start block character.
public readonly string GanttChartConfig::endExcludedCharacter;   //Returns the excluded end block character.
public readonly string GanttChartConfig::endIncludedCharacter;   //Returns the included end block character.
public readonly int GanttChartConfig::width;                     //Returns the max size width.
public readonly string GanttChartConfig::body;                   //Returns the body block character.
public readonly string GanttChartConfig::space;                  //Returns the space block character.
public readonly array<Chart\Color> GanttChartConfig::colors;     //Returns the selected colors for each row.
public readonly int GanttChartConfig::gapSize;                   //Returns the gap sequence between the label and the line.
public readonly int GanttChartConfig::labelAlignment;            //Returns how label should be aligned.
public readonly int GanttChartConfig::leftMarginSize;            //Returns the margin between the label and the console left side.
~~~

**`GanttChartConfig` is immutable, modifying its properties returns a new instance with the updated values.**

Here's a complex example which highlights most of the features introduces along visualizing `Period` and `Sequance` instances:

~~~php
<?php

use League\Period\Bounds;
use League\Period\Chart;
use League\Period\DatePoint;
use League\Period\Duration;
use League\Period\Period;
use League\Period\Sequence;

$config = Chart\GanttChartConfig::fromStream(STDOUT, Chart\Terminal::Posix)
    ->colors(...Chart\Color::rainBow())
    ->startExcludedCharacter('🍕')
    ->startIncludedCharacter('🍅')
    ->endExcludedCharacter('🎾')
    ->endIncludedCharacter('🍔')
    ->bodyCharacter('😊')
    ->spaceCharacter('💩')
    ->width(30)
    ->gapSize(2)
    ->leftMarginSize(1)
    ->labelAlignment(Chart\Alignment::Right);

$labelGenerator = new Chart\DecimalNumber(42);
$labelGenerator = new Chart\RomanNumber($labelGenerator, Chart\LetterCase::Upper);
$labelGenerator = new Chart\AffixLabel($labelGenerator, '', '.');
$labelGenerator = new Chart\ReverseLabel($labelGenerator);

$sequence = new Sequence(
    DatePoint::fromDateString('2018-11-29')->year(Bounds::ExcludeStartIncludeEnd),
    DatePoint::fromDateString('2018-05-29')->month()->expand('3 MONTH'),
    DatePoint::fromDateString('2017-01-13')->quarter(Bounds::ExcludeAll),
    Period::around(new DateTime('2016-06-01'), Duration::fromDateString('3 MONTHS'), Bounds::IncludeAll)
);
$dataset = Chart\Dataset::fromItems($sequence, $labelGenerator);
$dataset->append($labelGenerator->format('gaps'), $sequence->gaps());
$graph = new Chart\GanttChart($config);
$graph->stroke($dataset);
~~~

which will output in your console:

~~~bash
   XLV.  💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩🍕😊😊😊😊😊😊😊😊😊🍔
  XLIV.  💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩🍅😊😊😊😊😊🎾💩💩💩
 XLIII.  💩💩💩💩💩💩💩💩🍕😊😊🎾💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩
  XLII.  🍅😊😊😊😊🍔💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩💩
  GAPS.  💩💩💩💩💩🍕😊😊🍔💩💩🍅😊😊😊😊😊😊😊🍔💩💩💩💩💩💩💩💩💩💩
~~~

*On a POSIX compliant console all lines have different colors*
