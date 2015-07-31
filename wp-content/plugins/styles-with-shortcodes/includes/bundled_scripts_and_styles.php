<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2003
 **/

class bundled_scripts_and_styles {
	function bundled_scripts_and_styles(){
		add_action('init',array(&$this,'init'));
	}

	function init(){
		global $sws_plugin,$wp_version;
		//3.5-beta1-22133
		$version = substr($wp_version,0,3);

		$sws_plugin->add_bundle('starter','Starter',WPCSS_PATH.'includes/bundle.php');

		wp_register_style('sws-button',WPCSS_URL.'css/sws_button.css',array(),'1.0.1');
		$sws_plugin->add_style('sws-button','SWS Button','');

		//registered by wp
		//$sws_plugin->add_script('jquery-ui-core','jQuery UI core','http://jqueryui.com');

		//wp_register_script('jquery-ui',WPCSS_URL.'js/jquery-ui-1.8.10.custom.min.js',array(),'1.8.10');
		if($version<3.5){
			wp_register_script('jquery-ui',WPCSS_URL.'js/jquery-ui-1.8.22.custom.min.js',array(),'1.8.22');
			wp_register_script('sws-jquery-ui-tabs',WPCSS_URL.'js/jquery-ui-tabs-1.8.10.custom.min.js',array('jquery-ui'),'1.8.10.1');
		}else if($version<3.6){
			wp_register_script('jquery-ui',WPCSS_URL.'js/jquery-ui-1.9.2.custom.min.js',array(),'1.9.2');
			wp_register_script('sws-jquery-ui-tabs',WPCSS_URL.'js/jquery.ui.tabs.1.9.2.custom.js',array('jquery-ui'),'1.9.2.1');
		}else{
			wp_register_script('jquery-ui',WPCSS_URL.'js/jquery-ui-1.9.2.custom.min.js',array(),'1.9.2');
			wp_register_script('sws-jquery-ui-tabs',WPCSS_URL.'js/jquery.ui.tabs.1.9.2.custom.js',array('jquery-ui'),'1.9.2.1');		
			/* 
			$dependency=array(
				'jquery-ui-core',
				'jquery-ui-accordion',
				'jquery-ui-widget',
				'jquery-ui-slider',
				'jquery-ui-dialog',
				'jquery-ui-button',
				'jquery-ui-autocomplete',
				'jquery-ui-draggable',
				'jquery-ui-droppable',
				'jquery-ui-sortable'
			);
			wp_register_script('jquery-ui',WPCSS_URL.'js/deprecated.js',$dependency,'bundled-jquery-ui');
			wp_register_script('sws-jquery-ui-tabs',WPCSS_URL.'js/jquery.ui.tabs.1.9.2.custom.js',array('jquery-ui'),'1.9.2.1');	
			*/	
		}

		$sws_plugin->add_script('jquery-ui','jQuery UI','http://jqueryui.com');		
		$sws_plugin->add_script('sws-jquery-ui-tabs','jQuery UI Tabs','http://jqueryui.com');

		wp_register_script('jquery-easing',WPCSS_URL.'js/jquery.easing.1.3.js',array(),'1.3.0');
		$sws_plugin->add_script('jquery-easing','jQuery Easing Plugin 1.3','http://gsgd.co.uk/sandbox/jquery/easing/');

		wp_register_style('ui-smoothness',WPCSS_URL.'css/smoothness/jquery-ui-1.8.7.custom.css',array(),'1.8.7');
		$sws_plugin->add_style('ui-smoothness','UI Smoothness','',true);

		wp_register_style('start',WPCSS_URL.'css/start/jquery-ui-1.8.7.custom.css',array(),'1.8.7');
		$sws_plugin->add_style('start','UI Start','',true);

		wp_register_script('lightbox-evolution',WPCSS_URL.'js/lightbox/jquery.lightbox.min.js',array('jquery'),'1.8.1.1');
		$sws_plugin->add_script('lightbox-evolution','Lightbox Evolution','');

		wp_register_script('preloadify',WPCSS_URL.'js/preloadify/jquery.preloadify.js',array('jquery','lightbox-evolution'),'1.0.3');
		$sws_plugin->add_script('preloadify','Preloadify','');

		wp_register_style('preloadify',WPCSS_URL.'js/preloadify/plugin/css/style.css',array(),'1.0.2');
		$sws_plugin->add_style('preloadify','Preloadify','');

		wp_register_style('sws-tables',WPCSS_URL.'css/tables.css',array(),'1.0.0');
		$sws_plugin->add_style('sws-tables','Table templates','');

		wp_register_script('tools-rangeinput',WPCSS_URL.'js/jquery.tools.rangeinput.min.js', array(),'1.2.5');
		//$sws_plugin->add_script('tools-rangeinput','Rangeinput','');

		wp_register_style('sws-picture-frames',WPCSS_URL.'css/picture_frames.css',array(),'1.0.0');
		$sws_plugin->add_style('sws-picture-frames','Picture frames','');

		//jquery-tools have a tabs method, if this is included by the theme or another plugin then our tabs dont work.
		wp_register_script('sws-jquery-tools',WPCSS_URL.'js/jquery.tools.min.js',array('jquery'),'1.2.5.1');
		$sws_plugin->add_script('sws-jquery-tools','jQuery TOOLS','http://flowplayer.org/tools/index.html');

		// wp_register_style('sws-bootstrap',WPCSS_URL.'css/bootstrap.css',array(),'2.2.2');
		wp_register_style('sws-bootstrap',WPCSS_URL.'css/bootstrap.css',array(),'2.3.0');
		//wp_register_style('sws-less',WPCSS_URL.'js/less/bootstrap.less',array(),'2.0.4');
		$sws_plugin->add_style('sws-bootstrap','Bootstrap, from Twitter','');

		wp_register_script('sws-less',WPCSS_URL.'js/less-1.3.0.min.js',array(),'1.3.0');
		// wp_register_script('bootstrap',WPCSS_URL.'js/bootstrap.js',array(),'2.2.2');
		wp_register_script('bootstrap',WPCSS_URL.'js/bootstrap.js',array(),'2.3.0');
		// wp_register_script('sws-bootstrap',WPCSS_URL.'js/deprecated.js',array('bootstrap','sws-less'),'2.2.2');
		wp_register_script('sws-bootstrap',WPCSS_URL.'js/deprecated.js',array('bootstrap','sws-less'),'2.3.0');
		//note: some shortcodes already depend on the key sws-bootstrap, so to avoid problems we continue to allow sws-bootstrap as key, but actually add bootstrap so that other plugins adding a new version can overwrite this one.
		$sws_plugin->add_script('sws-bootstrap','Bootstrap, from Twitter','');
		
		wp_register_style('sws-creative-buttons',WPCSS_URL.'css/sws-creative-buttons.css',array(),'1.0.0');
		$sws_plugin->add_style('sws-creative-buttons','SWS Creative Buttons','');		
		
		wp_register_script('classie',WPCSS_URL.'js/sws-creative-buttons/classie.js',array(),'1.0.0');
		wp_register_script('modernizr-custom',WPCSS_URL.'js/sws-creative-buttons/modernizr.custom.js',array('classie'),'1.0.0');
		$sws_plugin->add_script('modernizr-custom','SWS Creative Buttons','');
	}

	function rh_register_script( $handle, $src, $deps = array(), $ver = false, $in_footer = false, $enqueue=false ) {
		if( wp_script_is( $handle,'done') ) return false; //already printed.
		global $wp_scripts;
		if ( ! is_a( $wp_scripts, 'WP_Scripts' ) ) {
			$wp_scripts = new WP_Scripts();
		}

		$query = isset($wp_scripts->registered[$handle])?$wp_scripts->registered[$handle]:false;
		$register = true;
		if( wp_script_is( $handle, 'queue' ) || wp_script_is( $handle, 'to_do' ) ){
			$enqueue = true;
			if( $ver && $query && $ver < $query->ver ){
				$register = false;
			}else{
				wp_deregister_script( $handle );
			}
		}else if( wp_script_is( $handle, 'registered' ) ){
			if( $ver && $query && $ver < $query->ver ){
				$register = false;
			}else{
				wp_deregister_script( $handle );
			}
		}

		if($enqueue){
			if($register)wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
		}else{
			if($register)wp_register_script( $handle, $src, $deps, $ver, $in_footer );
		}
	}
}
?>
