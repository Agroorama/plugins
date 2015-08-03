<?php namespace Fotis\Wishlist\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateWishesTable extends Migration
{

    public function up()
    {
        Schema::create('fotis_wishlist_wishes', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('product_id')->nullable();
            $table->text('product_title')->nullable();
            $table->text('user_email')->nullable();
            $table->text('product_image')->nullable();
            $table->text('product_url')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
      //  Schema::dropIfExists('fotis_wishlist_wishes');
    }

}
