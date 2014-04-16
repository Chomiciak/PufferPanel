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
 */
session_start();
require_once('../../../../../core/framework/framework.core.php');

if($core->auth->isLoggedIn($_SERVER['REMOTE_ADDR'], $core->auth->getCookie('pp_auth_token'), null, true) !== true){
	$core->page->redirect('../../../../index.php');
}

if(!isset($_POST['sid']))
	$core->page->redirect('../../find.php');

/*
 * Validate Disk & Memory
 */	
if(!is_numeric($_POST['alloc_mem']) || !is_numeric($_POST['alloc_disk']))
	$core->page->redirect('../../view.php?id='.$_POST['sid'].'&error=alloc_mem|alloc_disk&disp=m_fail&tab=server_sett');

$mysql->prepare("UPDATE `servers` SET `max_ram` = :ram, `disk_space` = :disk WHERE `id` = :sid")->execute(array(
    ':sid' => $_POST['sid'],
    ':ram' => $_POST['alloc_mem'],
    ':disk' => $_POST['alloc_disk']
));

/* 
 * Select Node, User, & Server Information
 */
$select = $mysql->prepare("SELECT `ftp_user`, `node` FROM `servers` WHERE `id` = ?");
$select->execute(array($_POST['sid']));
    $server = $select->fetch();

$selectNode = $mysql->prepare("SELECT `password`, `encryption_iv` FROM `nodes` WHERE `id` = ?");
$selectNode->execute(array($server['node']));
    $node = $selectNode->fetch();

/*
 * Update Disk Space 
 */
$core->ssh->generateSSH2Connection($node['id'], true)->executeSSH2Command('cd /srv/scripts; sudo ./update_disk.sh '.$server['ftp_user'].' '.($_POST['alloc_disk'] - 1024).' '.$_POST['alloc_disk'], false);

$core->page->redirect('../../view.php?id='.$_POST['sid'].'&tab=server_sett');