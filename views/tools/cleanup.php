<?php
	require_once(DUPLICATOR_PLUGIN_PATH . '/views/javascript.php'); 
	require_once(DUPLICATOR_PLUGIN_PATH . '/views/inc.header.php'); 

    $nonce = wp_create_nonce('duplicator_cleanup_page');    
	$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : 'display';
	
	$installer_file 	= DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_PHP;
	$installer_bak		= DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_BAK;
	$installer_sql1  	= DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_SQL;
	$installer_sql2  	= DUPLICATOR_WPROOTPATH . 'database.sql';
	$installer_log  	= DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_LOG;
	
	if(isset($_GET['action']))
	{
		if(($_GET['action'] == 'installer') || ($_GET['action'] == 'legacy') || ($_GET['action'] == 'tmp-cache'))
		{
			$verify_nonce = $_REQUEST['_wpnonce'];
			if ( ! wp_verify_nonce( $verify_nonce, 'duplicator_cleanup_page' ) ) {
				exit; // Get out of here, the nonce is rotten!
			}
		}   
	}
        
	switch ($_GET['action']) {            
		case 'installer' :     
			$action_response = __('Installer file cleanup ran!');
			$css_hide_msg = 'div.dup-header div.error {display:none}';
			break;		
		case 'legacy': 
			DUP_Settings::LegacyClean();			
			$action_response = __('Legacy data removed.');
			break;
		case 'tmp-cache': 
			DUP_Package::TmpCleanup(true);
			$action_response = __('Build cache removed.');
			break;		
	} 
	
?>

<style type="text/css">
	<?php echo isset($css_hide_msg) ? $css_hide_msg : ''; ?>
	div.success {color:#4A8254}
	div.failed {color:red}
	table.dup-reset-opts td:first-child {font-weight: bold}
	table.dup-reset-opts td {padding:10px}
	form#dup-settings-form {padding: 0px 10px 0px 10px}
	a.dup-fixed-btn {min-width: 150px; text-align: center}
	div#dup-tools-delete-moreinfo {display: none; padding: 5px 0 0 20px; border:1px solid silver; background-color: #fff; border-radius: 5px; padding:10px; margin:5px; width:700px }
</style>


<form id="dup-settings-form" action="?page=duplicator-tools&tab=cleanup" method="post">
	
	<?php if ($_GET['action'] != 'display')  :	?>
		<div id="message" class="updated below-h2">
			<p><?php echo $action_response; ?></p>
			<?php if ( $_GET['action'] == 'installer') :  ?>
			
			<?php	
				$html = "";

				$package_name   	= (isset($_GET['package'])) ? DUPLICATOR_WPROOTPATH . esc_html($_GET['package']) : '';
				
				//Uncommon to see $installer_sql2 so don't display message
				$html .= (@unlink($installer_file)) ?  "<div class='success'>Successfully removed {$installer_file}</div>"	:  "<div class='failed'>Does not exist or unable to remove file: {$installer_file}</div>";
				$html .= (@unlink($installer_bak))  ?  "<div class='success'>Successfully removed {$installer_bak}</div>"	:  "<div class='failed'>Does not exist or unable to remove file: {$installer_bak}</div>";
				$html .= (@unlink($installer_sql1)) ?  "<div class='success'>Successfully removed {$installer_sql1}</div>"  :  "<div class='failed'>Does not exist or unable to remove file: {$installer_sql1}</div>";
				$html .= (@unlink($installer_sql2)) ?  "<div class='success'>Successfully removed {$installer_sql2}</div>"  :  "<div class='failed'>Does not exist or unable to remove file: {$installer_sql2}</div>";
				$html .= (@unlink($installer_log))  ?  "<div class='success'>Successfully removed {$installer_log}</div>"	:  "<div class='failed'>Does not exist or unable to remove file: {$installer_log}</div>";

				//No way to know exact name of archive file except from installer.
				//The only place where the package can be remove is from installer
				//So just show a message if removing from plugin.
				if (! empty($package_name) ){
					$path_parts = pathinfo($package_name);
					$path_parts = (isset($path_parts['extension'])) ? $path_parts['extension'] : '';
					if ($path_parts  == "zip"  && ! is_dir($package_name)) {
						$html .= (@unlink($package_name))   
							?  "<div class='success'>Successfully removed {$package_name}</div>"   
							:  "<div class='failed'>Does not exist or unable to remove archive file.</div>";
					} else {
						$html .= "<div class='failed'>Does not exist or unable to remove archive file.  Please validate that an archive file exists.</div>";
					}
				} else {
					$html .= '<div>It is <u>recommended</u> to remove your archive file from the root of your WordPress install.  This will need to be done manually.</div>';
				}

				echo $html;
			 ?>
			
			<i> <br/>
			 <?php DUP_Util::_e('If the installer files did not successfully get removed, then you WILL need to remove them manually')?>. <br/>
			 <?php DUP_Util::_e('Please remove all installer files to avoid leaving open security issues on your server')?>. <br/><br/>
			</i>
			
		<?php endif; ?>
		</div>
	<?php endif; ?>	
	

	<h3><?php DUP_Util::_e('Data Cleanup')?><hr size="1"/></h3>
	<table class="dup-reset-opts">
		<tr style="vertical-align:text-top">
			<td>
				<a class="button button-small dup-fixed-btn" href="?page=duplicator-tools&tab=cleanup&action=installer&_wpnonce=<?php echo $nonce; ?>">
					<?php DUP_Util::_e("Delete Reserved Files"); ?>
				</a>
			</td>
			<td>
				<?php DUP_Util::_e("Removes all reserved installer files."); ?>
				<a href="javascript:void(0)" onclick="jQuery('#dup-tools-delete-moreinfo').toggle()">[<?php DUP_Util::_e("more info"); ?>]</a>
				<br/>
				<div id="dup-tools-delete-moreinfo">
					<?php

						DUP_Util::_e("Duplicator will attempt to removed the following reserved files.  These files are typically from a previous Duplicator install, "
								. "but may be from other sources. If you are unsure of the source, please validate the files.  These files should never be left on "
								. "production systems as they can leave a security hole for your site.");
						
						echo "<br/><br/>"
							. "<div>{$installer_file}</div>"
							. "<div>{$installer_bak}</div>"
							. "<div>{$installer_sql1}</div>" 
							. "<div>{$installer_sql2}</div>"
							. "<div>{$installer_log}</div>";
					?>
				</div>
			</td>
		</tr>
		<tr>
			<td><a class="button button-small dup-fixed-btn" href="javascript:void(0)" onclick="Duplicator.Tools.DeleteLegacy()"><?php DUP_Util::_e("Delete Legacy Data"); ?></a></td>
			<td><?php DUP_Util::_e("Removes all legacy data and settings prior to version"); ?> [<?php echo DUPLICATOR_VERSION ?>].</td>
		</tr>
				<tr>
			<td><a class="button button-small dup-fixed-btn" href="javascript:void(0)" onclick="Duplicator.Tools.ClearBuildCache()"><?php DUP_Util::_e("Clear Build Cache"); ?></a></td>
			<td><?php DUP_Util::_e("Removes all build data from:"); ?> [<?php echo DUPLICATOR_SSDIR_PATH_TMP ?>].</td>
		</tr>	
	</table>

	
</form>

<script>	
jQuery(document).ready(function($) {
	

   Duplicator.Tools.DeleteLegacy = function () {
	   <?php
		   $msg  = __('This action will remove all legacy settings prior to version %1$s.  ');
		   $msg .= __('Legacy settings are only needed if you plan to migrate back to an older version of this plugin.'); 
	   ?>
	   var result = true;
	   var result = confirm('<?php printf(__($msg), DUPLICATOR_VERSION) ?>');
	   if (! result) 
		   return;
		
	   window.location = '?page=duplicator-tools&tab=cleanup&action=legacy&_wpnonce=<?php echo $nonce; ?>';
   }
   
   Duplicator.Tools.ClearBuildCache = function () {
	   <?php
		   $msg  = __('This process will remove all build cache files.  Be sure no packages are currently building or else they will be cancelled.');
	   ?>
	   var result = true;
	   var result = confirm('<?php echo $msg ?>');
	   if (! result) 
		   return;
               
	   window.location = '?page=duplicator-tools&tab=cleanup&action=tmp-cache&_wpnonce=<?php echo $nonce; ?>';
   }   
  
	
});	
</script>

