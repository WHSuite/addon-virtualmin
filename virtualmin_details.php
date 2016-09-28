<?php
namespace Addon\Virtualmin;

class VirtualminDetails extends \App\Libraries\AddonDetails
{
    /**
     * addon details
     */
    protected static $details = array(
        'name' => 'Virtualmin',
        'description' => 'Server Module for Virtualmin',
        'author' => array(
            'name' => 'WHSuite Dev Team',
            'email' => 'info@whsuite.com'
        ),
        'website' => 'http://www.whsuite.com',
        'version' => '1.0.1',
        'license' => 'http://whsuite.com/license/ The WHSuite License Agreement',
        'type' => 'server'
    );

    /**
     * get the addon details
     *
     * @param   int $addon_id   The addons ID within WHSuite database
     * @return  bool
     */
    public function uninstallCheck($addon_id)
    {
        return $this->addon_helper->hostingAddonUninstallCheck($addon_id);
    }
}
