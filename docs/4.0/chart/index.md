---
layout: default
title: Charting Periods and Sequences instances
---

# Visualizing Period and Sequence instances

To visualize multiple `Period` or `Sequence` instances you can use the provided charting feature. 

<p class="message-info">The feature is introduced in <code>version 4.10</code>.</p>

## Generating a simple graph.

To generate a graph you need to give to the `Dataset` constructor a list of pairs. Each pair is an `array` containing 2 values:

- the value at key `0` represents the label
- the value at key `1` is a `League\Period\Period` or a `League\Period\Sequence` object 

~~~php
<?php

use League\Period\Chart\Dataset;
use League\Period\Chart\GanttChart;
use League\Period\Period;
use League\Period\Sequence;

$dataset = new Dataset([
    ['period', new Period('2018-01-01', '2018-02-01')],
    ['sequence', new Sequence(
        new Period('2018-01-15', '2018-01-18'),
        new Period('2018-01-20', '2018-02-01')
    )],
]);
(new GanttChart())->stroke($dataset);
~~~

results:

~~~bash
 period   [----------------------------------------------------------)
 sequence                            [----)   [----------------------)
~~~

## Appending items to display

If you want to display a `Sequence` and some of its operations. You can append the operation results using the `Dataset::append` method.

~~~php
<?php

use League\Period\Chart\Dataset;
use League\Period\Chart\GanttChart;
use League\Period\Period;
use League\Period\Sequence;

$sequence = new Sequence(
    new Period('2018-01-01', '2018-03-01'),
    new Period('2018-05-01', '2018-08-01')
);
$dataset = new Dataset();
$dataset->append('A', $sequence[0]);
$dataset->append('B', $sequence[1]);
$dataset->append('GAPS', $sequence->gaps());
(new GanttChart())->stroke($dataset);
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
public function Dataset::appendAll(iterable $pairs): void; //adds multiple pairs at once.
public function Dataset::isEmpty(): bool; //Tells whether the collection is empty.
public function Dataset::labels(): string[]; //the current labels used
public function Dataset::items(): Sequence[]; //the current objects inside the Dataset
public function Dataset::boundaries(): ?Period;  //Returns the collection boundaries or null if it is empty.
public function Dataset::labelMaxLength(): int;  //Returns the label max length.
public function Dataset::withLabels(LabelGenerator $labelGenerator): self; //Update the labels used for the dataset.
~~~

## Displaying the Dataset

The `GanttChart` class is responsible for generating the graph from the `Dataset` by implementing the `Graph` interface for the console.

The `GanttChart::stroke` methods expects a `Dataset` object as its unique argument.

If you wish to present the graph on another medium like a web browser or an image, you will need to implement the interface for your implementation.

~~~php
<?php

use League\Period\Chart\Dataset;
use League\Period\Chart\GanttChart;
use League\Period\Period;

$graph = new GanttChart();
$graph->stroke(new Dataset([
    ['first', new Period('2018-01-01 08:00:00', '2018-01-01 12:00:00')],
    ['last', new Period('2018-01-01 10:00:00', '2018-01-01 14:00:00')],
]));
~~~

results:

~~~bash
 first [---------------------------)
 last            [------------------------------)
~~~

### Customized the graph looks

The `GanttChart` class can be customized by providing a `GanttChartConfig` which defines:

- the output medium via a `OutputWriter` implementing class.
- the graph settings. (How the intervals will be stroked)
    - sets the graph width
    - sets the graph colors
    - sets the gap between the labels and the rows
    - sets the label alignment
- the output settings (How the intervals will be created)
    - sets single characters to represent the boundary types
    - sets single characters to represent the body and space
     
You can easily create a `OutputWriter` implementing class with libraries like `League CLImate` or `Symfony Console` 
to output the resulting graph. If you don't, the package ships with a minimal `ConsoleOutput` class which is used
 if you do not provide you own implementation.

The `GanttChartConfig` class exposes the following additional constants and methods:

~~~php
<?php
const GanttChartConfig::ALIGN_LEFT = 1;
const GanttChartConfig::ALIGN_RIGHT = 0;
const GanttChartConfig::ALIGN_CENTER = 2;
public function GanttChartConfig::__construct(OutputWriter $output);
public function GanttChartConfig::output(): OutputWriter;  //Returns the OutputWriter instance.
public function GanttChartConfig::startExcluded(): string; //Retrieves the excluded start block character.
public function GanttChartConfig::startIncluded(): string; //Retrieves the included start block character.
public function GanttChartConfig::endExcluded(): string;   //Retrieves the excluded end block character.
public function GanttChartConfig::endIncluded(): string;   //Retrieves the included end block character.
public function GanttChartConfig::width(): int;            //Retrieves the max size width.
public function GanttChartConfig::body(): string;          //Retrieves the body block character.
public function GanttChartConfig::space(): string;         //Retrieves the space block character.
public function GanttChartConfig::colors(): string[];      //The selected colors for each row.
public function GanttChartConfig::gapSize(): int;          //Retrieves the gap sequence between the label and the line.
public function GanttChartConfig::labelAlign(): int;       //Returns how label should be aligned.
public function GanttChartConfig::leftMarginSize(): int;   //Retrieves the margin between the label and the console left side.
~~~

**`GanttChartConfig` is immutable, modifying its properties returns a new instance with the updated values.**

Here's a complex example which highlights most of the features introduces along visualizing `Period` and `Sequance` instances:

~~~php
<?php

use League\Period\Chart\AffixLabel;
use League\Period\Chart\ConsoleOutput;
use League\Period\Chart\Dataset;
use League\Period\Chart\DecimalNumber;
use League\Period\Chart\GanttChart;
use League\Period\Chart\GanttChartConfig;
use League\Period\Chart\ReverseLabel;
use League\Period\Chart\RomanNumber;
use League\Period\Datepoint;
use League\Period\Period;
use League\Period\Sequence;

$config = GanttChartConfig::createFromRainbow()
    ->withOutput(new ConsoleOutput(STDOUT))
    ->withStartExcluded('🍕')
    ->withStartIncluded('🍅')
    ->withEndExcluded('🎾')
    ->withEndIncluded('🍔')
    ->withWidth(30)
    ->withSpace('💩')
    ->withBody('😊')
    ->withGapSize(2)
    ->withLeftMarginSize(1)
    ->withLabelAlign(GanttChartConfig::ALIGN_RIGHT)
;

$labelGenerator = new DecimalNumber(42);
$labelGenerator = new RomanNumber($labelGenerator, RomanNumber::UPPER);
$labelGenerator = new AffixLabel($labelGenerator, '', '.');
$labelGenerator = new ReverseLabel($labelGenerator);

$sequence = new Sequence(
    Datepoint::create('2018-11-29')->getYear(Period::EXCLUDE_START_INCLUDE_END),
    Datepoint::create('2018-05-29')->getMonth()->expand('3 MONTH'),
    Datepoint::create('2017-01-13')->getQuarter(Period::EXCLUDE_ALL),
    Period::around('2016-06-01', '3 MONTHS', Period::INCLUDE_ALL)
);
$dataset = Dataset::fromItems($sequence, $labelGenerator);
$dataset->append($labelGenerator->format('gaps'), $sequence->gaps());
$graph = new GanttChart($config);
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
