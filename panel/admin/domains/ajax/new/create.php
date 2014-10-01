<?php

/*
    PufferPanel - A Minecraft Server Management Panel
    Copyright (c) 2013 Dane Everitt

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see http://www.gnu.org/licenses/.
    Chomiciak's file :D
 */
session_start();
require_once('../../../../../src/framework/framework.core.php');

if($core->auth->isLoggedIn($_SERVER['REMOTE_ADDR'], $core->auth->getCookie('pp_auth_token'), null, true) !== true){
	Page\components::redirect('../../../index.php');
}

//Cookies :3
setcookie("__TMP_pp_admin_newserver", json_encode($_POST), time() + 30, '/', $core->settings->get('cookie_website'));

/*
* Set Values
*/
@$_POST['server_port'] = $_POST['server_port_'.str_replace('.', '_', $_POST['server_ip'])];

/*
 * Are they all Posted?
 */
if(!isset($_POST['domain_name'], $_POST['api_email'], $_POST['api_key'], $_POST['blacklist'], $_POST['input_form'], $_POST['allow_websites']))
	Page\components::redirect('../../add.php?disp=missing_args&error=na');



$add = $mysql->prepare("INSERT INTO `domains` VALUES(NULL, :name, :cf_email, :cf_api_key, :blacklist, :input, :allow_websites)");
$add->execute(array(
	':name' => $_POST['domain_name'],
	':cf_email' => $_POST['api_email'],
	':cf_api_key' => $_POST['api_key'],
	':blacklist' => $_POST['blacklist'],
	':input' => $_POST['input_form'],
	':allow_websites' => $_POST['allow_websites'],
));

$lastInsert = $mysql->lastInsertId();

Page\components::redirect('../../view.php?id='.$lastInsert);

?>
