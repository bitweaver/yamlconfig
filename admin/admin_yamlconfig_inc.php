<?php

include_once( YAMLCONFIG_PKG_PATH.'YamlConfig.php' );

if( !empty( $_REQUEST["dump"] )) {
	$yaml = "";
	if( !empty( $_REQUEST['kernel_config_pkg'] ) ){ 
		$pkg = $_REQUEST['kernel_config_pkg'];
		$yaml .= YamlConfig::getKernelConfig( $pkg ); 
	}
	if( !empty( $_REQUEST['themes_layouts'] ) ){ 
		$pkg = $_REQUEST['themes_layouts'];
		$yaml .= YamlConfig::getThemesLayout( $pkg ); 
	}
	if( !empty( $_REQUEST['users_permissions'] ) ){ 
		$pkg = $_REQUEST['users_permissions'];
		$yaml .= YamlConfig::getUsersPermissions( $pkg ); 
	}
	$gBitSmarty->assign( 'yaml', $yaml );
}

if( !empty( $_REQUEST['submit_upload'] ) ){
	$gBitUser->verifyTicket();

	if( YamlConfig::processUploadFile( $_REQUEST ) ){
		// display log as valid yaml too - how sweet is that?
		$gBitSmarty->assign( "config_log", Horde_Yaml::dump( $_REQUEST['config_log'] ) );
	}
}

// get data for forms
$activePackages = array( 'all' => 'ALL' );
foreach( $gBitSystem->mPackages as $pkgname=>$data ){
	if( $data['active_switch'] ){
		$activePackages[$pkgname] = $pkgname;
	}
}
ksort( $activePackages );
array_unshift( $activePackages, 'None' ); // requests NULL
$gBitSmarty->assign_by_ref( 'activePackages', $activePackages );


$layouts = array( 'all' => 'ALL' );
foreach( $gBitThemes->getAllLayouts() as $package=>$modules ){
	$layouts[$package] = $package;
}
ksort( $layouts );
array_unshift( $layouts, 'None' ); // requests NULL
$gBitSmarty->assign_by_ref( 'layouts', $layouts );
