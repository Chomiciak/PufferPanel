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

$find = $mysql->prepare("SELECT * FROM `subdomains`");
$find->execute();

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
