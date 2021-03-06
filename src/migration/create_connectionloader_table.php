<?php
/**
 * Laralabs Connection Loader
 *
 * Laravel Service Provider that loads database connection details
 * into the application from a table specified in configuration
 * file.
 *
 * ConnectionLoader Database Migration
 *
 * @license The MIT License (MIT) See: LICENSE file
 * @copyright Copyright (c) 2016 Matt Clinton
 * @author Matt Clinton <matt@laralabs.uk>
 * @website http://www.laralabs.uk
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatalogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connectionloader', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('driver');
            $table->string('host');
            $table->string('port');
            $table->string('database');
            $table->string('username');
            $table->string('password');
            $table->string('charset');
            $table->string('collation');
            $table->string('prefix');
            $table->boolean('strict')->default(false);
            $table->string('schema');
            $table->string('engine')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('connectionloader');
    }
}
