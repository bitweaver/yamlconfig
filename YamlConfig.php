<?php 
/**
* $Header: /cvsroot/bitweaver/_bit_yamlconfig/YamlConfig.php,v 1.5 2009/09/16 20:52:28 wjames5 Exp $
* $Id: YamlConfig.php,v 1.5 2009/09/16 20:52:28 wjames5 Exp $
*/

/*
* a simple class of static methods to return some basic configuration data or import it
*
* forward looking it would be preferable if packages exposed methods to get data and 
* this class could get those methods and call on them, rather than having to have sepecific functions 
* in here

* date created 2009/9/1
* @author Will James <will@tekimaki.com>
* @version $Revision: 1.5 $ $Date: 2009/09/16 20:52:28 $ $Author: wjames5 $
* @class YamlConfig
*/

class YamlConfig {
	private $mInstance;

	private function YamlConfig(){
	}

	public function getInstance(){
		if( empty( $this->mInstance ) ){
			$this->mInstance = new YamlConfig();
		}
		return $this->mInstance;
	}

	public static function processUploadFile( &$pParamHash ){ 
		$pParamHash['config_log'] = array();

		if( YamlConfig::verifyUpload( $pParamHash ) ){
			foreach( $pParamHash['upload_process'] as $file ){
				if( $hash = Horde_Yaml::loadFile( $file['tmp_name'] ) ){
					// deal with anything that might be in this hash
					// @Todo probably want to move this out of here eventually

					// kernel_config settings
					if( !empty( $hash['kernel_config'] ) ){
						// parser is a little annoying when it comes to n and y - it reinterprets them as FALSE and TRUE
						// we're lazy and dont want to regex the dump so lets try just flipping them back
						foreach( $hash['kernel_config'] as $pkg=>$data ){
							foreach( $hash['kernel_config'][$pkg] as $config=>$value ){
								if( $value === TRUE || $value === FALSE ){
									$hash['kernel_config'][$pkg][$config] = $value?'y':'n';	
								}
							}
						}
						$pParamHash['config_data']['kernel_config'] = $hash['kernel_config'];

						// store the configurations
						YamlConfig::setKernelConfig( $pParamHash ); 
					}

					// themes_layouts settings
					if( !empty( $hash['themes_layouts'] ) ){
						$pParamHash['config_data']['themes_layouts'] = $hash['themes_layouts'];

						// store the configurations
						YamlConfig::setThemesLayouts( $pParamHash ); 
					}

					// users_permissions settings
					if( !empty( $hash['users_permissions'] ) ){
						$pParamHash['config_data']['users_permissions'] = $hash['users_permissions'];

						// store the configurations
						YamlConfig::setUsersPermissions( $pParamHash ); 
					}
				}
			}
		}
		else{
			$pParamHash['config_log']['ERRORS'] = "Upload verification failed. ".$pParamHash['errors']['files'];
		}

		return ( 
			empty( $pParamHash['errors'] ) || 
			count( $pParamHash['errors'] ) == 0 
		);
	}

	private function verifyUpload( &$pParamHash ){
		if( !empty( $_FILES )) {
			foreach( $_FILES as $key => $file ) {
				if( !empty( $file['name'] ) && !empty( $file['tmp_name'] ) && is_uploaded_file( $file['tmp_name'] ) && empty( $file['error'] ) ) {
					$pParamHash['upload_process'][$key] = $file;
				}
			}
		}else{
			$pParamHash['errors']['files'] = tra( '$_FILES is empty' );
		}
		return ( 
			empty( $pParamHash['errors'] ) || 
			count( $pParamHash['errors'] ) == 0 
		);
	}


	/* Package Specific handlers - these could become service handlers eventually */

	// data from kernel_config table by package
	public static function getKernelConfig( $pPackage ){ 
		global $gBitSystem;

		$data = array( 'kernel_config'=>array() );
		$pkgs = array();

		if( !empty( $pPackage ) && strtoupper( $pPackage ) != 'ALL' ){
			if( in_array( $pPackage, array_keys($gBitSystem->mPackages) ) ){
				$pkgs[$pPackage] = "y";
			}
			else{
				$pParamHash['errors']['package'] = tra( 'Package not in system' );
			}
		}else{
			$pkgs = &$gBitSystem->mPackages;
		}

		foreach( $pkgs as $pkg=>$hash ){
			// hideous - but gBitSystem currently has no other way to return config by package name
			$gBitSystem->mConfig = NULL;
			$gBitSystem->loadConfig( $pkg );
			ksort( $gBitSystem->mConfig );
			$data['kernel_config'][$pkg] = $gBitSystem->mConfig; 
		}
		ksort( $data );

		// restore normal settings
		$gBitSystem->mConfig = NULL;
		$gBitSystem->loadConfig();

		$ret = Horde_Yaml::dump( $data );

		return $ret;
	}

	// data from themes layouts
	public static function getThemesLayout( $pPackage ){
		global $gBitThemes;
		$layouts = $gBitThemes->getAllLayouts();

		$data = array( 'themes_layouts'=>array() );

		if( !empty( $pPackage ) && strtoupper( $pPackage ) != 'ALL' ){
			if( in_array( $pPackage, array_keys($layouts) ) ){
				$hash = array( $pPackage => $layouts[ $pPackage ] );
			}
			else{
				$pParamHash['errors']['package'] = tra( 'No layout for this package exists.' );
			}
		}else{
			$hash = $layouts;
		}

		foreach( $hash as $pkg=>$area ){
			// layout modules are needlessly nested in areas - remove them
			foreach( $area as $modules ){
				foreach( $modules as $module ){
					$data['themes_layouts'][$pkg][] = $module;;
				}
			}
		}

		$ret = Horde_Yaml::dump( $data );

		return $ret;
	}

	// data from users
	public static function getUsersPermissions( $pPackage ){
		global $gBitSystem, $gBitUser;

		// get a list of all groups and their users_permissions
		$data = array( 'users_permissions'=>array() );

		$listHash = array(
			'only_root_groups' => TRUE,
			'sort_mode' => !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : 'group_name_asc'
		);

		if( !empty( $pPackage ) && strtoupper( $pPackage ) != 'ALL' ){
			$packages = array( $pPackage );
		}else{
			$packages = array_keys( $gBitSystem->mPackages );
		}

		$allPerms = $gBitUser->getGroupPermissions();
		$allGroups = $gBitUser->getAllGroups( $listHash );

		foreach( $allPerms as $perm=>$params ){
			if( in_array( $params['package'], $packages ) ){ 
				$data['users_permissions'][$perm] = array( 'description' => $params['perm_desc'], 'groups' => array() );
				foreach( $allGroups as $group ){
					if( in_array( $perm,  array_keys( $group['perms'] ) ) ){
						$data['users_permissions'][$perm]['groups'][] = $group['group_id']; 
					}
				}
			}
		}

		$ret = Horde_Yaml::dump( $data );

		return $ret;
	}

	public static function setKernelConfig( &$pParamHash ){
		global $gBitSystem;
		if( !empty( $pParamHash['config_data'] ) && !empty( $pParamHash['config_data']['kernel_config'] ) ){
			$hash = $pParamHash['config_data'];

			foreach( $hash['kernel_config'] as $pkg=>$data ){
				foreach( $hash['kernel_config'][$pkg] as $config=>$value ){
					$gBitSystem->storeConfig( $config, $value, $pkg );
					$pParamHash['config_log']['KERNEL::storeConfig'][$pkg][$config] = $value;
				}
			}
		}
	}

	public static function setThemesLayouts( &$pParamHash ){
		global $gBitThemes;

		if( !empty( $pParamHash['config_data'] ) && !empty( $pParamHash['config_data']['themes_layouts'] ) ){
			$hash = $pParamHash['config_data'];

			foreach( $hash['themes_layouts'] as $pkg=>$modules ){
				foreach( $modules as $module ){
					$gBitThemes->storeModule( $module );
					//storeModule modifies the module hash, so pick up the results from 'store' param
					$pParamHash['config_log']['THEMES::storeModule'][$pkg][] = $module['store']; 
				}
			}
		}
	}

	public static function setUsersPermissions( &$pParamHash ){
		global $gBitUser;

		$listHash = array(
			'only_root_groups' => TRUE,
			'sort_mode' => !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : 'group_name_asc'
		);
		$allGroups = $gBitUser->getAllGroups( $listHash );

		if( !empty( $pParamHash['config_data'] ) && !empty( $pParamHash['config_data']['users_permissions'] ) ){
			$hash = $pParamHash['config_data'];

			foreach( $hash['users_permissions'] as $perm=>$data ){
				foreach( array_keys( $allGroups ) as $groupId ) {
					if( in_array( $groupId, $data['groups'] ) ){
						$gBitUser->assignPermissionToGroup( $perm, $groupId );
						$pParamHash['config_log']['USERS::setPermissions'][$perm]['assign_to_group'][] = $groupId;
					} else {
						$gBitUser->removePermissionFromGroup( $perm, $groupId );
						$pParamHash['config_log']['USERS::setPermissions'][$perm]['remove_from_group'][] = $groupId;
					}
				}
			}
		}
	}
}
