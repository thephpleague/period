#Changelog
All Notable changes to `League\Period` will be documented in this file

## 2.0.0 - YYYY-MM-DD

### Added
- Change vendor namespace from `Period` to `League\Period`
- Named constructor: `Period::createFromTrimester`
- Comparing methods: 
    - `Period::sameValueAs`,
    - `Period::compareDuration`,
    - `Period::durationGreaterThan`,
    - `Period::durationLessThan`,
    - `Period::sameDurationAs`,
    - `Period::diff`
- Modifying methods:
    - `Period::add`,
    - `Period::sub`,
    - `Period::intersect`

### Deprecated
- Nothing

### Fixed
- `Period::contains` now works with `Period` objects
- `Period::getDuration` accept an optional parameter `$get_as_seconds` if used and set to `true`, the method will return a integer which represents the duration in seconds.

### Remove
- Nothing

### Security
- Nothing

## 1.0.1 - 2014-10-08

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- The `$interval` parameter can also an integer which represents the interval expressed in seconds.

### Remove
- Nothing

### Security
- Nothing

## 1.0 - 2014-09-24

First stable release of `Period`
