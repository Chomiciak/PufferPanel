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
	Chomiciak's file! :D
 */

//This document contains cloudflare's DNS management functions. It's a simple API for PufferPanel, that reffers to CloudFlare's API.
//
//I love bobers!!!

//OK, now as part of framework :D
class subdomains  {

	use Auth\components, \Database\database;
	
	public function __construct()
		{

			$this->mysql = self::connect();
			$this->settings = new settings();

		}

	public function curl($fields){
		//set POST variables
		$url = 'https://www.cloudflare.com/api_json.html';

		//url-ify the data for the POST
		$fields_string = "";
		foreach($fields as $key=>$value) { 
			//$fields.=($key.'='.$value.'&'); 
			$fields_string = $fields_string.$key."=".$value."&";
		}
		//rtrim($fields_string, '&');

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

		//execute post
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);
	}

	public function addDNSRecord($type, $name, $content, $domain, $key, $email){
		$this->curl(array(
				'a' => urlencode("rec_new"),
				'tkn' => urlencode($key),
				'email' => urlencode($email),
				'z' => urlencode($domain), 
				'type' => urlencode($type),
				'name' => urlencode($name),
				'content' => urlencode($content)
		));
	}

	public function editDNSRecord($type, $name, $content, $domain, $id, $ttl, $service_mode, $key, $email){
		$this->curl(array(
				'a' => urlencode("rec_edit"),
				'tkn' => urlencode($key),
				'id' => urlencode($id),
				'email' => urlencode($email),
				'z' => urlencode($domain), 
				'type' => urlencode($type),
				'name' => urlencode($name),
				'content' => urlencode($content),
				'service_mode' => urlencode($service_mode),
				'ttl' => urlencode($ttl)
		));
	}

	public function removeDNSRecord($id, $domain, $key, $email){
		$this->curl(array(
				'a' => urlencode("rec_delete"),
				'tkn' => urlencode($key),
				'email' => urlencode($email),
				'z' => urlencode($domain), 
				'id' => urlencode($id)
		));

	}

	public function addSubdomain($name, $domain, $make_website, $webserver_ip, $server){
		$select = $this->mysql->prepare("SELECT hostname,server_port FROM `servers` WHERE id='".$server."'");
		$select->execute(array());
		$sel = $select->fetch();
		
		$select2 = $this->mysql->prepare("SELECT cf_email,cf_api_key FROM `domains` WHERE name='".$domain."'");
		$select2->execute(array());
		$sel2 = $select->fetch();
		$key = $sel2['cf_api_key'];
		$email = $sel2['cf_email'];
		/*
		 * Add the SRV record (with priority 10 and weight 5)
		 */
		$this->addDNSRecord("SRV", "_minecraft._tcp.".$name, "10 IN SRV 5 ".$sel['server_port']." ".$sel['hostname'].".", $domain, $key, $email);
		
		/*
		 * If selected, add A/AAAA record.
		 */
		if($make_website == 1 OR $make_website == "1"){
			if(substr_count($webserver_ip, '.') == 3){
				$this->addDNSRecord("A", $name, $webserver_ip, $domain, $key, $email);
			}else{
				$this->addDNSRecord("AAAA", $name, $webserver_ip, $domain, $key, $email);
			}
		}
		
		/*
		 * And the database...
		 */
		$add = $this->mysql->prepare("INSERT INTO `subdomains` VALUES(NULL, :name, :domain, :enable_website, :webserver_ip, :server)");
		$add->execute(array(
			':name' => $_POST['subdomain_name'],
			':domain' => $_POST['domain_name'],
			':enable_website' => $_POST['make_website'],
			':webserver_ip' => $_POST['webserver_ip'],
			':server' => $_POST['server_id']
		));

	}
}
?>
