<?php namespace Fotis\Filters\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Database\DatabaseManager;
use App;
use Request;
use Redirect;
use Cms\Classes\Page;
use Tiipiik\Catalog\Models\Store;
use DB;

class StoreFilters extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'StoreFilters',
            'description' => 'Store Filters'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        
        $this->stores = Store::get();
    }

    public function onGetfilters(){

        $data = post('filters');

        // $result = Product::leftJoin('fotis_reviews_prod_rates', 'tiipiik_catalog_products.id', '=', 'fotis_reviews_prod_rates.product_id');
        

        if (isset($data)){
            

        //     foreach ($data as $object) {
        //         switch ($object['Name']) {

        //             case "FilterPrice":

        //                 $result = $result->where('price', '<=', $object['Value']);
        //                 break;
        //             case "FilterSI":

        //                 if ($object['Value'] == 0) {
        //                     $result = $result->where('index_rating', '=', null);
        //                 }else{
        //                     $result = $result->where('index_rating', '>=', $object['Value']);
        //                 }
        //                 break;
        //             default:
        //         }
        //     }

        //     $this->products = $result->get();
               $this->stores = Store::orderBy('products_number', 'desc')->take(2)->get();
            
        }else{

                $this->stores = Store::orderBy('products_number', 'asc')->take(2)->get();
        //     $this->products = Product::leftJoin('fotis_reviews_prod_rates', 'tiipiik_catalog_products.id', '=', 'fotis_reviews_prod_rates.product_id')->orderBy('price', 'desc')->get();
        }
    }
}