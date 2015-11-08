<?php

/**
 * @author      Aakif N. <webmaster@nixfifty.com>
 * @copyright   ? 2015 pixelexit.com. All rights reserved.
 * @link        https://pixelexit.com
 * @package		PixelExit_Sucuri
 */

class PixelExit_Sucuri_Route_PrefixAdmin_Sucuri implements XenForo_Route_Interface
{
	/**
	 * Match a specific route for an already matched prefix.
	 *
	 * @see XenForo_Route_Interface::match()
	 */
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		return $router->getRouteMatch('PixelExit_Sucuri_ControllerAdmin_Sucuri', $routePath, 'peSucuri');
	}
}