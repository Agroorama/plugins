<?php namespace Fotis\Reviews\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateProdRatesTable extends Migration
{

    public function up()
    {
        if (Schema::hasTable('fotis_reviews_prod_rates'))
        {
            
        }else{
            Schema::create('fotis_reviews_prod_rates', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('ids');
                $table->integer('product_id')->unsigned();
                $table->integer('total_reviews')->default(0);
                $table->integer('total_ratings')->default(0);
                $table->float('index_rating')->default(0);
                $table->integer('total_views')->default(0);
                $table->integer('wishlist')->default(0);
                $table->integer('buy')->default(0);
                $table->timestamps();
            });
        }
        if (Schema::hasColumn('fotis_reviews_prod_rates', 'wishlist'))
        {
            
        }else{

            Schema::table('fotis_reviews_prod_rates', function($table)
            {
                $table->integer('wishlist')->default(0);
            });
        }

        if (Schema::hasColumn('fotis_reviews_prod_rates', 'buy'))
        {
            
        }else{

            Schema::table('fotis_reviews_prod_rates', function($table)
            {
                $table->integer('buy')->default(0);
            });
        }
    }

    public function down()
    {
        // Schema::dropIfExists('fotis_reviews_prod_rates');
    }

}
