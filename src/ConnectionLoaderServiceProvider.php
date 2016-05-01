<?php
/**
 * Laralabs Connection Loader
 *
 * Laravel Service Provider that loads database connection details
 * into the application from a table specified in configuration
 * file.
 *
 * ConnectionLoaderServiceProvider
 *
 * @license The MIT License (MIT) See: LICENSE file
 * @copyright Copyright (c) 2016 Matt Clinton
 * @author Matt Clinton <matt@laralabs.uk>
 * @website http://www.laralabs.uk
 */

namespace Laralabs\ConnectionLoader;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use ConnectionLoader;

class ConnectionLoaderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/config/connectionloader.php';
        $migrationPath = __DIR__ . '/migration/create_connectionloader_table.php';
        $this->publishes([$configPath => config_path('connectionloader.php')], 'config');
        $this->publishes([$migrationPath => database_path('migrations')], 'migrations');

        if(!$this->app['config']->get('connectionloader.enabled')){
            return;
        }

        $connection = $this->app['config']->get('connectionloader.connection');
        $table = $this->app['config']->get('connectionloader.table');
        $check = $this->app['config']->get('connectionloader.check_enabled');


        if(!(isset($connection) && isset($table) && isset($check)))
        {
            return \error_log('Invalid connection or table specified in configuration file');
        }

        /**
         * Function to gather database connections from database and table provided
         * in configuration file. Compiles into file that returns an array.
         * Function returns path to the temporary file.
        */
        $fileName = ConnectionLoader::getConnections($connection, $table);
        if($fileName == null)
        {
            return \error_log('Error in returned file name value');
        }
        $file_path = storage_path('app/'.$fileName);

        /**
         * Merge the returned configuration array into the existing database.connections
         * configuration key.
         */
        $key = 'database.connections';
        $config = $this->app['config']->get($key, []);
        $configSet = $this->app['config']->set($key, array_merge(require $file_path, $config));

        /**
         * Now to delete the temporary file created during the process
         */
        $result = Storage::delete($fileName);
        if($result === false)
        {
            \error_log('Failed to delete '.storage_path().$fileName);
            \error_log('Trying once more');
            $result = Storage::delete($fileName);
            if($result === true)
            {
                return \error_log(storage_path().$fileName.' Deleted successfully');
            }
            
            return \error_log('Failed to delete twice, delete manually '.storage_path().$fileName);
        }
        
        ConnectionLoader::checkConnections($connection, $table, $check);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/config/connectionloader.php';
        $this->mergeConfigFrom($configPath, 'connectionloader');
    }
}
