# Connection Loader for Laravel 5

Have you ever wanted to add database connections from the backend of a Laravel application without the need to edit the configuration files?

Connection Loader for Laravel is a ServiceProvider that loads database connection details from a table in the specified database connection.

You can then access these connections using their name property's with conventional Laravel techniques.

## Installation

Install via composer by adding the following line to your `composer.json` file:

```php
"laralabs/connection-loader": "~1.0.0"
```

After updating composer you will need to add the ServiceProvider to the providers array in `config/app.php`

```php
Laralabs\ConnectionLoader\ConnectionLoaderServiceProvider::class,
```

Publish the configuration file with the following command:

```php
php artisan vendor:publish
```

This will add `connectionloader.php` to your config directory and copy the database migration to your migrations folder.

If you change the name of the table in the `connectionloader.php` configuration file then alter it in the database migration file 'create_connectionloader_table.php'.

Run the database migration with:

```php
php artisan migrate
```

## Support

Please raise an issue on Github if there is a problem.

## License

This is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
