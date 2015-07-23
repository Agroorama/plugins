<?php namespace Fotis\External;

use System\Classes\PluginBase;

/**
 * External Plugin Information File
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
            'name'        => 'External',
            'description' => 'No description provided yet...',
            'author'      => 'fotis',
            'icon'        => 'icon-leaf'
        ];
    }

    public function registerComponents()
    {
        return [
            'fotis\external\components\ReadArticle' => 'ReadArticle'
        ];
    }
}
