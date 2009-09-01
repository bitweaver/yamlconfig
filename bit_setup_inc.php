<?php
global $gBitSystem;

$registerHash = array(
	'package_name' => 'yamlconfig',
	'package_path' => dirname( __FILE__ ).'/',
);
$gBitSystem->registerPackage( $registerHash );

// yes this is retarded but necessary until yaml support is in the bw core
if( !spl_autoload_functions() || !in_array( 'hordeyaml_autoloader', spl_autoload_functions() ) ) {
	// schmema for auto loading Horde/Yaml
	function hordeyaml_autoloader($class) {
		if( strstr( $class, 'Horde_Yaml' ) ){
			foreach( explode( PATH_SEPARATOR, get_include_path()) as $path ){
				$file = str_replace('_', '/', $class) . '.php';
				if( file_exists( $path.'/'.$file ) ){ 
					require $file; 
					break;
				}
			}
		}
	}       
	spl_autoload_register('hordeyaml_autoloader');
}


