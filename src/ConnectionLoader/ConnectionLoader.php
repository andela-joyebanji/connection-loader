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
 * @website www.laralabs.uk
 */

namespace ConnectionLoader;

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
        //$configPath = __DIR__ . '/../config/connectionloader.php';
        $configPath = config_path().'/connectionloader.php';
        $this->publishes([$configPath => config_path('connectionloader.php')], 'config');

        if($this->app['config']->get('connectionloader.enabled')){

            $connection = $this->app['config']->get('connectionloader.connection');
            $table = $this->app['config']->get('connectionloader.table');
            $check = $this->app['config']->get('connectionloader.check_enabled');


            if(isset($connection) && isset($table) && isset($check))
            {
                /*
                 * Function to gather database connections from database and table provided
                 * in configuration file. Compiles into file that returns an array.
                 * Function returns path to the temporary file.
                */
                $fileName = ConnectionLoader::getConnections($connection, $table);
                if($fileName != null)
                {
                    $file_path = storage_path('app/'.$fileName);

                    /*
                     * Merge the returned configuration array into the existing database.connections
                     * configuration key.
                     */
                    $key = 'database.connections';
                    $config = $this->app['config']->get($key, []);
                    $configSet = $this->app['config']->set($key, array_merge(require $file_path, $config));

                    /*
                     * Now to delete the temporary file created during the process
                     */
                    $result = Storage::delete($fileName);
                    if($result === false)
                    {
                        \Monolog\Handler\error_log('Failed to delete '.storage_path().$fileName);
                        \Monolog\Handler\error_log('Trying once more');
                        $result = Storage::delete($fileName);
                        if($result === true)
                        {
                            \error_log(storage_path().$fileName.' Deleted successfully');
                        }
                        else
                        {
                            \error_log('Failed to delete twice, delete manually '.storage_path().$fileName);
                        }
                    }
                    ConnectionLoader::checkConnections($connection, $table, $check);
                }
                else
                {
                    \error_log('Error in returned file name value');
                }
            }
            else
            {
                \error_log('Invalid connection or table specified in configuration file');
            }
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //$configPath = __DIR__ . '/../config/connectionloader.php';
        $configPath = config_path().'/connectionloader.php';
        $this->mergeConfigFrom($configPath, 'connectionloader');
    }
}
