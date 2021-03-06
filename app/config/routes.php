<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app.config
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/views/pages/home.ctp)...
 */
	Router::connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));

/**
 * ...and connect the rest of 'Pages' controller's urls.
 */
	Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));

/** 
 *  Reroute versions to Appcasting
 */
  Router::connect('/appcasting', array('controller' => 'versions', 'action' => 'index'));
  Router::connect('/appcasting/add', array('controller' => 'versions', 'action' => 'add'));
  Router::connect('/appcasting/archive', array('controller' => 'versions', 'action' => 'archive'));
  Router::connect('/appcasting/track', array('controller' => 'tracks', 'action' => 'edit'));
  Router::connect('/appcasting/:safename/feed', array('controller' => 'versions', 'action' => 'appcast'), array('id' => '[a-zA-Z]+', 'pass' => array('safename')));

/**
 * Paypal IPN plugin
 */
  Router::connect('/paypal_ipn/process', array('plugin' => 'paypal_ipn', 'controller' => 'instant_payment_notifications', 'action' => 'process'));
  /* Optional Route, but nice for administration */
  Router::connect('/paypal_ipn/:action/*', array('admin' => 'true', 'plugin' => 'paypal_ipn', 'controller' => 'instant_payment_notifications', 'action' => 'index'));

/**
 * Provide hudson an endpoint to pull new version information
 */
  Router::connect('/versions/hudson', array('controller' => 'versions', 'action' => 'hudson', '[method]' => 'POST'));
  Router::parseExtensions();
