<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Period\Chart;

/**
 * An interface to draw a dataset of intervals.
 */
interface Chart
{
    /**
     * Visualizes one or more intervals provided via a Dataset object.
     */
    public function stroke(Data $dataset): void;
}
