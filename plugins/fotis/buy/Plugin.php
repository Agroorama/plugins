<?php namespace Agroo\Buy;

use System\Classes\PluginBase;

/**
 * Buy Plugin Information File
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
            'name'        => 'Buy',
            'description' => 'Buy Button',
            'author'      => 'Agroo',
            'icon'        => 'icon-tag'
        ];
    }
    
    public function registerComponents()
    {
        return [
            'Agroo\Buy\Components\Buy' => 'buyButton'
        ];
    }

}
