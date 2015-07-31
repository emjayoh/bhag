<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
error_reporting(0);
//----added to support external configuration--------------------------------------------------------
define('SWS_BROWSER_CACHE',1);
require_once("../api.php");

$options = get_option('sws_options');
$options = empty($options)?array():$options;
$allowedSites = isset($options['allowed_ext_thumb_url'])?$options['allowed_ext_thumb_url']:'';
$allowedSites = str_replace("\n","",$allowedSites);
$allowedSites = explode("\r",$allowedSites);
$allowedSites = is_array($allowedSites)?$allowedSites:array();

$new_sources = array();
foreach($default_sources as $default_source){
	$name = 'ext_source_'.str_replace('.','_',$default_source);
	$value = isset($options[$name])?$options[$name]:'1';
	if('1'!=$value){
		$new_sources[]=$default_source;
	}
	$default_sources = $new_sources;
}

$allowedSites = array_merge($allowedSites,$default_sources);
if(''!=trim($_SERVER['HTTP_HOST']))$allowedSites[]=$_SERVER['HTTP_HOST'];
if(''!=trim($_SERVER['SERVER_NAME']))$allowedSites[]=$_SERVER['SERVER_NAME'];
//-----------------------------------------------------------------------------------------------------

// If ALLOW_EXTERNAL is true and ALLOW_ALL_EXTERNAL_SITES is false, then external images will only be fetched from these domains and their subdomains. 
$ALLOWED_SITES = $allowedSites;
// -------------------------------------------------------------

//------ PREFER the uploads directory
$upload_dir = wp_upload_dir();
if(is_writable($upload_dir['basedir'])){
	if(is_writable($upload_dir['basedir'].'/sws/cache')){
		define('FILE_CACHE_DIRECTORY', $upload_dir['basedir'].'/sws/cache' );
	}else{
		if(is_writable($upload_dir['basedir'].'/sws')){
			if( mkdir($upload_dir['basedir'].'/sws/cache') && is_writable($upload_dir['basedir'].'/sws/cache') ){
				define('FILE_CACHE_DIRECTORY', $upload_dir['basedir'].'/sws/cache' );
			}
		}else{
			if( mkdir($upload_dir['basedir'].'/sws') && mkdir($upload_dir['basedir'].'/sws/cache') && is_writable($upload_dir['basedir'].'/sws/cache')){
				define('FILE_CACHE_DIRECTORY', $upload_dir['basedir'].'/sws/cache' );
			}			
		}
	}
}
?>