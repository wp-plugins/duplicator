<?php

function duplicator_package_create() {
	
	global $wp_version;
    global $wpdb;
    global $current_user;
    
	@set_time_limit(0);
	$timerStart = DUP_Util::GetMicrotime();
	
	$json	  = array();
    $post	  = stripslashes_deep($_POST);
	$errLevel = error_reporting();
	error_reporting(E_ERROR);
	
	DUP_Util::InitSnapshotDirectory();

	$Package = new DUP_Package();
	$Package = $Package->GetActive();
	$Package->Hash				 = uniqid() . mt_rand(1000, 9999) . date("ymdHis");
	$Package->NameHash			 = "{$Package->Name}_{$Package->Hash}";
	$Package->Archive->File		 = "{$Package->NameHash}_archive.zip";
	$Package->Installer->File    = "{$Package->NameHash}_installer.php";
	$Package->Database->File     = "{$Package->NameHash}_database.sql";
	$Package->CreateRecord();
	
	DUP_Log::Open($Package->NameHash);
	$php_max_time	= @ini_get("max_execution_time");
	$php_max_memory = @ini_set('memory_limit', DUPLICATOR_PHP_MAX_MEMORY);
	$php_max_time	= ($php_max_time == 0)        ? "Enabled" : "NOT Enabled";
	$php_max_memory = ($php_max_memory === false) ? "Unabled to set php memory_limit"       : "set from={$php_max_memory} to=" . DUPLICATOR_PHP_MAX_MEMORY;
	
	DUP_Log::Info("********************************************************************************");
    DUP_Log::Info("PACKAGE-LOG: " . @date("Y-m-d H:i:s"));
    DUP_Log::Info("NOTICE: Do NOT post to public sites or forums");
    DUP_Log::Info("********************************************************************************");
    DUP_Log::Info("duplicator: " . DUPLICATOR_VERSION);
    DUP_Log::Info("wordpress: {$wp_version}");
    DUP_Log::Info("php: " . phpversion() . ' | ' . 'sapi: ' . php_sapi_name());
    DUP_Log::Info("server: {$_SERVER['SERVER_SOFTWARE']}");
    DUP_Log::Info("browser: {$_SERVER['HTTP_USER_AGENT']}");
	DUP_Log::Info("php set_time_limit: {$php_max_time}");
	DUP_Log::Info("php_max_memory: {$php_max_memory}");
	DUP_Log::Info("mysql wait_timeout:" . DUPLICATOR_DB_MAX_TIME);

	//BUILD PROCESS
	$Package->Database->Build();
	$Package->Archive->Build();
	$Package->Installer->Build();

	//VALIDATE FILE SIZE
	$dbSizeRead	 = DUP_Util::ByteSize($Package->Database->Size);
	$zipSizeRead = DUP_Util::ByteSize($Package->Archive->Size);
	$exeSizeRead = DUP_Util::ByteSize($Package->Installer->Size);
	if ( !($Package->Archive->Size && $Package->Database->Size && $Package->Installer->Size)) {
		DUP_Log::Error("A required file contains zero bytes.", "Archive Size: {$zipSizeRead} | SQL Size: {$dbSizeRead} | Installer Size: {$exeSizeRead}");
	}
	
	$Package->SetStatus(DUP_PackageStatus::COMPLETE);
	$timerEnd = DUP_Util::GetMicrotime();
    $timerSum = DUP_Util::ElapsedTime($timerEnd, $timerStart);
	
	DUP_Log::Info("********************************************************************************");
	DUP_Log::Info("RECORD ID:[{$Package->ID}]");
	DUP_Log::Info("FILE SIZE: Archive:{$zipSizeRead} | SQL:{$dbSizeRead} | Installer:{$exeSizeRead}");
	DUP_Log::Info("TOTAL PROCESS RUNTIME: {$timerSum}");
    DUP_Log::Info("DONE PROCESSING => {$Package->Name} " . @date("Y-m-d H:i:s"));
    DUP_Log::Info("********************************************************************************");

	//JSON:Debug Response
	//Pass = 1, Warn = 2, Fail = 3
	$json['Package'] = $Package;
    $json['Status']  = 1;
	$json['Runtime']  = $timerSum;
	$json['ExeSize']  = $exeSizeRead;
	$json['ZipSize']  = $zipSizeRead;
	$json_response = json_encode($json);
	DUP_Log::Close();

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
	$json['ARC']['LongFiles']	= is_array($Package->Archive->LongFileList) ? $Package->Archive->LongFileList : "unknown";
	$json['ARC']['BigFiles']	= is_array($Package->Archive->BigFileList)  ? $Package->Archive->BigFileList  : "unknown";
	$json['ARC']['Status']['Size']	= ($Package->Archive->Size > DUPLICATOR_SCAN_SITE) ? 'Warn' : 'Good';
	$json['ARC']['Status']['Names']	= count($Package->Archive->LongFileList) ? 'Warn' : 'Good';
	$json['ARC']['Status']['Big']	= count($Package->Archive->BigFileList)  ? 'Warn' : 'Good';
	
	///die(str_repeat("To force error message uncomment this line", 200));
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
						@chmod(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}_archive.zip"), 0644);
						@chmod(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}_database.sql"), 0644);
						@chmod(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}_installer.php"), 0644);
						@chmod(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}.log"), 0644);
						//Remove
						@unlink(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}_archive.zip"));
						@unlink(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}_database.sql"));
						@unlink(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}_installer.php"));
						@unlink(DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH . "/{$nameHash}.log"));
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