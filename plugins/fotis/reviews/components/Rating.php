<?php namespace Fotis\Reviews\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Database\DatabaseManager;
use Rainlab\User\Models\User;
use Tiipiik\Catalog\Models\Product;
use Fotis\Reviews\Models\Rate;
use Fotis\Reviews\Models\ProdRate;
use DB;
use Input;

class Rating extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Rating Component',
            'description' => 'Start rating component'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'tiipiik.catalog::lang.component.product_details.param.id_param_title',
                'description' => 'tiipiik.catalog::lang.component.product_details.param.id_param_desc',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
            'maxviews' => [
             'title'             => 'Max Views',
             'description'       => 'The most amount of Page Views',
             'default'           => '200',
             'type'              => 'string',
             'validationPattern' => '^[0-9]+$',
             'validationMessage' => 'The Max Views property can contain only numeric symbols'
            ],
            'maxshares' => [
             'title'             => 'Max Shares',
             'description'       => 'The most amount of Shares',
             'default'           => '15',
             'type'              => 'string',
             'validationPattern' => '^[0-9]+$',
             'validationMessage' => 'The Max Shares property can contain only numeric symbols'
            ],
            'wishlist' => [
             'title'             => 'Max Wishlist',
             'description'       => 'The most amount of Wishlist',
             'default'           => '10',
             'type'              => 'string',
             'validationPattern' => '^[0-9]+$',
             'validationMessage' => 'The Max Shares property can contain only numeric symbols'
            ],
            'buy' => [
             'title'             => 'Max Buy',
             'description'       => 'The most amount of Buy',
             'default'           => '10',
             'type'              => 'string',
             'validationPattern' => '^[0-9]+$',
             'validationMessage' => 'The Max Shares property can contain only numeric symbols'
            ]
        ];
    }


    public function onRun(){

        $shares_counter = 0;
        $likes_counter = 0;
         // if(isset(post('link'))){
            $link = 'https://graph.facebook.com/';
            $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            //$url = "http://www.agroo.gr/blog/post/azalea-problhmata-kai-antimetwpish"; // azalea-problhmata-kai-antimetwpish
            $link .= $url;
            $json = file_get_contents($link);
            $data = json_decode($json);
            if(isset($data->likes)){
                $like_counter = $data->likes;
            }
            if(isset($data->shares)){
                $shares_counter = $data->shares;
            }

			$this->page['likes'] = $likes_counter;
			$this->page['shares'] = $shares_counter;
            // $url = "$_SERVER[REQUEST_URI]";
            // $slug = end((explode('/', rtrim($url, '/'))));
            $slug = $this->property('slug');
        /*}
        else{

            return 'Broken Link';
            $this->page['shares'] = 0;
        }*/
        
            $tempdb = DB::table('tiipiik_catalog_products')->where('slug', $slug)->first();
            $prid = $tempdb->id;
            $this->page['productid'] = $prid;
            

            $this->page['products'] = Product::orderByRaw("RAND()")->get();
            
            $query = DB::table('fotis_reviews_prod_rates')->where('product_id', '=', $prid)->get();
            
            if (!$query){
                
                $rating = new ProdRate;
                $rating->product_id = $prid;
                $rating->total_reviews = 0;
                $rating->total_ratings = 0;
                $rating->index_rating = 0;
                $rating->total_views = 0;
                $rating->wishlist = 0; // new
                $rating->buy = 0; // new
                $rating->save();
                
            }else{
                
            }
            DB::table('fotis_reviews_prod_rates')->where('product_id', $prid)->increment('total_views');
            $query = DB::table('fotis_reviews_prod_rates')->where('product_id', '=', $prid)->get();
            
            $rating = $query[0]->total_ratings;
            $reviews = $query[0]->total_reviews;
            $views = $query[0]->total_views;
            $wishlist = $query[0]->wishlist;
            $buy = $query[0]->buy;


            
            $this->page['totalviews'] = $views;
            $this->page['wishlist'] = $wishlist;
            $this->page['buy'] = $buy;

            if (($reviews!=0) and ($rating!=0) ){
                $this->page['NewRating'] = ($rating/$reviews);
                $index_rating = ($rating/$reviews);
            }
            else{
                $this->page['NewRating'] = 2.5;
                $index_rating = 2.5;
            }
            
            $morating = $this->moratingcalc($index_rating, $views, $shares_counter, $wishlist, $buy);
            
            $this->page['mytotalrating'] = $morating;
            
            DB::table('fotis_reviews_prod_rates')->where('product_id', $prid)->update(['index_rating' => $morating]);
    }

    
    public function onSaveRating(){

        // Initialise Variables
        $new_user_rating= post('rating');
        $user_mail = post('mail'); // $this->user->email || post('mail');
        $product_id = post('product_id'); // $this->product->title || post('product');
        $product_shares = post('shares');
        $product_likes = post('likes');
        $total_views = post('totalviews');
        $wishlist = post('wishlist');
        $buy = post('buy');
        $old_rating = 0;

        $query = DB::table('fotis_reviews_prod_rates')->where('product_id', '=', $product_id)->get();
        $old_total_rating = $query[0]->total_ratings;
        $old_total_reviews = $query[0]->total_reviews;

        $tempdb = DB::table('fotis_reviews_rates')->where('user_email', $user_mail)->where('product_id', $product_id)->first();
                
        if($tempdb){ // Update

            return $this->UpdateRating($new_user_rating, $user_mail, $product_id, $product_shares, $product_likes, $total_views, $old_rating, $old_total_rating, $old_total_reviews, $tempdb, $wishlist, $buy);
        }
        else{ //Insert

            return $this->InsertRating($new_user_rating, $user_mail, $product_id, $product_shares, $product_likes, $total_views, $old_rating, $old_total_rating, $old_total_reviews, $tempdb, $wishlist, $buy);
        }
    }


    function moratingcalc($new_rating, $total_views, $product_shares, $wishlist, $buy){

        $max_product_shares = $this->property('maxshares');
        $max_views = $this->property('maxviews'); // $max_views = DB::table('fotis_reviews_prod_rates')->max('total_views');
        $max_wishlist = $this->property('wishlist');
        $max_buy = $this->property('buy');
		
		
        // $index_new_rating = $new_rating;
		
		$index_new_rating = (2.5*$new_rating)/5;
		
        if($product_shares < $max_product_shares){
            $index_product_shares = (2*$product_shares)/($max_product_shares);    
        }else{
            $index_product_shares = 2;
        }

        if($wishlist < $max_wishlist){
            $index_wishlist = (1.5*$wishlist)/($max_product_shares);    
        }else{
            $index_wishlist = 1.5;
        }

        if($buy < $max_buy){
            $index_buy = (2.5*$buy)/($max_product_shares);    
        }else{
            $index_buy = 2.5;
        }

        if($total_views < $max_views){
            $index_new_reviews = 1.5*(($total_views)/($max_views));    
        }else{
            $index_new_reviews = 1.5;
        }
        
        return $index_new_rating + $index_new_reviews + $index_product_shares + $index_wishlist + $index_buy;
    }


    function UpdateRating($new_user_rating, $user_mail, $product_id, $product_shares, $product_likes, $total_views, $old_rating, $old_total_rating, $old_total_reviews, $tempdb, $wishlist, $buy){

        $old_user_rating = $tempdb->rating;
        $new_total_rating = $new_user_rating + ($old_total_rating - $old_user_rating);

        DB::update('update fotis_reviews_rates set rating = ? where user_email = ? and product_id = ?',[$new_user_rating ,$user_mail, $product_id]);
        DB::table('fotis_reviews_prod_rates')->where('product_id', $product_id)->update(['total_ratings' => $new_total_rating]);

        $new_rating = ($new_total_rating/$old_total_reviews);
        $morating = $this->moratingcalc($new_rating, $total_views, $product_shares, $wishlist, $buy);
        DB::table('fotis_reviews_prod_rates')->where('product_id', $product_id)->update(['index_rating' => $morating]);

        return [
        'User Mail' => $user_mail,
        'Product Id' => $product_id,
        'NewRating' => round($new_rating,2),
        'FacebookShares' => $product_shares,
        'FacebookLikes' => $product_likes,
        'Views' => $total_views,
        'moRating' => round($morating,2),
        'Wishlist' => $wishlist,
        'Buy' => $buy,
        'update' => 'update'
        ];
    }


    function InsertRating($new_user_rating, $user_mail, $product_id, $product_shares, $product_likes, $total_views, $old_rating, $old_total_rating, $old_total_reviews, $tempdb, $wishlist, $buy){

        $new_total_rating = $new_user_rating + $old_total_rating;
        $new_total_reviews = $old_total_reviews + 1;
        $new_rating = ($new_total_rating/$new_total_reviews);

        DB::table('fotis_reviews_prod_rates')->where('product_id', $product_id)->update(['total_ratings' => $new_total_rating]);
        DB::table('fotis_reviews_prod_rates')->where('product_id', $product_id)->update(['total_reviews' => $new_total_reviews]);

        $rating = new Rate;
        $rating->rating = $new_user_rating;
        $rating->user_email = $user_mail;
        $rating->product_id = $product_id;
        $rating->save();

        $morating = $this->moratingcalc($new_rating, $total_views, $product_shares, $wishlist, $buy);
        DB::table('fotis_reviews_prod_rates')->where('product_id', $product_id)->update(['index_rating' => $morating]);

        return [
        'User Mail' => $user_mail,
        'Product Id' => $product_id,
        'NewRating' => round($new_rating,2),
        'Facebook Shares' => $product_shares,
        'FacebookLikes' => $product_likes,
        'Views' => $total_views,
        'moRating' => round($morating,2),
        'Wishlist' => $wishlist,
        'Buy' => $buy,
        'insert' => 'insert'
        ];
    }

}
