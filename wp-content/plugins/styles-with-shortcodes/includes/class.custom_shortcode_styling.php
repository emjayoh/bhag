<?php

class custom_shortcode_styling {
	var $id = 'styles-with-shortcodes';
	var $options;
	var $plugin_code;
	var $sws_scripts = array();
	var $sws_styles = array();
	var $bundles = array();
	var $show_ui = true;
	var $options_parameters = array();
	var $editor_parameters = array();
	var $load_footer_resources = true;
	var $ajax_content_support = false;
	var $resources_path = '';
	function custom_shortcode_styling($args=array()){
		$option_overwrite = isset($args['options'])&&is_array($args['options'])?$args['options']:array();
		$this->options = get_option('sws_options');
		if(is_array($option_overwrite)&&count($option_overwrite)>0){
			foreach($option_overwrite as $field => $value){
				$this->options[$field]=$value;
			}
		}		
		//------------
		$defaults = array(
			'plugin_code'			=> 'SWS',
			'show_ui'				=> true,
			'options_parameters'	=> array(),
			'editor_parameters'		=> $this->get_editor_parameters(),
			'options_panel_version'	=> '2.6.7',
			'load_footer_resources'	=> true,
			'ajax_content_support'	=> true,//by default should be false to avoid extra processing.
			'autoupdate'			=> true
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}
		//-----------
		$this->ajax_content_support = '1'==$this->get_option('ajax_content_support','0',true) ? true : false ;
		//-----------
		$this->options_parameters = $this->get_options_parameters();
		//-----------
		add_action('plugins_loaded',array(&$this,'plugins_loaded'));
		add_action('init',array(&$this,'init'));
		add_action('admin_init', array(&$this,'handle_multisite_subsite_bundle'));
		
		if(isset($this->options['disable_autop'])&&$this->options['disable_autop']==1){
			remove_filter ('the_content', 'wpautop');
		}
		
		if(isset($this->options['increase_pcre'])&&$this->options['increase_pcre']==1){
			try {
				if(@ini_get('pcre.backtrack_limit')<10000001){
					@ini_set('pcre.backtrack_limit',10000001);
				}
				if(@ini_get('pcre.recursion_limit')<20000002){
					@ini_set('pcre.recursion_limit',20000002);
				}
			}catch(Exception $e){
				
			}
		}
		if(is_admin()){
			require_once WPCSS_PATH.'options-panel/load.pop.php';
			rh_register_php('options-panel',WPCSS_PATH.'options-panel/class.PluginOptionsPanelModule.php', $this->options_panel_version);
			rh_register_php('rh-functions', WPCSS_PATH.'options-panel/rh-functions.php', $this->options_panel_version);
		}		
	}
	function get_options_parameters(){
		$r = array();
		foreach(array('option_show_in_metabox') as $field){
			if(isset($this->options[$field])){
				$r[$field]=$this->options[$field];
			}
		}
		
		$api_url = 'secondary'==$this->get_option('righthere_api_url','',true) ? 'http://plugins.albertolau.com/' : 'http://plugins.righthere.com/';
		
		$dc_options = array(
			'id'				=> $this->id.'-dc',
			'plugin_id'			=> $this->id,
			//'capability'		=> 'sws_downloads',
			'resources_path'	=> $this->resources_path,
			'parent_id'			=> 'edit.php?post_type=csshortcode',
			'menu_text'			=> __('Downloads','sws'),
			'page_title'		=> __('Downloadable content - Styles With Shortcodes for WordPress','sws'),
			'license_keys'		=> $this->get_option('license_keys',array()),
			'plugin_code'		=> $this->plugin_code,
			'api_url'			=> $api_url,
			'product_name'		=> __('Styles With Shortcodes','sws'),
			'options_varname' 	=> 'sws_options',
			'tdom'				=> 'sws'
		);			
		
		$defaults = array(				
			'id'					=> $this->id,
			'plugin_id'				=> $this->id,
			'capability'			=> 'manage_options',
			'options_varname'		=> 'sws_options',
			'menu_id'				=> 'sws-options',
			'page_title'			=> __('Options','sws'),
			'menu_text'				=> __('Options','sws'),
			'option_menu_parent'	=> 'edit.php?post_type=csshortcode',
			'notification'			=> (object)array(
				'plugin_version'=> WPCSS,
				'plugin_code' 	=> 'SWS',
				'message'		=> __('Styles With Shortcodes update %s is available! <a href="%s">Please update now</a>','sws')
			),
			'option_show_in_metabox'=> true,
			'registration'			=> true,
			'downloadables'			=> true,
			'theme'					=> false,
			'extracss'				=> 'extracss-'.$this->id,
			'rangeinput'			=> true,
			'fileuploader'			=> true,
			'dc_options'			=> $dc_options,
			'pluginurl'				=> WPCSS_URL,
			'tdom'					=> 'sws',
			'path'			=> WPCSS_PATH.'options-panel/',
			'url'			=> WPCSS_URL.'options-panel/',
			'pluginslug'	=> WPCSS_SLUG,
			//'api_url' 		=> "http://localhost"
			'api_url' 		=> $api_url//affects registration api		
		);
		
		foreach($defaults as $field => $value){
			$r[$field] = isset($r[$field])?$r[$field]:$value;
		}
				
		return $r;
	}
	function get_editor_parameters(){
		$r = array();
		foreach(array('show_in_metabox','metabox_title','meta_box_post_types','editor_head_always') as $field){
			if(isset($this->options[$field])){
				$r[$field]=$this->options[$field];
			}
		}
		return $r;
	}
	
	function init(){
		wp_enqueue_style( 'styles-with-shortcodes', WPCSS_URL.'css/style.css', array(),'1.0.1');
		wp_enqueue_script('sws_frontend',WPCSS_URL.'js/sws_frontend.js',array('jquery'),'1.0.0');
		if(is_admin()):
			if(@$_REQUEST['post_type']!='slider')wp_enqueue_style('wpcss-toggle',WPCSS_URL.'css/toggle.css',array(),'1.0.3');
			wp_enqueue_style('jquery-colorpicker',WPCSS_URL.'colorpicker/css/colorpicker.css',array(),'1.0.0');
			wp_register_style( 'sws-insert-tool', WPCSS_URL.'css/insert_tool.css', array(),'1.0.1');
//			wp_register_style( 'sws-options', WPCSS_URL.'css/pop.css', array(),'1.0.0');
			
			wp_enqueue_script('wpsws',WPCSS_URL.'js/sws.js',array(),'1.0.2');
//			wp_enqueue_script('jquery-colorpicker',WPCSS_URL.'colorpicker/js/colorpicker.js',array('jquery'),'1.0.0');				
			wp_register_script( 'sws-insert-tool', WPCSS_URL.'js/insert_tool.js', array(),'1.0.2');			
		endif;
	}
	
	function plugins_loaded(){			
		//-- register scripts ----
		require_once WPCSS_PATH.'includes/bundled_scripts_and_styles.php';	
		new bundled_scripts_and_styles();//
		
		require_once WPCSS_PATH.'includes/class.CSShortcodes.php';//defines the csshortcode custom post type.
		new CSShortcodes(array('show_ui'=>$this->show_ui));
		
		require_once WPCSS_PATH.'includes/class.ImportExport.php';//api for importing exporting		
		require_once WPCSS_PATH.'includes/class.CSShortcodesLoad.php';//load shortcodes to wp
		new CSShortcodesLoad(array(
			'load_footer_resources'	=> $this->load_footer_resources,
			'ajax_content_support'	=> $this->ajax_content_support
		));
		require_once WPCSS_PATH.'includes/class.sws_mce.php';
		require_once WPCSS_PATH.'includes/class.sws_lightbox.php';
		new sws_lightbox($this->options);
		
		if('1'!=$this->get_option('disable_visual_columns','0',true)){
			require_once WPCSS_PATH.'includes/class.sws_visual_columns.php';
			new sws_visual_columns(WPCSS_URL);
		}
		
		if(is_admin()):
			if(!isset($this->options['editor_capability']) || ''==$this->options['editor_capability'] || current_user_can($this->options['editor_capability']) ){				
				require_once WPCSS_PATH.'includes/class.CSSEditor.php';
				new CSSEditor($this->editor_parameters);
			}
			do_action('rh-php-commons');	
			$settings = $this->options_parameters;
			
			require_once WPCSS_PATH.'includes/class.sws_options.php';
			new sws_options($settings);		
			
			require_once WPCSS_PATH.'includes/class.lightbox_options.php';
			new lightbox_options($settings);
		
			new PluginOptionsPanelModule($settings);
		endif;		
	}
	
	function add_script($id,$label,$url=''){
		$this->sws_scripts[] = (object)array('id'=>$id,'label'=>$label,'url'=>$url);
	}
	
	function add_style($id,$label,$url='',$ui_theme=false){
		$this->sws_styles[] = (object)array('id'=>$id,'label'=>$label,'url'=>$url,'ui_theme'=>$ui_theme);
	}
	
	function add_bundle($id,$label,$path){
		$this->bundles[$id]=(object)array('id'=>$id,'label'=>$label,'path'=>$path);
	}
	
	function handle_multisite_subsite_bundle(){
		if(is_multisite() && is_admin() && WPCSS>get_option('WPCSS')){
			global $wpdb;
			$sql = "SELECT COALESCE((SELECT 1 FROM `{$wpdb->posts}` WHERE post_type='csshortcode' LIMIT 1),0)";
			if( 0==$wpdb->get_var($sql,0,0) ){
				$import_export = new ImportExport();
				$res = $import_export->restore_bundle_from_name('starter',$error);
				update_option('WPCSS',WPCSS);
			}
		}
	}
	
	function get_option($name,$default='',$default_if_empty=false){
		$value = isset($this->options[$name])?$this->options[$name]:$default;
		if($default_if_empty){
			$value = ''==$value?$default:$value;
		}
		return $value;
	}		
}  


?>