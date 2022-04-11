![Tests](https://github.com/laravel-json-api/laravel/workflows/Tests/badge.svg)

# JSON:API for Web Artisans

Implement feature-rich [JSON:API](https://jsonapi.org) compliant APIs in your
[Laravel](https://laravel.com) applications. Build your next standards-compliant API today.

## Why use JSON:API and Laravel JSON:API?

Great question! [Here's some reasons from this excellent article by Denisa Halmaghi](https://graffino.com/web-development/how-to-use-laravel-json-api-to-create-a-json-api-compliant-backend-in-laravel):

### Why Use JSON:API?

- Standardised, consistent APIs.
- Feature rich - some of which are: sparse fieldsets (only fetch the fields you need), filtering, sorting, pagination,
  eager loading for relationships (includes, which solve the _N+1_ problem).
- Easy to understand.

### Why use Laravel JSON:API?

- Saves a lot of development time.
- Highly maintainable code.
- Great, extensive documentation.
- Strong conventions, but also highly customisable.
- Makes use of native Laravel features such as policies and form requests to make the shift easier for developers.
- Beautiful, expressive Nova-style schemas.
- Fully testable via expressive test helpers.

```php
class PostSchema extends Schema
{

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Post::class;

    /**
     * The maximum include path depth.
     *
     * @var int
     */
    protected int $maxDepth = 3;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make(),
            BelongsTo::make('author')->type('users')->readOnly(),
            HasMany::make('comments')->readOnly(),
            Str::make('content'),
            DateTime::make('createdAt')->sortable()->readOnly(),
            DateTime::make('publishedAt')->sortable(),
            Str::make('slug'),
            BelongsToMany::make('tags'),
            Str::make('title')->sortable(),
            DateTime::make('updatedAt')->sortable()->readOnly(),
        ];
    }

    /**
     * Get the resource filters.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            WhereIdIn::make($this),
            WhereIn::make('author', 'author_id'),
        ];
    }

    /**
     * Get the resource paginator.
     *
     * @return Paginator|null
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make();
    }
}
```

## Documentation

See our website, [laraveljsonapi.io](https://laraveljsonapi.io)

### Tutorial

New to JSON:API and/or Laravel JSON:API? Then
the [Laravel JSON:API tutorial](https://laraveljsonapi.io/docs/2.0/tutorial/)
is a great way to learn!

Follow the tutorial to build a blog application with a JSON:API compliant API.

## Installation

Install using [Composer](https://getcomposer.org)

```bash
composer require laravel-json-api/laravel
```

See our documentation for further installation instructions.

## Upgrading

When upgrading you typically want to upgrade this package and all our related packages. This is the recommended way:

```bash
composer require laravel-json-api/laravel --no-update
composer require laravel-json-api/testing --dev --no-update
composer up "laravel-json-api/*" cloudcreativity/json-api-testing
```

## Example Application

To view an example Laravel application that uses this package, see the
[Tutorial Application](https://github.com/laravel-json-api/tutorial-app).

## License

Laravel JSON:API is open-sourced software licensed under the [Apache 2.0 License](./LICENSE).
