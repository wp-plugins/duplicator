<?php

function duplicator_package_create() {
	
	@set_time_limit(0);

	$errLevel = error_reporting();
	error_reporting(E_ERROR);
	DUP_Util::InitSnapshotDirectory();

	$Task = new DUP_Task();

	$Task->Create();
	
	
	//JSON:Debug Response
	//Pass = 1, Warn = 2, Fail = 3
	$json = array();
	$json['Package'] = $Task->Package;
    $json['Status']  = 1;
	$json['Runtime']  = $Task->Package->Runtime;
	$json['ExeSize']  = $Task->Package->ExeSize;
	$json['ZipSize']  = $Task->Package->ZipSize;
	$json_response = json_encode($json);
	
	error_reporting($errLevel);
    die($json_response);
}


/**
 *  DUPLICATOR_PACKAGE_SCAN
 *  Returns the directory size and file count for the root directory minus
 *  any of the filters
 *  
 *  @return json   size and file count of directory
 *  @example	   to test: admin-ajax.php?action=duplicator_package_scan
 *  
 */
function duplicator_package_scan() {
	
	@set_time_limit(0);
	
	$json = array();
	$Package = new DUP_Package();
	$Package = $Package->GetActive();
	
	//SERVER
	$srv = $Package->GetServerChecks();
	$json['SRV']['OpenBase'] = $srv['CHK-SRV-100'];
	$json['SRV']['CacheOn']  = $srv['CHK-SRV-101'];
	$json['SRV']['TimeOuts'] = $srv['CHK-SRV-102'];

	//DATABASE
	$db = $Package->Database->Stats();
	$json['DB']['Status']		= $db['Status'];
	$json['DB']['Size']			= DUP_Util::ByteSize($db['Size'])	or "unknown";
	$json['DB']['Rows']			= number_format($db['Rows'])		or "unknown";
	$json['DB']['TableCount']	= $db['TableCount']					or "unknown";
	$json['DB']['TableList']	= $db['TableList']					or "unknown";
	
	//FILES
	$Package->Archive->GetStats();
	$json['ARC']['Size']		= DUP_Util::ByteSize($Package->Archive->Size)  or "unknown";
	$json['ARC']['DirCount']	= empty($Package->Archive->DirCount)  ? '0' : number_format($Package->Archive->DirCount);
	$json['ARC']['FileCount']	= empty($Package->Archive->FileCount) ? '0' : number_format($Package->Archive->FileCount);
	$json['ARC']['LinkCount']	= empty($Package->Archive->LinkCount) ? '0' : number_format($Package->Archive->LinkCount);
	$json['ARC']['InvalidFiles']	= is_array($Package->Archive->InvalidFileList) ? $Package->Archive->InvalidFileList : "unknown";
	$json['ARC']['BigFiles']		= is_array($Package->Archive->BigFileList)  ? $Package->Archive->BigFileList  : "unknown";
	$json['ARC']['Status']['Size']	= ($Package->Archive->Size > DUPLICATOR_SCAN_SITE) ? 'Warn' : 'Good';
	$json['ARC']['Status']['Names']	= count($Package->Archive->InvalidFileList) ? 'Warn' : 'Good';
	$json['ARC']['Status']['Big']	= count($Package->Archive->BigFileList)  ? 'Warn' : 'Good';
	
	///die(str_repeat("To force error message uncomment this line", 100));
	$json_response = json_encode($json);
    die($json_response);
}

/**
 *  DUPLICATOR_PACKAGE_DELETE
 *  Deletes the files and database record entries
 *
 *  @return json   A json message about the action.  
 *				   Use console.log to debug from client
 */
function duplicator_package_delete() {
	
    try {
		global $wpdb;
		$json		= array();
		$post		= stripslashes_deep($_POST);
		$tblName	= $wpdb->prefix . 'duplicator_packages';
		$postIDs	= isset($post['duplicator_delid']) ? $post['duplicator_delid'] : null;
		$list		= explode(",", $postIDs);
		$delCount	= 0;
		
        if ($postIDs != null) {
            
            foreach ($list as $id) {
				$getResult = $wpdb->get_results("SELECT name, hash FROM `{$tblName}` WHERE id = {$id}", ARRAY_A);
				if ($getResult) {
					$row		=  $getResult[0];
					$nameHash	= "{$row['name']}_{$row['hash']}";
					$delResult	= $wpdb->query("DELETE FROM `{$tblName}` WHERE id = {$id}");
					if ($delResult != 0) {
						//Perms
						@chmod(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH_TMP . "/{$nameHash}_archive.zip"), 0644);
						@chmod(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH_TMP . "/{$nameHash}_database.sql"), 0644);
						@chmod(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH_TMP . "/{$nameHash}_installer.php"), 0644);						
						@chmod(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}_archive.zip"), 0644);
						@chmod(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}_database.sql"), 0644);
						@chmod(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}_installer.php"), 0644);
						@chmod(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}.log"), 0644);
						//Remove
						@unlink(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH_TMP . "/{$nameHash}_archive.zip"));
						@unlink(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH_TMP . "/{$nameHash}_database.sql"));
						@unlink(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH_TMP . "/{$nameHash}_installer.php"));
						@unlink(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}_archive.zip"));
						@unlink(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}_database.sql"));
						@unlink(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}_installer.php"));
						@unlink(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}.log"));
						//Unfinished Zip files
						$tmpZip = DUPLICATOR_SSDIR_PATH_TMP . "/{$nameHash}_archive.zip.*";
						array_map('unlink', glob($tmpZip));
						@unlink(DUP_Util::SafePath());
						$delCount++;
					} 
				}
            }
        }

    } catch (Exception $e) {
		$json['error'] = "{$e}";
        die(json_encode($json));
    }
	
	$json['ids'] = "{$postIDs}";
	$json['removed'] = $delCount;
    die(json_encode($json));
}

//DO NOT ADD A CARRIAGE RETURN BEYOND THIS POINT (headers issue)!!
?>