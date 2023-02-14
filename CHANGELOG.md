# Change Log

All notable changes to this project will be documented in this file. This project adheres to
[Semantic Versioning](http://semver.org/) and [this changelog format](http://keepachangelog.com/).

## [3.0.0] - 2023-02-14

### Changed

- Upgraded to Laravel 10 and set minimum PHP version to `8.1`.
- **BREAKING** If using the `laravel-json-api/cursor-pagination` package, you now need to passed the schema's `id` field
  to the paginator's `make()` method. I.e. use `CursorPagination::make($this->id())`

### Fixed

- **BREAKING** [#190](https://github.com/laravel-json-api/laravel/issues/190) The JSON:API media type now needs to be
  sent in the `Accept` header for a "delete" resource request. Previously there was no checking of the `Accept` media
  type, so anything could be sent. This is incorrect as the JSON:API specification shows the `Accept` header as
  `application/vnd.api+json` for [delete resource requests.](https://jsonapi.org/format/#crud-deleting)

## [2.6.0] - 2023-02-09

### Added

- New `MultiPaginator` that allows a schema to offer multiple different pagination strategies.

### Fixed

- The JSON:API rule validators for the follow query parameter fields all errored if a non-array value was provided. This
  is now fixed:
    - `fields`
    - `page`
    - `filter`

## [2.5.2] - 2023-01-25

### Fixed

- [#225](https://github.com/laravel-json-api/laravel/issues/225) Fix validation of empty field list for a resource type
  in the `fields` query parameter, e.g. `/api/v1/employees?fields[employees]=`.

## [2.5.1] - 2023-01-23

### Fixed

- [#223](https://github.com/laravel-json-api/laravel/issues/223) Ensure Eloquent models always have fresh data after
  write operation. This is to prevent cached relationships from having "stale" data after the write operation. This can
  occur if a related model's attributes change during the write operation, but the related model was cached before the
  write operation occurred.

## [2.5.0] - 2023-01-15

### Added

- Relations can now be conditionally set to be eager-loadable via the `canEagerLoad()` method.
- New `WhereNull` and `WhereNotNull` filters.

### Fixed

- [#204](https://github.com/laravel-json-api/laravel/issues/204) Fix exception parser causing error when request does
  not have a matching route (e.g. in a `404 Not Found` scenario).
- Fixed PHP 8.2 deprecation messages in the `laravel-json-api/validation` dependency.

## [2.4.0] - 2022-06-25

### Added

- The `JsonApiException` class now has a `context()` method. Laravel's exception handler uses this to add log context
  when the exception is logged. This means logging of JSON:API exceptions will now include the HTTP status code and the
  JSON:API errors.
- Moved the default `406 Not Acceptable` and `415 Unsupported Media Type` messages to the following two new exception
  classes:
    - `Exceptions\HttpNotAcceptableException`
    - `Exceptions\HttpUnsupportedMediaTypeException`

### Fixed

- [#184](https://github.com/laravel-json-api/laravel/issues/184) Ensure that an `Accept` header with the media type
  `application/json` is rejected with a `406 Not Acceptable` response. Previously this media type worked, which is
  incorrect as the JSON:API specification requires the media type `application/vnd.api+json`.
- [#197](https://github.com/laravel-json-api/laravel/pull/197) Fix sending `null` for a to-one relationship update.

## [2.3.0] - 2022-04-11

### Added

- Added Spanish and Brazilian Portuguese translations for specification and validation error messages.

## [2.2.0] - 2022-04-10

### Added

- [#181](https://github.com/laravel-json-api/laravel/issues/181) The `JsonApiController` now extends the base Laravel
  controller.

### Fixed

- [#180](https://github.com/laravel-json-api/laravel/pull/180) Add missing method to the Authorizer stub.

## [2.1.2] - 2022-04-04

### Fixed

- [#175](https://github.com/laravel-json-api/laravel/issues/175) Fix page URLs missing sparse field sets.

## [2.1.1] - 2022-04-01

### Fixed

- [#178](https://github.com/laravel-json-api/laravel/issues/178) Allow a resource id that is `"0"`.

## [2.1.0] - 2022-02-20

### Added

- [#110](https://github.com/laravel-json-api/laravel/issues/110) For requests that modify a relationship, it is now
  possible to get the model or models referenced in the request JSON using the `toOne()` or `toMany()` methods on the
  resource request class.
- [#113](https://github.com/laravel-json-api/laravel/issues/113) The Eloquent `Number` field can now be configured to
  accept numeric strings by calling the `acceptStrings()` method on the field.

## [2.0.0] - 2022-02-09

### Added

- This package now supports Laravel 9.
- This package now supports PHP 8.1.

### Changed

- **BREAKING** PHP 8.1 introduces `readonly` as a keyword. It was therefore necessary to rename the following interface
  and trait:
    - `LaravelJsonApi\Eloquent\Contracts\ReadOnly` is now `IsReadOnly`.
    - `LaravelJsonApi\Eloquent\Fields\Concerns\ReadOnly` is now `IsReadOnly`.
- Return types have been added to all internal methods in all child packages, to remove deprecation messages in PHP 8.1
- [#83](https://github.com/laravel-json-api/laravel/issues/83) Amended container bindings to ensure package works with
  Laravel Octane. Most of these changes should have no impact on consuming applications. However, the following changes
  could potentially be breaking to the JSON:API `Server` class in an application:
    - The type-hint of the first constructor argument has changed to `LaravelJsonApi\Core\Support\AppResolver`.
    - The deprecated `$container` property has been removed, and the `$app` property is now private. To access the
      current application instance in your server class, use `$this->app()` instead.
- **BREAKING** [#110](https://github.com/laravel-json-api/laravel/issues/110) The `model()` and `modelOrFail()` methods
  on the `ResourceQuery` request class have been changed from `public` to `protected`. These were not documented for use
  on this query class, and were only intended to be used publicly on the `ResourceRequest` class. Although technically
  breaking, this change is unlikely to affect the vast majority of applications which should not be using the method.

## [1.1.0] - 2022-01-03

### Added

- The default JSON:API resource class can now be changed via
  the `LaravelJsonApi\Laravel\LaravelJsonApi::defaultResource()` method. This should be set in a service
  provider's `register()` method.
- [#127](https://github.com/laravel-json-api/laravel/issues/127) The `JsonApiResource` class now has a
  protected `serializeRelation` method that can be used to override the default serialization of relationships if
  needed.
- [#111](https://github.com/laravel-json-api/laravel/issues/111) Relationship documents returned by relationship `self`
  routes will now include any non-standard links set on the resource relationship in the top-level `links` member.

### Fixed

- [#147](https://github.com/laravel-json-api/laravel/issues/147) Related relationship response now correctly merge the
  relationship links into the top-level document links member.
- [#130](https://github.com/laravel-json-api/laravel/issues/130) The `JsonApiResource` now correctly handles conditional
  fields when iterating over relationships to find a specific relation.
- [#105](https://github.com/laravel-json-api/laravel/issues/105) The JSON:API document returned by a relationship `self`
  route now handles a relationship not existing if it is hidden. Previously an exception was thrown when attempting to
  merge relationship links into the document.
- [#111](https://github.com/laravel-json-api/laravel/issues/111) Relationship documents now handle a relationship that
  does not have one or both of the `self` and `related` relationship links.

## [1.0.1] - 2021-12-08

### Changed

- The maximum PHP version is now 8.0. PHP 8.1 is not supported because it introduces a breaking change. The next major
  version of this package will add support for PHP 8.1.

### Fixed

- [#139](https://github.com/laravel-json-api/laravel/issues/139) Fix the `WhereHas` and `WhereDoesntHave` filters.
  Previously these were not iterating over the filters from the correct resource schema - they were iterating over the
  filters from the schema to which the relationship belonged. They now correctly iterate over the filters from the
  schema for the resource that is on the inverse side of the relationship.

## [1.0.0] - 2021-07-31

### Added

- New relationship filter classes: `Has`, `WhereHas`, `WhereDoesntHave`. Refer to
  the [filter documentation](https://laraveljsonapi.io/docs/1.0/schemas/filters.html#available-filters) for details.

### Changed

- **BREAKING: Countable Relationships.** This feature is now turned off by default. Although included in the 1.0
  release, this feature is **not considered production-ready**. This is because we plan to make breaking changes to it,
  which will change how the client requests countable relationships. As such, this feature is considered
  highly-experimental and developers must opt-in to it by calling the `canCount()` method on a relationship. Refer to
  the [Countable relationships chapter](https://laraveljsonapi.io/docs/1.0/digging-deeper/countable.html) in the
  documentation for more details.
- **BREAKING: Cursor Pagination.** Laravel now has its own cursor pagination feature. We have therefore moved our
  implementation into its own
  package: [laravel-json-api/cursor-pagination](https://github.com/laravel-json-api/cursor-pagination)
  This change has been made because it makes sense for the in-built cursor pagination implementation to use Laravel's
  cursor pagination implementation rather than our own custom one. Support for Laravel's cursor pagination will be added
  during the `1.x` release cycle. If you are already using our cursor implementation, you can migrate in two easy steps:
    1. Install the new package: `composer require laravel-json-api/cursor-pagination`
    2. In any schemas using the cursor pagination, change the import statement
       from `LaravelJsonApi\Eloquent\Pagination\CursorPagination` to `LaravelJsonApi\CursorPagination\CursorPagination`.

## [1.0.0-beta.5] - 2021-07-10

### Added

- The authorizer now has separate `showRelated()` and `showRelationship()` methods. Previously both these controller
  actions were authorized via the single `showRelationship()` method. Adding the new `showRelated` method means
  developers can now implement separate authorization logic for these two actions if desired. Our default implementation
  remains unchanged - both are authorized using the `view<RelationshipName>` method on the relevant policy.
- The request class now has a `isCreatingOrUpdating()` helper method to determine whether the request is to create or
  updated a resource.
- Add stop on first failure to all validators in the resource request class.
- [#85](https://github.com/laravel-json-api/laravel/issues/85) When running an application with debug mode turned on,
  the default JSON:API error object for an exception will now contain detailed exception information, including the
  stack trace, in the object's `meta` member.
- [#103](https://github.com/laravel-json-api/laravel/issues/103) Can now fully customise attribute serialization to JSON
  using the `extractUsing()` callback. This receives the model, column name and value. This is useful if the developer
  needs to control the serialization of a few fields on their schema. However, the recommendation is to use a resource
  class for complete control over the serialization of a model to a JSON:API resource.

### Changed

- Minimum Laravel version is now `8.30`. This change was required to use the `$stopOnFirstFailure` property on Laravel's
  `FormRequest` class.
- Schema classes no longer automatically sort their fields by name when iterating over them. This change was made to
  give the developer full control over the order of fields (particularly as this order affects the order in which fields
  are listed when serialized to a JSON:API resource). Developers can list fields in name order if that is the preferred
  order.
- Removed the `LaravelJsonApi\Spec\UnexpectedDocumentException` which was thrown if there was a failure when decoding
  request JSON content before parsing it for compliance with the JSON:API specification. A `JsonApiException` will now
  be thrown instead.

### Fixed

- [#101](https://github.com/laravel-json-api/laravel/issues/101) Ensure controller create action always returns a
  response that will result in a `201 Created` response.
- [#102](https://github.com/laravel-json-api/laravel/issues/102) The attach and detach to-many relationship controller
  actions now correctly resolve the collection query class using the relation's inverse resource type. Previously they
  were incorrectly using the primary resource type to resolve the query class.

## [1.0.0-beta.4] - 2021-06-02

### Fixed

- [#76](https://github.com/laravel-json-api/laravel/issues/76) Pagination links will now be correctly added to related
  resources and relationship identifiers responses.

## [1.0.0-beta.3] - 2021-04-26

### Added

- [#14](https://github.com/laravel-json-api/laravel/issues/14) Additional sort parameters can now be added to Eloquent
  schemas. Previously only sortable attributes were supported. These new classes are added to schemas in the
  `sortables()` method.
- Eloquent schemas now support a default sort order via the `$defaultSort` property.
- New generator command `jsonapi:sort-field` to create a custom sort field class.
- [#74](https://github.com/laravel-json-api/laravel/issues/74) Developers can now add default include paths to the query
  request classes (e.g. `PostQuery` and `PostCollectionQuery`) via the `$defaultIncludePaths` property. These include
  paths are used if the client does not provide any include paths.

## [1.0.0-beta.2] - 2021-04-20

### Added

- [#65](https://github.com/laravel-json-api/laravel/issues/65) **BREAKING** The `fill()` method on Eloquent fields has
  been updated to receive all the validated data as its third argument. This change was made to allow fields to work out
  the value to fill into the model based on other JSON:API field values. If you have written any custom fields, you will
  need to update the `fill()` method on your field class.
- **BREAKING** Eloquent attributes now support serializing and filling column values on related objects. This is
  primarily intended for use with the Eloquent `belongsTo`, `hasOne`, `hasOneThrough` and `morphOne` relationships that
  have a `withDefault()` method. As part of this change, the `mustExist()` method was added to the `Fillable` interface.
  If you have written any custom fields, you will need to add this method to your field class - it should return `true`
  if the attribute needs to be filled *after* the primary model has been persisted.
- [#58](https://github.com/laravel-json-api/laravel/issues/58) Schema model classes can now be a parent class or an
  interface.

### Fixed

- [#69](https://github.com/laravel-json-api/laravel/issues/69) Fixed the parsing of empty `include`, `sort` and
  `withCount` query parameters.

## [1.0.0-beta.1] - 2021-03-30

### Added

- [#18](https://github.com/laravel-json-api/laravel/issues/18) Added a `withCount` query parameter. For Eloquent
  resources, this allows a client to request the relationship count for the primary data's relationships. Refer to
  documentation for implementation details.
- [#55](https://github.com/laravel-json-api/laravel/pull/55) Encoding and decoding of resource IDs is now supported.
  The `ID` field needs to implement the `LaravelJsonApi\Contracts\Schema\IdEncoder` interface for this to work.
- [#41](https://github.com/laravel-json-api/laravel/issues/41) Hash IDs are now supported by installing the
  `laravel-json-api/hashids` package and using the `HashId` field instead of the standard Eloquent `ID` field. Refer to
  documentation for details.
- [#30](https://github.com/laravel-json-api/laravel/issues/30) Non-Eloquent resources are no supported via the
  `laravel-json-api/non-eloquent` package. Refer to documentation for implementation details.
- There is now a `Core\Reponses\RelatedResponse` class for returning the result for a related resources endpoint. For
  example, the `/api/v1/posts/1/comments` endpoint. Previously the `DataResponse` class was used. While this class can
  still be used, the new `RelatedResponse` class merges relationship meta into the top-level `meta` member of the
  response document. For *to-many* relationships that are countable, this will mean the top-level `meta` member will
  contain the count of the relationship.
- The schema generator Artisan command now has a `--non-eloquent` option to generate a schema for a non-Eloquent
  resource.

### Changed

- The `LaravelJsonApi::registerQuery()`, `LaravelJsonApi::registerCollectionQuery()` and
  `LaravelJsonApi::registerRequest()` methods must now be used to register custom HTTP request classes for specified
  resource types. Previously methods could be called on the `RequestResolver` classes, but these have now been removed.

### Fixed

- Relationship endpoints that return resource identifiers now correctly include page meta in the top-level meta member
  of the document, if the results are paginated. Previously the page meta was incorrectly omitted.

## [1.0.0-alpha.5] - 2021-03-12

### Added

- [#43](https://github.com/laravel-json-api/laravel/issues/43) The package now supports soft-deleting resources. For
  full details on how to apply this to resource schemas, refer to the new *Soft Deleting* chapter in the documentation.
- Multi-resource models are now supported. This allows developers to represent a single model class as multiple
  different JSON:API resource types within an API. Refer to documentation for details of how to implement.
- [#8](https://github.com/laravel-json-api/laravel/issues/8) The new `MorphToMany` relation field can now be used to add
  polymorphic to-many relations to a schema. Refer to documentation for details.
- Developers can now type-hint dependencies in their server's `serving()` method.
- Can now manually register request, query and collection query classes using the `RequestResolver::registerRequest()`,
  `RequestResolver::registerQuery()` and `RequestResolver::registerCollectionQuery()` static methods.

## [1.0.0-alpha.4] - 2021-02-27

### Added

- Added missing `jsonapi:authorizer` generator command.
- The Eloquent schema now has `indexQuery` and `relatableQuery` methods. These allow filtering for authorization
  purposes when a list of resources is being retrieved. For instance, it could filter those queries so that only models
  belonging to the authenticated user are returned.
- [#23](https://github.com/laravel-json-api/laravel/issues/23) The resource request class now does not need to exist for
  the destroy controller action. Previously the implementation was expecting the resource request class to exist, even
  though delete validation was optional.
- [#24](https://github.com/laravel-json-api/laravel/issues/24) Controller actions will now stop executing and return a
  response if one is returned by the *before* action hooks: i.e. `searching`, `reading`, `saving`, `creating`,
  `updating`, `deleting`, `readingRelated<Name>`, `reading<Name>`, `updating<Name>`, `attaching<Name>` and
  `detaching<Name>`.
- [#37](https://github.com/laravel-json-api/laravel/issues/37) Can now use constructor dependency injection in `Server`
  classes.
- [#40](https://github.com/laravel-json-api/laravel/issues/40) There is now a new `MetaResponse` class that can be used
  when returning meta-only responses. In addition, response classes have been updated to add a `withServer` method. This
  can be used to specify the named server the response should use to encode the JSON:API document. This has to be used
  when returning responses from routes that have not run the JSON:API middleware (i.e. there is no default server
  available via the service container).
- [#9](https://github.com/laravel-json-api/laravel/issues/9) The Laravel route registrar is now passed through to
  the `resources`, `relationships` and `actions` callbacks as the second function argument.
- [#36](https://github.com/laravel-json-api/laravel/issues/36) Eloquent schemas now support complex singular filter
  logic, via the `Schema::isSingular()` method.
- [#33](https://github.com/laravel-json-api/laravel/issues/33) Specification compliance will now reject an incorrect
  resource type in a relationship. For example, if a relationship expects `tags` but the client sends `posts`, the
  request will be rejected with an error message that `posts` are not supported.

### Changed

- [#22](https://github.com/laravel-json-api/laravel/issues/22) **BREAKING** The `index` and `store` methods on the
  authorizer contract now receive the model class as their second argument. This is useful for authorizers that are used
  for multiple resource types.
- **BREAKING** When querying or modifying models via the schema repository or store, calls to `using()` must be replaced
  with `withRequest()`. This change was made to make it clearer that the request class can be passed into query
  builders.
- [#28](https://github.com/laravel-json-api/laravel/issues/28) The sparse field sets validation rule will now reject
  with a specific message identifying any resource types in the parameter that do not exist.
- [#35](https://github.com/laravel-json-api/laravel/issues/35) The `Relation::type()` method must now be used when
  setting the inverse resource type for the relation.

### Fixed

- Optional parameters to generator commands that require values now work correctly. Previously these were incorrectly
  set up as optional parameters that expected no values.
- [#25](https://github.com/laravel-json-api/laravel/issues/25) The encoder now correctly handles conditional fields when
  iterating over a resource's relationships.
- [#26](https://github.com/laravel-json-api/laravel/issues/26) Fix parsing the `fields` query parameter to field set
  value objects.
- [#34](https://github.com/laravel-json-api/laravel/issues/34) Do not require server option when generating a generic
  authorizer with multiple servers present.
- [#29](https://github.com/laravel-json-api/laravel/issues/29) Do not reject delete requests without a `Content-Type`
  header.
- [#11](https://github.com/laravel-json-api/laravel/issues/11) Fixed iterating over an empty *to-many* generator twice
  in the underlying compound document encoder.

### Deprecated

- The `Relation::inverseType()` method is deprecated and will be removed in `1.0-stable`. Use `Relation::type()`
  instead.

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
