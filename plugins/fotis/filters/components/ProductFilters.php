<?php namespace Fotis\Filters\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Database\DatabaseManager;
use App;
use Request;
use Redirect;
use Cms\Classes\Page;
use Tiipiik\Catalog\Models\Category;
use Tiipiik\Catalog\Models\Product;
use Tiipiik\Catalog\Models\Store;
use Agroo\Searchengine\Models\Task as Task;
use Tiipiik\Catalog\Models\CustomField;
use DB;
use Session;

class ProductFilters extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'ProductFilters',
            'description' => 'Product Filters'
        ];
    }

    public function defineProperties()
    {
        return ['pagination' => [
             'title'             => 'Number of Products',
             'description'       => 'Number of Products to Show',
             'default'           => '5',
             'type'              => 'string',
             'validationPattern' => '^[0-9]+$',
             'validationMessage' => 'The Default Limit property can contain only numeric symbols'
            ]
        ];
    }

   public function onRun()
   {
        Session::put("searchtype", '');

       $this->page['scope'] = 'products';
       if (isset($_GET["scope"]))
       {
           $this->page['scope'] = $_GET["scope"];

           if ($_GET["scope"] == 'products') {

               $this->initvalues();                
           }elseif ($_GET["scope"] == 'stores') {
               $this->initstorevalues();
           }else{
				$this->initvalues();
			 }
       }else{
           $this->initvalues();
       }

   }
   
    //STORES function
    public function initstorevalues(){

      $this->page['deliveryareas'] = $this->getstoredeliveryareas(36); // 36
      $this->page['storeareas'] = $this->getstoreareas(30);
      $result = Store::with('customfields')->whereIsActivated(1);
      $this->mapstores = Store::with('customfields')->whereIsActivated(1)->get();
      $result = $this->Pagination($result, 0);
      $this->stores = $result->orderBy('products_number', 'desc')->get();
   }

    public function onGetStorefilters(){

      $result = Store::with('customfields')->whereIsActivated(1);
      $mapstores = Store::with('customfields')->whereIsActivated(1);
      $mapstores = $this->StoreFilters($mapstores);
      $this->mapstores = $mapstores->get();
      $result = $this->StoreFilters($result);
      $result = $this->Pagination($result, (post('page') - 1));
      $this->stores = $result->orderBy('products_number', 'desc')->get();
	
	}

   
    public function onGetfilters(){

        $result = Product::where('is_published', '=', '1')->with(array('stores'=>function($query){
       $query->with('customfields');
}))->with('categories')->with('customfields')->leftJoin('fotis_reviews_prod_rates', 'tiipiik_catalog_products.id', '=', 'fotis_reviews_prod_rates.product_id');

        $result = $this->Filters($result);
        $result = $this->Search($result);

        $this->proproducts = $result->get();
        if ( post('page') == 'all' ){

        }else{
            $result = $this->Pagination($result, (post('page') - 1));
        }

        $this->products = $result->get();

        return $result;
    }


   public function Search($result){

        $temp = Product::where('is_published', '=', '1')->with('categories')->with('customfields')->leftJoin('fotis_reviews_prod_rates', 'tiipiik_catalog_products.id', '=', 'fotis_reviews_prod_rates.product_id');
        $temp2 = Product::where('is_published', '=', '1')->with('categories')->with('customfields')->leftJoin('fotis_reviews_prod_rates', 'tiipiik_catalog_products.id', '=', 'fotis_reviews_prod_rates.product_id');
        $this->page['issearch'] = false;
        if (isset($_GET["query"]) && (Session::get("searchtype")) != 'ajax')
        {
            $this->page['issearch'] = true;
            $this->page['myquery'] = $_GET["query"];
            $q = $_GET["query"];

            $query = new Task;
            $query->quary = $q;
            $query->save();
            
            $this->page['queryterm'] = $q;

            $searchTerms = explode(' ', $q);
            
            $temp2 -> where(function($query) use($q)
                {
                    $query->orWhere('slug', 'LIKE', '%'. trim($q) .'%')->orWhere('title', 'LIKE', '%'. trim($q) .'%')->orWhere('description', 'LIKE', '%'. trim($q) .'%');
                });

            $temp -> where(function($query) use($searchTerms)
                {
                    foreach($searchTerms as $term)
                    {
                        if ($term != '' || $term != null){
                        $query->orWhere('slug', 'LIKE', '%'. $term .'%')->orWhere('title', 'LIKE', '%'. $term .'%')->orWhere('description', 'LIKE', '%'. $term .'%');
                        }
                    }
                });
                
            $this->page['searchresults'] = 0;
           if (sizeof( $temp2->get()) > 0 ) {

               $result = $temp2;
               $this->page['searchresults'] = sizeof( $temp2->get());

           }elseif (sizeof( $temp->get()) > 0 ){

               $result = $temp;
               $this->page['searchresults'] = sizeof( $temp->get());
               
           }
                
        }

        return $result;

    }

    function AlaxSearch($result, $ajaxsearch){

        Session::put("searchtype", 'ajax');
        $temp = Product::where('is_published', '=', '1')->with('categories')->with('customfields')->leftJoin('fotis_reviews_prod_rates', 'tiipiik_catalog_products.id', '=', 'fotis_reviews_prod_rates.product_id');
        $temp2 = Product::where('is_published', '=', '1')->with('categories')->with('customfields')->leftJoin('fotis_reviews_prod_rates', 'tiipiik_catalog_products.id', '=', 'fotis_reviews_prod_rates.product_id');
        $this->page['issearch'] = false;
        // trigger_error('error');
          $this->page['issearch'] = true;
            $this->page['myquery'] = $ajaxsearch;
            $q = $ajaxsearch;

            $query = new Task;
            $query->quary = $q;
            $query->save();
            
            $this->page['queryterm'] = $q;

            $searchTerms = preg_split( "/(,| )/", $q ); // explode(',', $q);
            
            $temp2 -> where(function($query) use($q)
                {
                    $query->orWhere('slug', 'LIKE', '%'. trim($q) .'%')->orWhere('title', 'LIKE', '%'. trim($q) .'%')->orWhere('description', 'LIKE', '%'. trim($q) .'%');
                });

            $temp -> where(function($query) use($searchTerms)
                {
                    foreach($searchTerms as $term)
                    {
                        if ($term != '' || $term != null){
                        $query->orWhere('slug', 'LIKE', '%'. trim($term) .'%')->orWhere('title', 'LIKE', '%'. trim($term) .'%')->orWhere('description', 'LIKE', '%'. trim($term) .'%');
                        }
                    }
                });
                
            $this->page['searchresults'] = 0;
           if (sizeof( $temp2->get()) > 0 ) {

               $result = $temp2;
               $this->page['searchresults'] = sizeof( $temp2->get());

           }elseif (sizeof( $temp->get()) > 0 ){

               $result = $temp;
               $this->page['searchresults'] = sizeof( $temp->get());
               
           }
        
        return $result;
    }

    public function StoreFilters($result){

        $data = post('filters');

        if (isset($data)){
            
            foreach ($data as $object) {
                switch ($object['Name']) {

                    case "FilterNameSort":

                        if ($object['Value'] == "asc") {
                            $result->orderBy('name', 'asc');
                        }else{
                            $result->orderBy('name', 'desc');
                        }
                        break;

                    case "deliveryareas":

                       $area = $object['Value'];
                       $result ->whereHas('customfields', function($q) use($area)
                               {
                                  if ($area != 'Σε όλη την Ελλάδα') {
                                    $q -> where('custom_field_id', '=', '36')-> where(function($query) use($area)
                                        {
                                            $query -> orWhere('value', 'LIKE', '%'. $area .'%')->orWhere('value', 'LIKE', '%Σε όλη την Ελλάδα%')->orWhere('value', 'LIKE', '%Σε όλη την Αττική%');
                                        });
                                  }else{
                                    $q -> where('custom_field_id', '=', '36')-> where(function($query) use($area)
                                        {
                                            $query -> orWhere('value', 'LIKE', '%'. $area .'%')->orWhere('value', 'LIKE', '%Σε όλη την Ελλάδα%');
                                        });
                                  }
                                });
                       break;

                       case "storeareas":

                       $storearea = $object['Value'];
                       $result ->whereHas('customfields', function($q) use($storearea)
                              {
                                $q -> where('custom_field_id', '=', '30')-> where(function($query) use($storearea)
                                    {
                                        $query -> orWhere('value', 'LIKE', '%'. $storearea .'%');
                                    });
                              });
                       break;

                    case "categories":

                        if ($object['Value'] == 'with') {
                            $result ->whereHas('customfields', function($q)
                              {
                                $q -> where('custom_field_id', '=', '31')-> where(function($query)
                                    {
                                        $query -> orWhere('value', '>', 0);
                                    });
                              });
                        }elseif ($object['Value'] == 'without') {
                            $result ->whereHas('customfields', function($q)
                              {
                                $q -> where('custom_field_id', '=', '31')-> where(function($query)
                                    {
                                        $query -> orWhere('value', '=', '');
                                    });
                              });
                        }
                        
                        break;
                }
            }

        }

         return $result;

    }

   public function Filters($result){

       $data = post('filters');
       $sort = false;

       if (isset($data)){
           
           foreach ($data as $object) {
               switch ($object['Name']) {

                   case "FilterPrice":

                       $result->where('price', '<=', $object['Value']);
                       break;

                   case "FilterSI":

                       $result -> where(function($query) use($object)
                       {
                           if ($object['Value'] == 0) {
                               $query->orwhere('index_rating', '=', null)->orwhere('index_rating', '>', $object['Value']);
                           }else{
                               $query->orwhere('index_rating', '>=', $object['Value']);
                           }
                       });
                       break;

                   case "FilterPriceSort":

                       if ($object['Value'] == "asc") {
                           $result->orderBy('price', 'asc');
                       }else{
                           $result->orderBy('price', 'desc');
                       }
                       $sort = true;
                       break;

                   case "FilterNameSort":

                       if ($object['Value'] == "asc") {
                           $result->orderBy('title', 'asc');
                       }else{
                           $result->orderBy('title', 'desc');
                       }
                       $sort = true;
                       break;

                   case "deliveryareas":

                       $result = $this->filterbystorecustomfields($result, $object['Value']);
                       break;

                   case "categories":

                       $result ->whereHas('categories', function($q) use($object)
                               {
                                   $q->where('name', '=', $object['Value']);
                               });
                       
                       break;

                    case "searchcheckbox":

                        if ( is_string($object['Value']) ) {

                            $result = $this->AlaxSearch($result, $object['Value']);
                        }

                    break;
               }
           }

       }else{

         $result->orderBy('index_rating', 'desc');
         $sort = true;
       }
       if (!$sort) {
         $result->orderBy('index_rating', 'desc');
       }

        return $result;

   }

    public function filterbystorecustomfields($result, $area){

        $store_ids = DB::table('tiipiik_catalog_custom_values')->select('store_id')->where('custom_field_id', '=', '36');
        $store_ids -> where(function($query) use($area)
        {
          // $query -> orWhere('value', 'LIKE', '%'. $area .'%')->orWhere('value', 'LIKE', '%Σε όλη την Ελλάδα%');
          if ($area != 'Σε όλη την Ελλάδα') {

            $query -> orWhere('value', 'LIKE', '%'. $area .'%')->orWhere('value', 'LIKE', '%Σε όλη την Ελλάδα%')->orWhere('value', 'LIKE', '%Σε όλη την Αττική%');

          }else{

            $query -> orWhere('value', 'LIKE', '%'. $area .'%')->orWhere('value', 'LIKE', '%Σε όλη την Ελλάδα%');

          }
        });
        $store_ids = $store_ids ->get();

        $product_ids = DB::table('tiipiik_catalog_products_stores')->select('product_id');
        foreach ($store_ids as $store_id) {
           $product_ids -> orWhere('store_id', '=', $store_id->store_id);
        }
        $product_ids = $product_ids ->get();

        $result -> where(function($query) use($product_ids)
        {
            foreach ($product_ids as $product_id) {
                $query -> orWhere('id', '=', $product_id->product_id);
            }
        });

        return $result;
    }
   
   //pagination
   public function Pagination($result, $page){

       $this->page['selectedpage'] = $page = post('page') ? post('page') : 1;
       $this->page['totalcount'] = $totalcount = count($result->get());
       $perpage = $this->property('pagination');

       $temp = array();
       $i=0;
       for ($i=0; $i < $totalcount; $i = $i + $perpage) { 
           
           $temp[] = '';
       }
       $this->page['lastpage'] = sizeof($temp);

       if ( $page == -1 ){

           $this->page['end'] = $totalcount;
       }else{

           $page = $page - 1;
           $this->page['pages'] = $temp;
           $this->page['start'] = $start = $page * $perpage;
           $this->page['end'] = $end = $start + $perpage;

           $result ->skip($start)->take($perpage);
       }

       $this->page['count'] = count($result->get());
       $this->page['pageresults'] = count($result->get());

       return $result;

   }


    public function initvalues(){

        $this->page['maxprice'] = Product::where('is_published', '=', '1')->max('price');
        $this->page['pricevalue'] = round(Product::where('is_published', '=', '1')->avg('price'));
        $this->page['SIvalue'] = round(Product::where('is_published', '=', '1')->leftJoin('fotis_reviews_prod_rates', 'tiipiik_catalog_products.id', '=', 'fotis_reviews_prod_rates.product_id')->avg('index_rating'));
        $this->page['deliveryareas'] = $this->getstoredeliveryareas(36); // 36
        $this->page['categories'] = $this->getcategories();
        //$result = Product::where('is_published', '=', '1')->with('categories')->with('customfields')->leftJoin('fotis_reviews_prod_rates', 'tiipiik_catalog_products.id', '=', 'fotis_reviews_prod_rates.product_id');
        $result = Product::where('is_published', '=', '1')->with(array('stores'=>function($query){
       $query->with('customfields');
   }))->with('categories')->with('customfields')->leftJoin('fotis_reviews_prod_rates', 'tiipiik_catalog_products.id', '=', 'fotis_reviews_prod_rates.product_id');
        $result = $this->Search($result);

        if (isset($_GET["categoryset"]))
        {
            $categoryset = $_GET["categoryset"];
            $this->page['categoryset'] = $categoryset;
            $result ->whereHas('categories', function($q) use($categoryset)
                {
                    $q->where('name', '=', $categoryset);
                });
        }

        if (isset($_GET["areaset"]))
        {
            $areaset = $_GET["areaset"];
            $this->page['areaset'] = $areaset;
            $result ->whereHas('customfields', function($q) use($areaset)
                    {
                        $q->where('value', '=', $areaset);
                    });
        }

        if (isset($_GET["deliveryareaset"]))
        {
            $deliveryareaset = $_GET["deliveryareaset"];
            $this->page['deliveryareaset'] = $deliveryareaset;
            $result = $this->filterbystorecustomfields($result, $deliveryareaset);
        }

        $this->proproducts = $result->get();

        $result = $this->Pagination($result, 0);

        $result = $result->orderBy('index_rating', 'desc')->get();

        $this->products = $result;

        return $result;
    }

    public function getcategories(){

        $product_ids = DB::table('tiipiik_catalog_products')->select('id')->where('is_published', '=', '1')->get();

        $categories_ids = DB::table('tiipiik_catalog_prods_cats');
        $categories_ids -> where(function($query) use($product_ids)
            {
                foreach($product_ids as $product_id)
                {
                    $query->orWhere('product_id', '=', $product_id->id);
                }
            })->select(DB::raw('category_id , count(category_id) as c'))->groupBy('category_id')->orderBy('c', 'desc')->get();
        $temp = $categories_ids->lists('category_id');

        $categories =  DB::table('tiipiik_catalog_categories')-> where(function($query) use($temp)
            {
                foreach($temp as $temp1)
                {
                    $query->orWhere('id', '=', $temp1);
                }
            })->get();

        return $categories;
    }

    public function getstoredeliveryareas($customfield){

        $areas = array();
        $product_ids = DB::table('tiipiik_catalog_stores')->select('id')->where('is_activated', '=', '1')->get();

        $custom = DB::table('tiipiik_catalog_custom_values')->where('custom_field_id', '=', $customfield);

        $custom -> where(function($query) use($product_ids)
            {
                foreach($product_ids as $product_id)
                {
                    $query->orWhere('store_id', '=', $product_id->id);
                }
            });

        // $custom -> select(DB::raw('value , count(value) as c'))->groupBy('value')->orderBy('c', 'desc');

        $result = $custom->get();

        foreach ($result as $key => $value) {

           if (($value->value != null) || ($value->value != '')) {
               $temps = explode(',', $value->value);
               foreach ($temps as $temp) {
                   $areas[] = trim($temp);
               }
           }
        }

        return array_unique($areas);
    }

        public function getstoreareas($customfield){

        $areas = array();
        $product_ids = DB::table('tiipiik_catalog_stores')->select('id')->where('is_activated', '=', '1')->get();

        $custom = DB::table('tiipiik_catalog_custom_values')->where('custom_field_id', '=', $customfield);

        $custom -> where(function($query) use($product_ids)
            {
                foreach($product_ids as $product_id)
                {
                    $query->orWhere('store_id', '=', $product_id->id);
                }
            });

        // $custom -> select(DB::raw('value , count(value) as c'))->groupBy('value')->orderBy('c', 'desc');

        $result = $custom->get();

        foreach ($result as $key => $value) {

           if (($value->value != null) || ($value->value != '')) {
              $temps = explode(',', $value->value);
              $areas[] = trim($temps[1]);
           }
        }

        return array_unique($areas);
    }
}
