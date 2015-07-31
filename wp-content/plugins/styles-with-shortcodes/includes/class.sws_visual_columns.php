<?php

/**
 * 
 * Author:  Alberto Lau
 * @version $Id$
 * @copyright 2003 
 **/

class sws_visual_columns {
	var $plugin_url;
	function sws_visual_columns($plugin_url){
		$this->plugin_url = $plugin_url;

		add_action('init', array(&$this,'init'), 11 );
		add_filter( 'tiny_mce_version', array(&$this,'refresh_mce'));
		add_filter('mce_css', array(&$this,'mce_css'), 10, 1);
		add_filter('tiny_mce_before_init', array(&$this,'tiny_mce_before_init'), 10 ,1);
	}
	
	function refresh_mce($ver) {
	  	return $ver++;
	}
	
	function tiny_mce_before_init($init){//allow inserting empty div's
		//$extended_valid_elements = 'div[class=rhcol]';
		//$extended_valid_elements = 'div[rel|class]';
		$extended_valid_elements = 'div[*]';
        if ( isset( $init['extended_valid_elements'] ) && ! empty( $init['extended_valid_elements'] ) ) {
            //$init['extended_valid_elements'] .= ',' . $extended_valid_elements; //on some client site this causes a parse error about string offsets or overloaded objects.
			$init['extended_valid_elements'] = $init['extended_valid_elements'] . ',' . $extended_valid_elements;
        } else {
            $init['extended_valid_elements'] = $extended_valid_elements;
        }

		return $init;
	}
	
	function init(){
		wp_enqueue_style('rh_columns',$this->plugin_url.'editorplugin/css/rh_columns.css',array(),'1.0.2');
		$this->add_tinymce_button();
	}
	
	function mce_css($mce_css){
		if ( ! empty( $mce_css ) )
			$mce_css .= ',';
		$mce_css.= $this->plugin_url.'editorplugin/css/style.css';
		return $mce_css;
	}
	
	function add_tinymce_button(){
		add_filter('mce_external_plugins', array(&$this,'add_tinymce_plugin') );
     	add_filter('mce_buttons', array(&$this,'register_button') );	
		add_filter('wp_fullscreen_buttons', array(&$this,'wp_fullscreen_buttons') );
	}	
	
	function add_tinymce_plugin($plugin_array){
		$plugin_array['rh_columns'] = $this->plugin_url.'editorplugin/rh_columns_101.js';
		return $plugin_array;
	}
	
	function register_button($buttons){
		array_push($buttons, "|", "rh_columns", "rh_column_add", "rh_column_remove", "rh_column_decrease", "rh_column_increase");
		return $buttons;
	}
	
	function wp_fullscreen_buttons($buttons){
		$buttons[]='separator';
		$buttons['rh_columns']=array(
			'title'=>__('Insert columns','sws'),
			'onclick'=>'rhcol_add_row(tinyMCE.activeEditor);',
			'both'=>false
		);
		
		return $buttons;
	}
}
?>