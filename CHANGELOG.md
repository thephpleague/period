# Changelog

All Notable changes to `Period` will be documented in this file

## Next - TBD

### Added

- `Exception` class
- `Period::createFromDurationAfterStart`
- `Period::withDurationAfterStart`
- `Period::expand`
- `Period::equals`
- `Period::createFromDatePeriod`

#### Helper functions

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
- `League\Period\duration`
- `League\Period\datepoint`

### Fixed

- The `Period` class is now final
- The following named constructors now accept as its sole argument a `DateTimeInterface` object.
    - `Period::createFromYear`
    - `Period::createFromMonth`
    - `Period::createFromDay`
    - `Period::createFromSemester`
    - `Period::createFromQuarter`
- `Period::JsonSerialize` now returns datepoint in JavaScript compatible datetime notation
- `Period::diff` always returns an array containing two values.

### Deprecated

- `Period::createFromYear` use instead `League\Period\year`
- `Period::createFromMonth` use instead `League\Period\month`
- `Period::createFromDay` use instead `League\Period\day`
- `Period::createFromSemester` use instead `League\Period\semester`
- `Period::createFromQuarter` use instead `League\Period\quarter`
- `Period::createFromDurationAfterStart` use instead `League\Period\interval_after`
- `Period::createFromDurationBeforeEnd` use instead `League\Period\interval_before`

### Removed

- `Period::next`
- `Period::previous`
- `Period::add`
- `Period::sub`
- `Period::sameValueAs` replaced by `Period::equals`
- `Period::createFromDuration` replaced by `League\Period\interval_after`
- `Period::withDuration` replaced by `Period::withDurationAfterStart`
- `Period::createFromWeek` replaced by `League\Period\iso_week`
- Support for PHP 7.0 and PHP 7.1

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
