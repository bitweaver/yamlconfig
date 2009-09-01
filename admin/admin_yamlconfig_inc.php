<?php

include_once( YAMLCONFIG_PKG_PATH.'YamlConfig.php' );

if( !empty( $_REQUEST["dump"] )) {
	$pkg = !empty( $_REQUEST['kernel_config_pkg'] )?$_REQUEST['kernel_config_pkg']:NULL;
	$yaml = YamlConfig::getKernelConfig( $pkg ); 
	$gBitSmarty->assign( 'yaml', $yaml );
}

if( !empty( $_REQUEST['submit_upload'] ) ){
	if( YamlConfig::processUploadFile( $_REQUEST ) ){
		// display log as valid yaml too - how sweet is that?
		$gBitSmarty->assign( "config_log", Horde_Yaml::dump( $_REQUEST['config_log'] ) );
	}
}

// stuff for out forms
$activePackages = array( 'all' => 'ALL' );
foreach( $gBitSystem->mPackages as $pkgname=>$data ){
	if( $data['active_switch'] ){
		$activePackages[$pkgname] = $pkgname;
	}
}
ksort( $activePackages );
$gBitSmarty->assign_by_ref( 'activePackages', $activePackages );

