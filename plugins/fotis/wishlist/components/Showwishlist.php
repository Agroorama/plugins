<?php namespace Fotis\Wishlist\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Database\DatabaseManager;
use Rainlab\User\Models\User;
use Tiipiik\Catalog\Models\Product;
use Fotis\Wishlist\Models\Wish;
use DB;
use Input;

class Showwishlist extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'showwishlist Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onWishlistdelete()
    {
        
        $email = post('email');
        $id = post('id');
        
        DB::table('fotis_wishlist_wishes')->where('user_email', $email)->where('product_id', $id)->delete();
        DB::table('fotis_reviews_prod_rates')->where('product_id', $id)->decrement('wishlist'); // new
		return $title;
            
    }
    
}
