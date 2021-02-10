# Change Log

All notable changes to this project will be documented in this file. This project adheres to
[Semantic Versioning](http://semver.org/) and [this changelog format](http://keepachangelog.com/).

## Unreleased

### Added

- Added missing `jsonapi:authorizer` generator command.

### Fixed

- Optional parameters to generator commands that require values now work correctly. Previously these were incorrectly
  set up as optional parameters that expected no values.

## [1.0.0-alpha.3] - 2021-02-09

### Added

- [#12](https://github.com/laravel-json-api/laravel/pull/12) Can now register routes for custom actions on a resource,
  using the `actions()` helper method when registering resources. See the PR for examples.
- The `JsonApiController` now has the Laravel `AuthorizesRequests`, `DispatchesJobs` and `ValidatesRequests` traits
  applied.
- [#6](https://github.com/laravel-json-api/laravel/issues/6) Resource class can now use conditional fields in their
  relationships. This works in the same way as conditional attributes: the resource's `when()` and `mergeWhen()` method
  should be used to add conditional relationships.
- [#13](https://github.com/laravel-json-api/laravel/issues/13) Added French translations for JSON:API errors generated
  by specification parsing and resource/query parameter validation.
- [#7](https://github.com/laravel-json-api/laravel/issues/7) Eloquent schemas now support default eager loading via
  their `$with` property.
- [#15](https://github.com/laravel-json-api/laravel/issues/15) When parsing a JSON:API document for compliance with the
  specification, the client will now receive a clearer error message if they submit a *to-one* relationship object for a
  *to-many* relationship (and vice-versa).

### Changed

- [#2](https://github.com/laravel-json-api/laravel/issues/2) **BREAKING** Improved the extraction of existing resource
  field values when constructing validation data for update requests:
    - The `existingAttributes()` and `existingRelationships()` methods on the resource request class has been removed.
      If you need to modify the existing values before the client values are merged, implement the `withExisting()`
      method instead. This receives the model and its JSON representation (as an array).
    - The `mustValidate()` method must now be called on a schema relationship field. (Previously this was on the
      resource relation.) By default, belongs-to and morph-to relations will be included when extracting existing
      values; all other relations will not. Use the `mustValidate()` or `notValidated()` method on the schema relation
      to alter whether a relation is included in the extracted values.

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
