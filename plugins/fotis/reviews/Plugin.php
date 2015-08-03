<?php namespace Fotis\Reviews;

use System\Classes\PluginBase;

/**
 * reviews Plugin Information File
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
            'name'        => 'reviews',
            'description' => 'Product Reviews',
            'author'      => 'fotis',
            'icon'        => 'icon-leaf'
        ];
    }

    public function registerComponents()
    {
        return [
            'fotis\reviews\components\Rating' => 'rating'
        ];
    }

}
