<?php

/**
* Low Link config file
*
* @package         low-link-ee2_addon
* @author          Lodewijk Schutte ~ Low <low@loweblog.com>
* @link            http://loweblog.com/software/low-link/
*/

if ( ! defined('LOW_LINK_NAME'))
{
	define('LOW_LINK_NAME',    'Low Link');
	define('LOW_LINK_VERSION', '1.0.0');
	define('LOW_LINK_DOCS',    'http://loweblog.com/software/low-link/');
}
 
$config['name']    = LOW_LINK_NAME;
$config['version'] = LOW_LINK_VERSION;
 
$config['nsm_addon_updater']['versions_xml'] = LOW_LINK_DOCS.'feed/';
