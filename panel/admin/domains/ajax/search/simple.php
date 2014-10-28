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
require_once('../../../../../src/framework/framework.core.php');

if($core->auth->isLoggedIn($_SERVER['REMOTE_ADDR'], $core->auth->getCookie('pp_auth_token'), null, true) !== true){
	exit('<div class="error-box round">Failed to Authenticate Account.</div>');
}

/*
 * Check Variables
 */
if(!isset($_POST['method'], $_POST['field'], $_POST['operator'], $_POST['term']))
	exit('<div class="error-box round">Missing required variable.</div>');

if($_POST['method'] != 'simple')
	exit('<div class="error-box round">Invalid Search Method.</div>');

if(empty($_POST['field']) || empty($_POST['operator']))
	exit('<div class="error-box round">Required Variable Empty.</div>');

if(!in_array($_POST['field'], array('name', 'domain', 'server', 'enable_website', 'webserver_ip')))
	exit('<div class="error-box round">Required `field` contains unknown value.</div>');

if(!in_array($_POST['operator'], array('equal', 'not_equal', 'starts_w', 'ends_w', 'like')))
	exit('<div class="error-box round">Required `operator` contains unknown value.</div>');

if(strlen($_POST['term']) < 4 && $_POST['field'] != 'active')
	exit('<div class="error-box round">Required `term` must be at least 4 characters.</div>');

if($_POST['field'] == 'active' && !in_array($_POST['term'], array('0', '1')))
	exit('<div class="error-box round">Required `term` for active must be 1 or 0.</div>');

/*
 * Is Search Looking for Similar
 */
if($_POST['operator'] == 'starts_w'){
	$searchTerm = $_POST['term'].'%';
	$useOperator = 'LIKE';
}else if($_POST['operator'] == 'ends_w'){
	$searchTerm = '%'.$_POST['term'];
	$useOperator = 'LIKE';
}else if($_POST['operator'] == 'like'){
	$searchTerm = '%'.$_POST['term'].'%';
	$useOperator = 'LIKE';
}else if($_POST['operator'] == 'not_equal'){
	$searchTerm = $_POST['term'];
	$useOperator = '!=';
}else if($_POST['operator'] == 'equal'){
	$searchTerm = $_POST['term'];
	$useOperator = '=';
}

/*
 * Different Search Method for Owner Email
 */


	$find = $mysql->prepare("SELECT * FROM `subdomains` WHERE `".$_POST['field']."` ".$useOperator." :term");
	$find->execute(array(
		':term' => $searchTerm
	));


	$returnRows = '';
	while($row = $find->fetch()){

		$hasWebsite = ($row['enable_website'] == '1') ? '<span class="label label-success">Enabled</span>' : '<span class="label label-danger">Disabled</span>';

		$row['name'] = (strlen($row['name']) > 20) ? substr($row['name'], 0, 17).'...' : $row['name'];
		$returnRows .= '
		<tr>
			<td><a href="../../../servers.php?goto='.$row['hash'].'"><i class="fa fa-tachometer"></i></a></td>
			<td><a href="view.php?id='.$row['id'].'">'.$row['name'].'</a></td>
			<td><a href="../domains/view.php?id='.$row['domain'].'">'.$row['domain'].'</a></td>
			<td>'.$row['server'].'</td>
			<td style="text-align:center;">'.$isActive.'</td>
			<td>'.$row['webserver_ip'].'</td>
		</tr>
		';

	}

echo '
<table class="table table-striped table-bordered table-hover">
	<thead>
		<tr>
			<th style="width:2%"></th>
			<th>Subdomain name</th>
			<th>Domain</th>
			<th>Server</th>
			<th>Has Website</th>
			<th>Webserver</th>
		</tr>
	</thead>
	<tbody>
		'.$returnRows.'
	</tbody>
</table>';

?>
