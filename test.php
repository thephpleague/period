<?php
declare(strict_types=1);

use League\Period\Sequence;
use League\Period\Period;

require 'vendor/autoload.php';

$sequenceA = new Sequence(
    new Period('2000-01-01', '2000-01-10'),
    new Period('2000-01-12', '2000-01-20')
);
$sequenceB = new Sequence(
    new Period('2000-01-05', '2000-01-08'),
    new Period('2000-01-11', '2000-01-25')
);
$diff = $sequenceA->substract($sequenceB);
dump(
    count($diff),
    $diff[0]->format('Y-m-d'),
    $diff[1]->format('Y-m-d')
);

$sequenceA = new Sequence(
    new Period('2000-01-01', '2000-01-10'),
    new Period('2000-01-12', '2000-01-20')
);
$sequenceB = new Sequence();
$diff1 = $sequenceA->substract($sequenceB);
dump(
    count($diff1),
    $diff1->get(0)->format('Y-m-d'),
    $diff1->get(1)->format('Y-m-d'),
    count($sequenceB->substract($sequenceA))
);
