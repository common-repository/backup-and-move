<?php

/*********** create a fresh backup ****************/

function backup_and_move_create()	{
	
	set_time_limit (60*60);
 
	$bname = isset( $_POST['backup_and_move_name']) ? trim( $_POST['backup_and_move_name'] ) : null;
	$mstatus = isset( $_POST['backup_and_move_mail']) ? trim( $_POST['backup_and_move_mail'] ) : null;
	if( $bname ){
		$filename = $bname.'.zip';
		global $wpdb;
		global $current_user;
		get_currentuserinfo();
		$temp_path = ABSPATH.'wp-backups/'.$bname;
		if( ! file_exists( $temp_path ) )  {
			mkdir($temp_path,0755);
		}
		backup_and_move_full_copy( ABSPATH,$temp_path );
		backup_and_move_backup_tables($temp_path);
		$zip = new backup_and_move_ZipFolder( ABSPATH.'wp-backups/'. $bname .'.zip',$temp_path, '.svn');
		backup_and_move_deleteAll($temp_path);
		$offset_time = date("Y-m-d H:i:s", current_time('timestamp',1) + (3600 * get_option('gmt_offset') ) ) ;
		$wpdb->insert(  $wpdb->prefix . "backup_and_move", array( 'bfile' => $filename, 'backup_created_on' => $offset_time ,'backup_created_by'=>$current_user->user_login ) );
		$wpdb->flush;
		if( strcmp($mstatus,"true") == 0 ){
			//$attachments = array(ABSPATH.'wp-backups/'. $bname .'.zip');
			$headers = 'From: Backup and Move plugin <no-reply@logiclord.com>' ."\r\n";
			$message = 'Backup named :' . $bname . ' was complete at '. date( "g:i a, F j, Y", strtotime($offset_time) ) ;
			wp_mail( $current_user->user_email, 'Backup : ' .$bname. ' completed at '. get_bloginfo('name') , $message , $headers );
		}
	}
	die;
}

/*********** delete page handler ****************/

function backup_and_move_delete()	{
	$delid = isset( $_POST['backup_and_move_delid']) ? trim( $_POST['backup_and_move_delid'] ) : null;
	$delpath = ABSPATH . 'wp-backups/' . $delid ;
	echo backup_and_move_delete_procedure( $delid , $delpath );
	die();
}

/*********** delete a previous backup ****************/

function backup_and_move_delete_procedure( $delid , $delpath )  {
	$msg = "invalid";
	if( $delid && $delpath ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'backup_and_move';
		if( $wpdb->query("DELETE FROM $table_name WHERE bfile= '".$delid."'" ) != 0 )  {
			$msg = "valid" ;
			try {
				unlink($delpath);
			}
			catch(Exception $e){
				error_log( var_dump( $e->getMessage() ) );
				$msg = "invalid";
			}
		}
	}
	return $msg;
}

/*********** overwrite handler ****************/

function backup_and_move_overwrite_check()	{
	$new_name = isset( $_POST['backup_and_move_new']) ? trim( $_POST['backup_and_move_new']) .".zip" : null;
	$msg = "new";
	global $wpdb;
	$table_name = $wpdb->prefix . 'backup_and_move';
	$count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE bfile= '".$new_name."'" ) );
	if($count>0){
		$msg="exists";
	}
	echo $msg;
	die();
}

/*********** overwrite handler ****************/

function backup_and_move_delete_old()	{
	$delid = isset( $_POST['backup_and_move_new']) ? trim( $_POST['backup_and_move_new']).'.zip' : null;
	$delpath = ABSPATH .'wp-backups/' . $delid ;
	echo backup_and_move_delete_procedure( $delid , $delpath );
	die();
}

?>