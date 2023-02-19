<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Period;

/*
 * @deprecated since version 5.2.1
 *
 * Presence Enum
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   5.0.0
 * @deprecated since version 5.2.0
 */
enum InitialDatePresence
{
    case Excluded;
    case Included;
}
