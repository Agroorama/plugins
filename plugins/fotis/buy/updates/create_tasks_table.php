<?php namespace Agroo\Buy\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTasksTable extends Migration
{

    public function up()
    {
        Schema::create('agroo_buy_tasks', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('product_id')->nullable();
            $table->string('email')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2)->default('0.00');
            $table->decimal('discount_price', 8, 2)->default('0.00');
            $table->text('image')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agroo_buy_tasks');
    }

}
