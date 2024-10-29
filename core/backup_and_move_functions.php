<?php

/****************** To extract complete database **************************/

function backup_and_move_backup_tables($destination)
{
	global $wpdb;
	$tables =  $wpdb->get_col('SHOW TABLES');
	foreach($tables as $table)	{
		$result = $wpdb->get_results('SELECT * FROM '. $table,ARRAY_N);
		$num_fields = count($result[0]);
		$total_elements=count($result);
		$return.= '';
		$row2 = $wpdb->get_row('SHOW CREATE TABLE '. $table,ARRAY_N);
		$return.= "\n\n".$row2[1].";\n\n";
		$ct=0;
		
		while($ct<$total_elements){

			$row=$result[$ct];
			$return.= 'INSERT INTO '.$table.' VALUES(';
			for($j=0; $j<$num_fields; $j++){

				$row[$j] = addslashes($row[$j]);
				$row[$j] = ereg_replace("\n","\\n",$row[$j]);

				if (isset($row[$j])) {
					$return.= '"'.$row[$j].'"' ;
				}
				else {
					$return.= '""';
				}
					
				if ($j<($num_fields-1)) {
					$return.= ',';
				}
			}
			$return.= ");\n";
			$ct++;
		}
		$return.="\n\n";
	}
	
	$handle = fopen($destination.'/database.sql','w+');
	fwrite($handle,$return);
	fclose($handle);
	$wpdb->flush();
	
}



/******* recursive procedure to copy a directory and all its sub-directories *********/

function backup_and_move_full_copy( $source, $target ) 	{
	if (is_readable($source)) {
		if ( is_dir( $source ) ) {
			if( !strstr($source,'wp-backups')){
				@mkdir( $target );
				$d = dir( $source );
				while ( FALSE !== ( $entry = $d->read() ) ) {
					if ( $entry == '.' || $entry == '..' ) {
						continue;
					}
					$Entry = $source . '/' . $entry;
					if ( is_dir( $Entry ) ) {
						backup_and_move_full_copy( $Entry , $target . '/' . $entry );
						continue;
					}
					copy( $Entry, $target . '/' . $entry );
				}

				$d->close();
			}
		}else {
			copy( $source, $target );
		}
	}
}


/****************** recursive procedure to delete a directory **************************/

function backup_and_move_deleteAll($directory, $empty = false)  {
	if(substr( $directory , -1) == "/") {
		$directory = substr( $directory , 0 , -1);
	}

	if(!file_exists( $directory ) || !is_dir( $directory )) {
		return false;
	} elseif( ! is_readable( $directory )) {
		return false;
	} else {
		$directoryHandle = opendir($directory);
			
		while ($contents = readdir($directoryHandle)) {
			if($contents != '.' && $contents != '..') {
				$path = $directory . "/" . $contents;
				if(is_dir($path)) {
					backup_and_move_deleteAll($path);
				} else {
					unlink($path);
				}
			}
		}
			
		closedir( $directoryHandle );

		if($empty == false) {
			if( ! rmdir($directory) ) {
				return false;
			}
		}
		return true;
	}
}

?>