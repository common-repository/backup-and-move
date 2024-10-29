<?php
/*
 Plugin Name: Backup and Move Plugin
 Plugin URI: http://www.logiclord.com/backup-and-move/
 Description: This plugin will create complete backup of your wordpress installation in a zip file which can be easily used to recover from a crash or simply shift to a new server.
 Author: Gaurav Aggarwal
 Version: 0.1
 Author URI: http://www.logiclord.com/

 Copyright 2011  Gaurav Aggarwal  (email : admin@logiclord.com)
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, version 2, as
 published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

require_once 'core/backup_and_move_functions.php';
require_once 'core/backup_and_move_zip.php';
require_once 'core/backup_and_move_core.php';

register_activation_hook( __FILE__ , 'backup_and_move_activate' );
add_action( 'admin_menu' , 'backup_and_move_menu' );

function backup_and_move_activate() {
	global $wpdb;

	$table_name = $wpdb->prefix . "backup_and_move";
	if ( $wpdb->get_var( 'SHOW TABLES LIKE ' . $table_name) != $table_name ){
		$sql = 'CREATE TABLE ' . $table_name . ' (
	     bid BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		 bfile TEXT NOT NULL, backup_created_on DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\',
		 backup_created_by VARCHAR(60) NOT NULL)' ;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);

		if (! file_exists( ABSPATH.'wp-backups' ) ) {
			mkdir(ABSPATH.'wp-backups',0755);
			$ourFileHandle = fopen( ABSPATH.'wp-backups/index.php' ,  'w' );
			fclose($ourFileHandle);
		}
		add_option( 'backup_and_move_database_version' , '1.0' );
	}
}


function backup_and_move_page()	{
	?>
<div class="wrap">
<?php screen_icon(); ?>
	<h2>Manage Backups</h2>
	<p>Welcome to the Backup and Move Plugin. You can download and delete,
		already created backups from here.</p>
		<?php
		global $wpdb;
		$result = $wpdb->get_results( 'SELECT * FROM '. $wpdb->prefix . "backup_and_move" , ARRAY_A );
		$total_elements = count( $result );
		if( $total_elements != 0 ){
			?>

	<table width="90%" border="1" cellspacing="5" cellpadding="5">
		<tr>
			<th>Backup ID</th>
			<th>Backup Name</th>
			<th>Created By</th>
			<th>Created On</th>
			<th colspan="2">Options</th>
		</tr>
		<?php
		$ct=0;
			while( $ct < $total_elements ){
				$row = $result[$ct];
				?>
			<tr align="center">
				<td><?php echo $row['bid'];?></td>
				<td><?php echo $row['bfile'];?></td>
				<td><?php echo $row['backup_created_by'];?></td>
				<td><?php echo date( "g:i a, F j, Y",strtotime($row['backup_created_on']));?>
				</td>
				<td><a
					href="<?php echo get_home_url().'/wp-backups/'.$row['bfile']; ?>">Download</a>
				</td>
				<td><a href="#delete_confirm" title="<?php echo $row['bfile'] ;?>">Delete</a>
				</td>
	
			</tr>
			<?php
			$ct++;
			}
		}
		else {
			echo "<h4>No previous backup found.</h4>";
		}
		?>
	</table>
</div>
		<?php
}

function backup_and_move_option()	{
	?>
<div class="wrap">
<?php screen_icon(); ?>
	<h2>Create new backup</h2>
	<p>Welcome to the Backup and Move Plugin. Here you can create a new
		backup or overwrite an existing one.</p>

	<form id="backup-and-move-form">
		<table border="0" cellspacing="5" cellpadding="15">
			<tr>
				<td>Status :</td>
				<td>
					<div id="formStatus">submit the form</div>
				</td>
			</tr>
			<tr>
				<td><label>Backup name :</label></td>
				<td><input name="backup_name" type="text" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input type="checkbox" id="mail_me" name="mail_me" value="send" />
					mail me when process completes.</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" name="submit" value="Backup" /></td>
			</tr>
		</table>
	</form>
</div>
<?php
}

function backup_and_move_menu()		{
	add_menu_page( 'Backup and Move' , 'Backup Move' , 'import' , 'backup_and_move-plugin' , 'backup_and_move_option', home_url().'/wp-content/plugins/backup-and-move/img/backup_and_move.png', 40 );
	add_submenu_page( 'backup_and_move-plugin' , 'Manage backups' , 'Manage backups' , 'import', 'backup_and_move_previous', 'backup_and_move_page');
}

add_action( 'wp_ajax_backup_and_move_overwrite_check' , 'backup_and_move_overwrite_check' );
add_action( 'wp_ajax_backup_and_move_check' , 'backup_and_move_delete' );
add_action( 'wp_ajax_backup_and_move_overwrite' , 'backup_and_move_delete_old' );
add_action( 'wp_ajax_backup_and_move_create_new' , 'backup_and_move_create' );
add_action( 'admin_print_scripts' ,  'backup_and_move_script' );

function backup_and_move_script()	{
	wp_enqueue_script( "backup_and_move_delete_handler_script",path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) ).'/js/delete.js'),array( 'jquery' )  );
}

?>
