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
 * Are they all Posted?
 */
if(!isset($_POST['subdomain_name'], $_POST['domain_name'], $_POST['make_website'], $_POST['webserver_ip'], $_POST['server_id']))
	Page\components::redirect('../../add.php?disp=missing_args&error=na');

/*
 * Select domain data :)
 */

$select = $mysql->prepare("SELECT blacklist FROM `domains` WHERE name='".$_POST['domain_name']."'");
$select->execute(array());
$sel = $select->fetch();
/*
 * Is on Blacklist???
 */
$blacks = explode(",", $sel['blacklist']);
if(in_array($_POST['subdomain_name'], $blacks)){
	Page\components::redirect('../../add.php?disp=name_on_blacklist&error=na');
}


$core->dns->addSubdomain($_POST['subdomain_name'], $_POST['domain_name'], $_POST['make_website'], $_POST['webserver_ip'], $_POST['server_id']);

$lastInsert = $mysql->lastInsertId();



Page\components::redirect('../../viewsub.php?id='.$lastInsert);

?>
