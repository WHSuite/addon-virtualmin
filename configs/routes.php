<?php
/**
 * Routes Configuration
 *
 * This files stores all the routes for the core WHSuite system.
 *
 * @package  WHSuite-Configs
 * @author  WHSuite Dev Team <info@whsuite.com>
 * @copyright  Copyright (c) 2013, Turn 24 Ltd.
 * @license http://whsuite.com/license/ The WHSuite License Agreement
 * @link http://whsuite.com
 * @since  Version 1.0
 */

/**
 * Admin Routes
 */
App::get('router')->attach('/admin', array(
    'name_prefix' => 'admin-',
    'values' => array(
        'sub-folder' => 'admin',
        'addon' => 'virtualmin'
    ),
    'params' => array(
        'id' => '(\d+)'
    ),

    'routes' => array(
        'service-virtualmin-manage' => array(
            'params' => array(
                'service_id' => '(\d+)',
            ),
            'path' => '/client/profile/{:id}/service/{:service_id}/virtualmin/hosting/',
            'values' => array(
                'controller' => 'VirtualminController',
                'action' => 'manageHosting'
            )
        ),
        'service-virtualmin-create' => array(
            'params' => array(
                'service_id' => '(\d+)',
            ),
            'path' => '/client/profile/{:id}/service/{:service_id}/virtualmin/hosting/create/',
            'values' => array(
                'controller' => 'VirtualminController',
                'action' => 'createAccount'
            )
        ),
        'service-virtualmin-suspend' => array(
            'params' => array(
                'service_id' => '(\d+)',
            ),
            'path' => '/client/profile/{:id}/service/{:service_id}/virtualmin/hosting/suspend/',
            'values' => array(
                'controller' => 'VirtualminController',
                'action' => 'suspendAccount'
            )
        ),
        'service-virtualmin-unsuspend' => array(
            'params' => array(
                'service_id' => '(\d+)',
            ),
            'path' => '/client/profile/{:id}/service/{:service_id}/virtualmin/hosting/unsuspend/',
            'values' => array(
                'controller' => 'VirtualminController',
                'action' => 'unsuspendAccount'
            )
        ),
        'service-virtualmin-terminate' => array(
            'params' => array(
                'service_id' => '(\d+)',
            ),
            'path' => '/client/profile/{:id}/service/{:service_id}/virtualmin/hosting/terminate/',
            'values' => array(
                'controller' => 'VirtualminController',
                'action' => 'terminateAccount'
            )
        ),
        'server-virtualmin-manage' => array(
            'params' => array(
                'server_id' => '(\d+)',
            ),
            'path' => '/servers/group/{:id}/server/{:server_id}/virtualmin/',
            'values' => array(
                'controller' => 'VirtualminController',
                'action' => 'manageServer'
            )
        ),
        'server-virtualmin-reboot' => array(
            'params' => array(
                'server_id' => '(\d+)',
            ),
            'path' => '/servers/group/{:id}/server/{:server_id}/virtualmin/reboot/',
            'values' => array(
                'controller' => 'VirtualminController',
                'action' => 'rebootServer'
            )
        ),
        'server-virtualmin-restart-service' => array(
            'params' => array(
                'server_id' => '(\d+)',
                'service' => '(\w+)'
            ),
            'path' => '/servers/group/{:id}/server/{:server_id}/virtualmin/restart/{:service}/',
            'values' => array(
                'controller' => 'VirtualminController',
                'action' => 'restartService'
            )
        ),
    )
));


/**
 * Client Routes
 */

App::get('router')->attach('', array(
    'name_prefix' => 'client-',
    'values' => array(
        'sub-folder' => 'client',
        'addon' => 'virtualmin'
    ),
    'params' => array(
        'id' => '(\d+)'
    ),

    'routes' => array(
        'service-virtualmin-manage' => array(
            'path' => '/virtualmin/manage/{:id}/',
            'values' => array(
                'controller' => 'VirtualminController',
                'action' => 'manageHosting'
            )
        ),
    ),
));
