# Change Log

All notable changes to this project will be documented in this file. This project adheres to
[Semantic Versioning](http://semver.org/) and [this changelog format](http://keepachangelog.com/).

## [1.0.0-alpha.2] - 2021-02-02

### Added

- [#1](https://github.com/laravel-json-api/laravel/pull/1)
  Resource classes are now optional. If one is not defined, the implementation falls-back to using the Eloquent schema
  to serialize a model. Eloquent schema fields now have new
  `hidden` and `serializeUsing` methods to customise the serialization of models by the schema.
- Resource classes now support using conditional attributes in their `meta()` method.
- New field classes `ArrayList` and `ArrayHash` have been added, to distinguish between PHP zero-indexed arrays that
  serialize to JSON arrays (`ArrayList`) and PHP associative arrays that serialize to JSON objects (`ArrayHash`). The
  distinction is required because an empty array list can be serialized to `[]` in JSON whereas an empty associative
  array must be serialized to `null` in JSON.

### Changed

- **BREAKING** The JsonApiResource method signatures for the `attributes()`, `relationships()`,
  `meta()`, and `links()` methods have been changed so that they receive the HTTP request as the first (and only)
  parameter. This brings the implementation in line with Laravel's Eloquent resources, which receive the request to
  their `toArray()` method. The slight difference is our implementation allows the request to be `null` - this is to
  cover encoding resources outside of HTTP requests, e.g. queued broadcasting. When upgrading, you will need to either
  delete resource classes (as they are now optional), or update the method signatures on any classes you are retaining.

### Fixed

- [#3](https://github.com/laravel-json-api/laravel/issues/3)
  Example server registration in the published configuration file prevented developer from creating a `v1` server after
  adding this package to their Laravel application.
- Package discovery for sub-packages that have service providers now works correctly.

### Removed

- **BREAKING** The `Arr` schema field has been removed - use the new `ArrayList` or `ArrayHash`
  fields instead.
- **BREAKING** The `uri` method on resource and relationship routes has been removed:
    - The resource type URI can now be set on the resource's schema (using the `$uriType` property).
    - Relationship URIs are now set on the schema field for the relationship (via the `withUriFieldName` method).

## [1.0.0-alpha.1] - 2021-01-25

Initial release.
