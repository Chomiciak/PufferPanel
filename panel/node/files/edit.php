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
require_once('../../core/framework/framework.core.php');

if($core->auth->isLoggedIn($_SERVER['REMOTE_ADDR'], $core->auth->getCookie('pp_auth_token'), $core->auth->getCookie('pp_server_hash')) === false){

	$core->page->redirect($core->settings->get('master_url').'index.php');
	exit();
    
}

$canEdit = array('txt', 'yml', 'log', 'conf', 'html', 'json', 'properties', 'props', 'cfg', 'lang');

if(isset($_POST['file']))
    $_POST['file'] = str_replace('..', '', urldecode($_POST['file']));

if(isset($_POST['dir']))
    $_POST['dir'] = str_replace('..', '', urldecode($_POST['dir']));

$path = $core->server->nodeData('server_dir').$core->server->getData('ftp_user').'/server/';
$parName = 'Editing: /'.$_POST['file'].'';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include('../../assets/include/header.php'); ?>
	<script type="text/javascript" src="<?php echo $core->settings->get('master_url'); ?>assets/javascript/jquery.redirect.min.js"></script>
	<title>PufferPanel - Manage Your Server</title>
</head>
<body>
	<div class="container">
		<?php include('../../assets/include/navbar.php'); ?>
		<div class="row">
			<div class="col-3">
				<div class="list-group">
					<a href="#" class="list-group-item list-group-item-heading"><strong>Account Actions</strong></a>
					<a href="../../account.php" class="list-group-item">Settings</a>
					<a href="../../servers.php" class="list-group-item">My Servers</a>
				</div>
				<div class="list-group">
					<a href="#" class="list-group-item list-group-item-heading"><strong>Server Actions</strong></a>
					<a href="../index.php" class="list-group-item">Overview</a>
					<a href="../console.php" class="list-group-item">Live Console</a>
					<a href="index.php" class="list-group-item active">File Manager <i class="fa fa-spinner fa fa-spin" id="loading_dir" style="display: none;"></i></a>
				</div>
				<div class="list-group">
					<a href="#" class="list-group-item list-group-item-heading"><strong>Server Settings</strong></a>
					
					<a href="../settings.php" class="list-group-item">Server Management</a>
					<a href="../plugins/index.php" class="list-group-item">Server Plugins</a>
				</div>
			</div>
			<div class="col-9" id="load_files">
				<span id="save_status" style="display:none;width: 100%;"></span>
				<?php                        
				if(!isset($_GET['error'])){
				    
				    if(isset($_POST['file'])){
				    
				        if(in_array(pathinfo($path.$_POST['file'], PATHINFO_EXTENSION), $canEdit)){
				                    
				            /*
		                     * Create File Path
		                     */
		                    $file = pathinfo($_POST['file'], PATHINFO_BASENAME);
		                    $directory = dirname($_POST['file']).'/';
		                    
		                    /*
		                     * Directory Cleaning
		                     */
		                    if($directory == './' || $directory == '.')
		                        $directory = '';
		            
		                    if(substr($directory, 0, 1) == '/')
		                        $directory = substr($directory, 1);
		                	                    
		            		$url = "http://".$core->server->nodeData('sftp_ip').":8003/gameservers/".$core->server->getData('gsd_id')."/file/".$directory.$file;
		            		
		            		$context = stream_context_create(array(
		            			"http" => array(
		            				"method" => "GET",
		            				"timeout" => 3
		            			)
		            		));
		            		
		            		$json = json_decode(file_get_contents($url, 0, $context), true);
		                
				                echo '<form method="post" id="editing_file">
										<div class="form-group">
											<label for="email" class="control-label">'.$parName.'</label>
											<div>
												<textarea name="file_contents" id="live_console" style="border: 1px solid #dddddd;" class="form-control console">'.$json['contents'].'</textarea>
											</div>
										</div>
										<div class="form-group">
											<div>
												<input type="hidden" name="file" value="'.$_POST['file'].'" />
												<button class="btn btn-primary btn-sm" id="save_file">Save</button>
												<button class="btn btn-default btn-sm" onclick="window.location=\'index.php?dir='.urlencode('/'.$directory).'\';return false;">Back to File Manager</button>
											</div>
										</div>
				                    </form>';
				            
				        }else{
				        
				            echo '<div class="alert alert-danger">This type of file cannot be edited via our control panel.</div>';
				        
				        }
				    
				    }else{
				    
				        echo '<div class="alert alert-warning">No file was specified that can be edited.</div>';
				    
				    }
				    
				}else{
				    
				    echo (isset($_GET['error'])) ? '<div class="alert alert-danger">'.base64_decode($_GET['error']).' You can return to the file manager by <a href="index.php">clicking here</a>.</div>' : '';
				    
				}
				?>
			</div>
		</div>
		<div class="footer">
			<?php include('../../assets/include/footer.php'); ?>
		</div>
	</div>
	<script type="text/javascript">
	$(window).load(function(){ 
	    $("#save_file").click(function(event){
			event.preventDefault();
			var file = $("input[name='file']").val();
			var content = $("#live_console").val();
			$("#save_file").append(' <i class="fa fa-spinner fa fa-spin"></i>').addClass('disabled');
			
			$.ajax({
				type: "POST",
				url: '../ajax/files/save.php',
				data: {'file': file, 'file_contents': content},
				success: function(data) {
					$("#save_status").html(data);
					$("#save_file").html('Save').removeClass('disabled');
					$("#save_status").slideDown();
//					/.delay(2500).slideUp()
				}
			});
		});
	});
	</script>
</body>
</html>