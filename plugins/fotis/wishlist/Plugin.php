<?php namespace Fotis\Wishlist;

use System\Classes\PluginBase;

/**
 * wishlist Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'wishlist',
            'description' => 'Wish list for products',
            'author'      => 'fotis',
            'icon'        => 'icon-bookmark'
        ];
    }

    public function registerComponents()
    {
        return [
            'fotis\wishlist\components\Addtowishlist' => 'AddtoWishList',
            'fotis\wishlist\components\Showwishlist' => 'ShowWishList'
        ];
    }

}
