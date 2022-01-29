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

use Iterator;

/**
 * An interface to help generating label for the chart.
 */
interface LabelGenerator
{
    /**
     * Returns the labels to associate with all items.
     *
     * @return Iterator<int, string>
     */
    public function generate(int $nbLabels): Iterator;

    /**
     * Returns a formatted label according to the generator rules.
     */
    public function format(string $label): string;
}
