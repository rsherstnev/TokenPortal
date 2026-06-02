<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'tokens';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['tokens']                              = 'tokens/index';
$route['tokens/list']                         = 'tokens/list_json';
$route['tokens/create']                       = 'tokens/create';
$route['tokens/update/(:any)']                = 'tokens/update/$1';
$route['tokens/delete/(:any)']                = 'tokens/delete/$1';
$route['tokens/get/(:any)']                   = 'tokens/get/$1';

$route['token_models']                        = 'token_models/list_json';
$route['token_models/list']                   = 'token_models/list_json';
$route['token_models/create']                 = 'token_models/create';
$route['token_models/update/(:any)']          = 'token_models/update/$1';
$route['token_models/delete/(:any)']          = 'token_models/delete/$1';
$route['token_models/get/(:any)']             = 'token_models/get/$1';
$route['token_models/options']                = 'token_models/options';

$route['transfer_history']                    = 'token_transfers/index';
$route['transfer_history/list']               = 'token_transfers/list_json';
$route['transfer_history/act/(:any)']         = 'token_transfers/transfer_act/$1';

$route['statistics']                          = 'statistics/index';
$route['statistics/without_token']            = 'statistics/without_token_list_json';
$route['statistics/multiple_tokens']          = 'statistics/multiple_tokens_list_json';

$route['token_transfers/create/(:any)']       = 'token_transfers/create/$1';
$route['token_transfers/get/(:any)']           = 'token_transfers/get/$1';
$route['token_transfers/update/(:any)']       = 'token_transfers/update/$1';
$route['token_transfers/history/(:any)']      = 'token_transfers/history/$1';

$route['tokens/employee_options']             = 'tokens/employee_options';
