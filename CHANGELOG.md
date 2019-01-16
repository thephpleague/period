# Changelog

All Notable changes to `Period` will be documented in this file

## Next - TBD

### Added

- Added support for the boundary types on the package
    - `Period::EXCLUDE_START_INCLUDE_END`
    - `Period::INCLUDE_START_EXCLUDE_END`
    - `Period::EXCLUDE_ALL`
    - `Period::INCLUDE_ALL`
    - `Period::getBoundaryType`
    - `Period::isStartExcluded`
    - `Period::isStartIncluded`
    - `Period::isEndExcluded`
    - `Period::isEndIncluded`
    - `Period::withBoundaryType`
    - `Period::__construct` adds the `$boundaryType` argument;
    - `Period::after` adds the `$boundaryType` argument;
    - `Period::before` adds the `$boundaryType` argument;
    - `Period::around` adds the `$boundaryType` argument;
    - `Period::fromDatePeriod` adds the `$boundaryType` argument;
- Added missing [Allen's Algebra intervals](https://www.ics.uci.edu/~alspaugh/cls/shr/allen.html)
    - `Period::bordersOnStart`
    - `Period::bordersOnEnd`
    - `Period::isDuring`
    - `Period::startsBy`
    - `Period::endsBy`
- `Sequence::unions`
- `Sequence::intersections`
- `Sequence::gaps`
- `Sequence::boundaries`
- `Sequence::reduce`

### Fixed

- `Datepoint::createFromFormat` see issue [#72](https://github.com/thephpleague/period/issues/72)

### Deprecated

- `Sequence::getIntersections`
- `Sequence::getGaps`
- `Sequence::getBoundaries`

### Removed

- None

## 4.3.0 - 2018-12-21

### Added

- `Sequence` implements the `ArrayAccess` interface

### Fixed

- `Sequence::map` must preserve offset index after modification.

### Deprecated

- None

### Removed

- None

## 4.2.0 - 2018-12-19

### Added

- `League\Period\Datepoint`
- `League\Period\Duration`
- `Period::fromIsoYear`
- `Period::fromYear`
- `Period::fromSemester`
- `Period::fromQuarter`
- `Period::fromMonth`
- `Period::fromIsoWeek`
- `Period::fromDay`
- `Period::after`
- `Period::before`
- `Period::around`
- `Period::fromDatePeriod`
- `Sequence::map`

### Fixed

- None

### Deprecated

- `League\Period\datepoint`
- `League\Period\duration`
- `League\Period\year`
- `League\Period\semester`
- `League\Period\quarter`
- `League\Period\month`
- `League\Period\day`
- `League\Period\hour`
- `League\Period\minute`
- `League\Period\second`
- `League\Period\instant`
- `League\Period\iso_year`
- `League\Period\iso_week`
- `League\Period\interval_after`
- `League\Period\interval_before`
- `League\Period\interval_around`
- `League\Period\interval_from_dateperiod`

### Removed

- None

## 4.1.0 - 2018-12-07

### Added

- `League\Period\Sequence`
- `League\Period\InvalidIndex`

### Fixed

- None

### Deprecated

- None

### Removed

- None

## 4.0.1 - 2018-11-30

### Added

- None

### Fixed

- Period::split does not work with daylight saving see [issue #68](https://github.com/thephpleague/period/issues/68)

### Deprecated

- None

### Removed

- None

## 4.0.0 - 2018-10-18

### Added

#### Classes

- `Exception` class

#### Methods

- `Period::durationCompare`
- `Period::durationEquals`
- `Period::format`
- `Period::expand`
- `Period::equals`
- `Period::getDatePeriodBackwards`

#### Functions

- `League\Period\datepoint`
- `League\Period\duration`
- `League\Period\year`
- `League\Period\semester`
- `League\Period\quarter`
- `League\Period\month`
- `League\Period\day`
- `League\Period\hour`
- `League\Period\minute`
- `League\Period\second`
- `League\Period\instant`
- `League\Period\iso_year`
- `League\Period\iso_week`
- `League\Period\interval_after`
- `League\Period\interval_before`
- `League\Period\interval_around`
- `League\Period\interval_from_dateperiod`

### Fixed

- The `Period` class is now final
- `Period::JsonSerialize` now returns datepoint in JavaScript compatible notation
- `Period::diff` always returns an array containing two values.
- An instance with a duration equals to `DateInterval('PT0S')` will contains no datepoint not even its starting datepoint.

### Deprecated

- None

### Removed

- Support for PHP `7.0`
- `Period::next`
- `Period::previous`
- `Period::add`
- `Period::sub`
- `Period::createFromYear` replaced by `League\Period\year`
- `Period::createFromMonth` replaced by `League\Period\month`
- `Period::createFromWeek` replaced by `League\Period\iso_week`
- `Period::createFromDay` replaced by `League\Period\day`
- `Period::createFromSemester` replaced by `League\Period\semester`
- `Period::createFromQuarter` replaced by `League\Period\quarter`
- `Period::createFromDuration` replaced by `League\Period\interval_after`
- `Period::createFromDurationBeforeEnd` replaced by `League\Period\interval_before`
- `Period::sameValueAs` replaced by `Period::equals`
- `Period::sameDurationAs` replaced by `Period::durationEquals`
- `Period::compareDuration` replaced by `Period::durationCompare`
- `Period::withDuration` replaced by `Period::withDurationAfterStart`

## 3.4.0 - 2017-11-17

### Added

- `Period::withDurationBeforeEnd`
- `Period::splitBackwards`

### Fixed

- None

### Deprecated

- None

### Removed

- None

## 3.3.0 - 2016-09-19

### Added

- `Period::move`
- `Period::moveStartDate`
- `Period::moveEndDate`

### Fixed

- None

### Deprecated

- `Period::add` you should use `Period::moveEndDate` instead
- `Period::sub` you should use `Period::moveEndDate` instead

### Removed

- None

## 3.2.0 - 2016-05-09

### Added

- `Period::__set_state`
- autoloader to use the library without composer

### Fixed

- `Period::createFromDay` see [issue #36](https://github.com/thephpleague/period/issues/36)
- `Period::intersect` see [issue #33](https://github.com/thephpleague/period/issues/33)

### Deprecated

- None

### Removed

- None

## 3.1.1 - 2016-02-10

### Added

- None

### Fixed

- `Period::contains` see [issue #31](https://github.com/thephpleague/period/issues/31)
- microseconds are taken into account when converting `DateTimeInterface` objects.

### Deprecated

- None

### Removed

- None

## 3.1.0 - 2016-02-01

### Added

- `Period::createFromDay`

### Fixed

- `Period::getDatePeriod` adding the `$option` parameter
- `DateTime` to `DateTimeImmutable` convertion improved for PHP 5.6+ version
- Bumped version requirement to PHP 5.5.9 because of a bug in `DatePeriod` constructor

### Deprecated

- None

### Removed

- None

## 3.0.1 - 2015-12-21

### Added

- None

### Fixed

- `Period::contains` see [issue #28](https://github.com/thephpleague/period/pull/28)

### Deprecated

- None

### Removed

- None

## 3.0.0 - 2015-09-02

### Added

- `Period` implements the `JsonSerializable` interface
- `Period` can now be extended

### Fixed

- `Period` always returns `DateTimeImmutable` objects
- `Period::split` returns a `Generator`
- `Period::merge` no longer throws `RuntimeException`

### Deprecated

- None

### Removed

- support for PHP 5.4
- `Period::getStart`
- `Period::getEnd`
- `Period::getRange`
- `Period::duration`
- `Period::durationDiff`

## 2.5.1 - 2015-03-06

### Fixed

- `Period` always returns `DateTime` objects even when given `DateTimeImmutable` objects.

## 2.5.0 - 2015-03-06

### Added

- `Period::split` to split a `Period` object into smaller `Period` objects
- `Period::getDatePeriod`
- `Period::getDateInterval`
- `Period::getTimestampInterval`
- `Period::getStartDate`
- `Period::getEndDate`
- `Period::dateIntervalDiff`
- `Period::timestampIntervalDiff`

### Fixed
- `DateInterval` validation when created from an integer.

### Deprecated
- `Period::getStart` you should use `Period::getStartDate` instead
- `Period::getEnd` you should use `Period::getEndDate` instead
- `Period::getRange` you should use `Period::getDatePeriod` instead
- `Period::duration` you should use `Period::getDateInterval` or `Period::getTimestampInterval` instead
- `Period::durationDiff` you should use `Period::dateIntervalDiff` or `Period::timestampIntervalDiff` instead

### Remove
- support for PHP 5.3

## 2.4.0 - 2014-12-18

### Added
- Modifying methods:
    - `Period::diff`

### Fixed
- Added support for `DateTimeInterface` interface

## 2.3.0 - 2014-12-15

### Added
- Named constructor:
    - `Period::createFromDurationBeforeEnd`

### Fixed
- `Period::isBefore` `Period::isAfter` bug fixed. must take into account the *half-open* implementation of a `Period`object.

## 2.2.0 - 2014-12-12

### Added
- Modifying methods:
    - `Period::gap`
- Comparing methods:
    - `Period::abuts`
    - `Period::isAfter`
    - `Period::isBefore`

### Fixed
- `Period::overlaps` bug fixed [issue #8](https://github.com/thephpleague/period/issues/8)

## 2.1.0 - 2014-12-08

### Added
- Modifying methods:
    - `Period::next`
    - `Period::previous`
- `Period::__toString` using ISO8601 representation

## 2.0.0 - 2014-10-15

### Added
- Change vendor namespace from `Period` to `League\Period`
- Comparing methods:
    - `Period::sameValueAs`,
    - `Period::compareDuration`,
    - `Period::durationGreaterThan`,
    - `Period::durationLessThan`,
    - `Period::sameDurationAs`,
    - `Period::durationDiff`
- Modifying methods:
    - `Period::add`,
    - `Period::sub`,
    - `Period::intersect`

### Fixed
- `Period::contains` now works with `Period` objects
- `Period::getDuration` accept an optional parameter `$get_as_seconds` if used and set to `true`, the method will return a integer which represents the duration in seconds.
- `Period::merge` now accepts one or more `Period` objects to return the `Period` object which contains all submitted `Period` objects.

## 1.0.1 - 2014-10-08

### Fixed
- The `$interval` parameter can also an integer which represents the interval expressed in seconds.

## 1.0 - 2014-09-24

First stable release of `Period`
