#Changelog

All Notable changes to `League\Period` will be documented in this file

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
