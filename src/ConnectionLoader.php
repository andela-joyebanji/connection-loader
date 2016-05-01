<?php
/**
 * Laralabs Connection Loader
 *
 * Laravel Service Provider that loads database connection details
 * into the application from a table specified in configuration
 * file.
 *
 * ConnectionLoader class
 *
 * @license The MIT License (MIT) See: LICENSE file
 * @copyright Copyright (c) 2016 Matt Clinton
 * @author Matt Clinton <matt@laralabs.uk>
 * @website www.laralabs.uk
 */

namespace Laralabs\ConnectionLoader;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class ConnectionLoader extends Manager
{
    /**
     *
     * @param string $connection
     * @param string $table
     */
    public function __construct($connection, $table)
    {
        parent::__construct();
    }

    /**
     * getConnections() function fetches all connections from
     * connection and table specified. Then returns a path
     * to a configuration file which gets imported and
     * deleted.
     *
     * @param $connection
     * @param $table
     * @return null|string
     */
    public static function getConnections($connection, $table)
    {
        $connections = DB::connection($connection)->table($table)->get();

        if(!empty($connections)) {
            /**
             * Create an empty array which will get populated with the data from
             * $connections in the correct format within the foreach statement
             * below file generation.
             */
            $config = array();
            /**
             * Generate a random string for the filename as it is temporary
             * and more secure.
             */
            $random_string = ConnectionLoader::random_string(35);
            /**
             * Append the .php extension to the file name and store here
             * storage_path/app/$tempFile with some starting content.
             */
            $tempFileName = $random_string . '.php';
            $tempFile = Storage::disk('local')->put($tempFileName, '<?php return ');

            if ($tempFile === true) {
                /**
                 * Loops through the connections checking the driver and pushes
                 * correctly formatted array into the $config array.
                 */
                foreach ($connections as $connection) {
                    $name = $connection->name;
                    $driver = $connection->driver;

                    if ($driver == 'sqlite') {

                        $connection_config = array(
                            'driver' => $driver,
                            'database' => database_path($connection->database),
                            'prefix' => $connection->prefix,
                        );

                        $config[$name] = $connection_config;

                    } elseif ($driver == 'mysql') {

                        $strict = $connection->strict;
                        if ($strict == 1) {
                            $strict = true;
                        } else {
                            $strict = false;
                        }

                        $connection_config = array(
                            'driver' => $driver,
                            'host' => $connection->host,
                            'database' => $connection->database,
                            'username' => $connection->username,
                            'password' => Crypt::decrypt($connection->password),
                            'charset' => $connection->charset,
                            'collation' => $connection->collation,
                            'prefix' => $connection->prefix,
                            'strict' => $strict,
                        );

                        $config[$name] = $connection_config;

                    } elseif ($driver == 'pgsql') {
                        $connection_config = array(
                            'driver' => $driver,
                            'host' => $connection->host,
                            'port' => $connection->port,
                            'database' => $connection->database,
                            'username' => $connection->username,
                            'password' => Crypt::decrypt($connection->password),
                            'charset' => $connection->charset,
                            'prefix' => $connection->prefix,
                            'schema' => $connection->schema,
                        );

                        $config[$name] = $connection_config;
                    }
                }

                /**
                 * Append a var_export of $config to the temporary file and
                 * append the semicolon.
                 *
                 * Returns the temporary filename.
                 */
                $endFile = ';';
                Storage::append($tempFileName, var_export($config, true));
                Storage::append($tempFileName, $endFile);

                return $tempFileName;

            } else {
                \error_log('Unable to create temporary file');
            }
        } else {
            \error_log('Configuration File Connection Invalid.');
            return null;
        }
    }

    /**
     * checkConnections function.
     *
     * If enabled in the configuration file this will check the connections
     * and update the status field in the table provided for each
     * connection with a boolean value.
     *
     * @param $connection_name
     * @param $table
     * @param $check
     */
    public static function checkConnections($connection_name, $table, $check)
    {
        if($check === true)
        {
            $connections = DB::connection($connection_name)->table($table)->get();

            foreach($connections as $connection)
            {
                $name = $connection->name;

                try{
                    $testConnection = DB::connection($name)->getPdo();
                }catch(\Exception $e)
                {
                    $error = $e->getMessage();
                    \error_log($error);
                }
                if(!empty($testConnection) && empty($error))
                {
                    $update = DB::connection($connection_name)->table($table)->where('name', $name)->update(['status' => true]);
                }
                else
                {
                    $update = DB::connection($connection_name)->table($table)->where('name', $name)->update(['status' => false]);
                }
            }
        }
    }


    /**
     * random_string function
     * Does what it says on the tin.
     *
     * @param $length
     * @return string
     */
    public static function random_string($length) {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }
}