<?php

/**
 * League.Period (https://period.thephpleague.com).
 *
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @license https://github.com/thephpleague/period/blob/master/LICENSE (MIT License)
 * @version 4.0.0
 * @link    https://github.com/thephpleague/period
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LeagueTest\Period;

use League\Period\Collection;
use League\Period\Sequence;

class SequenceTest extends CollectionTest
{
    protected function buildCollection(iterable $intervals = []): Collection
    {
        return new Sequence($intervals);
    }
}
