<?php namespace Fotis\External\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateArticlesTable extends Migration
{

    public function up()
    {
        if (Schema::hasTable('fotis_external_articles'))
        {
            
        }else{
            Schema::create('fotis_external_articles', function($table)
                {
                    $table->engine = 'InnoDB';
                    $table->increments('id');
                    $table->integer('User_id');
                    $table->text('user_email')->nullable();
                    $table->text('article_url')->nullable();
                    $table->text('article_image')->nullable();
                    $table->text('article_title')->nullable();
                    $table->timestamps();
                });
        }

        if (Schema::hasColumn('fotis_external_articles', 'user_name'))
        {
            
        }else{

            Schema::table('fotis_external_articles', function($table)
            {
                $table->text('user_name')->nullable();
            });
        }

        if (Schema::hasColumn('fotis_external_articles', 'total_views'))
        {
            
        }else{

            Schema::table('fotis_external_articles', function($table)
            {
                $table->integer('total_views')->default(0);
            });
        }
    }

    public function down()
    {
        // Schema::dropIfExists('fotis_external_articles');
    }

}
