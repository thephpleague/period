<?php
declare(strict_types=1);

namespace League\Period;

/**
 * Class PeriodCollection
 * @package League\Period
 * @abstract perform actions on a collection of Periods
 */
final class PeriodCollection
{

    private $periods;

    public function __construct(
        array $periods
    ) {
        foreach ($periods as $key => $potentialPeriod) {
            if (!$potentialPeriod instanceof Period) {
                throw new \InvalidArgumentException('Must receive a Period object. Received: ' . get_class($potentialPeriod));
            }
        }

        $this->periods = $periods;
    }

    /**
     * @abstract find the available gaps given a collection of periods
     * @return Period[]
     */
    public function invert(): array
    {
        $out = [];
        $sortedPeriods = $this->sort(true);

        $lastIndex = count($sortedPeriods) - 1;

        /**
         * @var Period $currentPeriod
         * @var Period $nextPeriod
         */
        foreach ($sortedPeriods as $key => $currentPeriod) {
            if ($key == $lastIndex) {
                break;
            }
            $next = $key + 1;
            $nextPeriod = $sortedPeriods[$next];

            // skip if the next period contains this one
            if ($currentPeriod->abuts($nextPeriod) || $currentPeriod->overlaps($nextPeriod)) {
                continue;
            }
            $out[] = $currentPeriod->gap($nextPeriod);
        }


        return $out;
    }

    public function sort(bool $consolidate = false): array
    {
        $out = $this->periods;

        usort($out, function (Period $periodA, Period $periodB) {
            if ($periodA->isBefore($periodB)) {
                return -1;
            }
            if ($periodA->isAfter($periodB)) {
                return 1;
            }

            return 0;
        });

        if ($consolidate === true) {
            $lastIndex = count($out) - 1;
            $updatedOut = [];
            /**
             * @var Period $currentPeriod
             * @var Period $nextPeriod
             */
            foreach ($out as $key => $currentPeriod) {

                if ($key == $lastIndex) {
                    $updatedOut[] = $currentPeriod;
                    break;
                }
                $next = $key + 1;
                $nextPeriod = $out[$next];

                // skip if the next period contains this one
                if ($currentPeriod->contains($nextPeriod)) {
                    //continue;
                }
                if ($nextPeriod->contains($currentPeriod)) {
                    continue;
                }
                $updatedOut[] = $currentPeriod;
            }
            $out = $updatedOut;
        }

        return $out;
    }

}