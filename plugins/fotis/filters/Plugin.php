<?php namespace Fotis\Filters;

use System\Classes\PluginBase;

/**
 * filters Plugin Information File
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
            'name'        => 'filters',
            'description' => 'Filter Products',
            'author'      => 'fotis',
            'icon'        => 'icon-leaf'
        ];
    }

    public function registerComponents()
    {
        return [
            'fotis\filters\components\ProductFilters' => 'ProductFilters',
            'fotis\filters\components\StoreFilters' => 'StoreFilters'
        ];
    }
}
