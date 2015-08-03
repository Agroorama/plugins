<?php namespace Agroo\Buy\Components;

use Cms\Classes\ComponentBase;
use Agroo\Buy\Models\Task;

use Mail;
use Redirect;
use DB;

class Buy extends ComponentBase
{
	
	private $data;
	public $coupon_exists;
	public $user;
	
	
    public function componentDetails()
    {
        return [
            'name'        => 'Buy Component',
            'description' => 'Insert Buy Button'
        ];
    }

    public function defineProperties()
    {
        return [];
    }
	
	
	public function onBuyServiceNow(){
	
		/*General*/
		$id = post('id');
		$email = post('email');
		$image = post('image');
		$userAddress = post('userAddress');
		$storeAddress = post('storeAddress');
		$price = post('price');
		$title = post('title');
		
		//Service Details
		$phone = post('phone');
		$emailStore = post('emailStore');
		$items_bought = post('items');
		
		//Items Decrement
		$items = DB::table('tiipiik_catalog_products')->where('id', $id)->decrement('items_available');

		DB::table('fotis_reviews_prod_rates')->where('product_id', $id)->increment('buy'); // new
		
		//Send to database
		$task = new Task;
		$task->product_id = $id;
		$task->email = $email;
		$task->title = $title;
		$task->image = $image;
		$task->userAddress = $userAddress;
		$task->storeAddress = $storeAddress;
		$task->description = $items_bought;
		$task->discount_price = $price;
		$task->phone = $phone;
		$task->save();
				
		//Send Email
				$this->data = [ 
				'title' => $title,
				'email' => $emailStore,
				'emailUser' => $email,
				'price' => $price,
				'items' => $items_bought,
				'couponcode' => $couponcode,
				'phone' => $phone,
				'userAddress' => $userAddress
				];
		
			Mail::send('buycouponStore', $this->data, function($m){
					
					$m->to($this->data['email'])->subject($this->data['title']);
					
					});
			
		return Redirect::to('confirmservicezvS0mv0ImRg3fz91');
			
	}
	
	public function onExample(){
		
		
		}
	

}
