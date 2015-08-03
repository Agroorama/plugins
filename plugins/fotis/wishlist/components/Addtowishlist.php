<?php namespace Fotis\Wishlist\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Database\DatabaseManager;
use Rainlab\User\Models\User;
use Tiipiik\Catalog\Models\Product;
use Fotis\Wishlist\Models\Wish;
use DB;
use Input;

class Addtowishlist extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Add to wish list Component',
            'description' => 'Add products to wish list'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onSavetoWishlist(){
        $user_email= post('user_email');
        $product_id = post('product_id');
        $product_title = post('product_title');
        $product_image = post('product_image');
        $product_url = post('product_slug');

        $tempdb = DB::table('fotis_wishlist_wishes')->where('user_email', $user_email)->where('product_id', $product_id)->first();

        if($tempdb){

            return 'Already';
        }
        else{

            $wishlist = new Wish;
            $wishlist->user_email = $user_email;
            $wishlist->product_id = $product_id;
            $wishlist->product_title = $product_title;
            $wishlist->product_image = $product_image;
            $wishlist->product_url = $product_url;
            $wishlist->save();

            // return [
            // 'User Mail' => $user_mail,
            // 'Product Id' => $product_id,
            // 'Image' => $image,
            // 'Wishlist' => 'insert'
            // ];

            DB::table('fotis_reviews_prod_rates')->where('product_id', $product_id)->increment('wishlist'); // new

            return 'Added';
        }
    }

}
