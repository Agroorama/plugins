<?php namespace Fotis\Reviews\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRatesTable extends Migration
{

    public function up()
    {
        Schema::create('fotis_reviews_rates', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('user_email')->nullable();
            $table->string('product_id')->nullable();
            $table->integer('rating');
            $table->timestamps();
        });
    }

    public function down()
    {
        // Schema::dropIfExists('fotis_reviews_rates');
    }

}
