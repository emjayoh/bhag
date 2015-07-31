<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class CSShortcodesLoad {
	var $tag_to_id = array();
	var $footer = array();
	var $uid = 0;
	var $shortcode_tags = array();
	var $added_footer = array();
	var $load_footer_resources = true;
	var $ajax_content_support = false;
	var $head_resources = '';
	function CSShortcodesLoad($args){
		if(!is_admin()){//only applies to frontend.
			$this->load_footer_resources = isset($args["load_footer_resources"])?$args["load_footer_resources"]:true;
			$this->ajax_content_support = isset($args["ajax_content_support"])?$args["ajax_content_support"]:false;
			$this->load_footer_resources = $this->ajax_content_support ? false : $this->load_footer_resources;		
		}
		//--------
		$this->add_shortcodes();
		//--------
		add_shortcode('sws_debug',array(&$this,'sws_debug'));
		add_filter('the_content',array(&$this,'do_shortcode'),9,1);
	}
	
	function sws_debug($atts,$content=null,$code=""){
		global $wp_filter;
		$out = "<textarea cols=40 rows=5>".$content."</textarea>";
		$out .= "<pre>".print_r($wp_filter['the_content'],true)."</pre>";
		$out .= "<pre>".print_r($wp_filter['wp_footer'],true)."</pre>";
		return $out;
	}
	
	function add_shortcodes(){
		global $wpdb;
		
		$sql = "SELECT P.ID, P.post_title";
		$sql.= ", COALESCE((SELECT M.meta_value FROM `{$wpdb->postmeta}` M WHERE M.post_id=P.ID AND M.meta_key=\"sc_shortcode\" LIMIT 1),'') as sc_shortcode";
		$sql.= ", COALESCE((SELECT M.meta_value FROM `{$wpdb->postmeta}` M WHERE M.post_id=P.ID AND M.meta_key=\"sc_shortcodes\" LIMIT 1),'') as sc_shortcodes";
		$sql.= ", COALESCE((SELECT M.meta_value FROM `{$wpdb->postmeta}` M WHERE M.post_id=P.ID AND M.meta_key=\"sc_priority_shortcode\" LIMIT 1),'') as sc_priority_shortcode";
		$sql.= " FROM `{$wpdb->posts}` P";
		$sql.= " WHERE post_type='csshortcode' AND post_status='publish' ORDER BY menu_order ASC";
		if($wpdb->query($sql)&&$wpdb->num_rows>0){
			$shortcode_ids = array();
			foreach($wpdb->last_result as $row){
				$shortcode_ids[]=$row->ID;
				$tag = $row->sc_shortcode;
				//$priority_shortcode = intval(get_post_meta($row->ID,'sc_priority_shortcode',true));
				$priority_shortcode = intval($row->sc_priority_shortcode); 
				if(trim($tag)!=''){
					$this->tag_to_id[$tag]=$row->ID;
					if($priority_shortcode){
						$this->add_shortcode($tag, array(&$this,'shortcode_handler'));
					}else{
						add_shortcode($tag, array(&$this,'shortcode_handler'));
					}
				}		
				//---
				//$tags = get_post_meta($row->ID,'sc_shortcodes',true);
				$tags = $row->sc_shortcodes;
				$tags = ''==trim($tags)?false:unserialize($tags);
				if(is_array($tags)&&count($tags)>0){
					foreach($tags as $tag){
						if(trim($tag)!=''){
							$this->tag_to_id[$tag]=$row->ID;
							if($priority_shortcode){
								$this->add_shortcode($tag, array(&$this,'sub_shortcode_handler'));
							}else{
								add_shortcode($tag, array(&$this,'sub_shortcode_handler'));
							}
						}
					}
				}
			}
			
			$this->shortcode_ids = $shortcode_ids;
			add_action('init',array(&$this,'handle_ajax_content_suppport'),20);//run after bundle_scripts_and_styles.php
		}
	}
	
	function get_loaded_shortcode_ids(){
		return $this->shortcode_ids;
	}
	
	function generate_resources($css,$js,$script_sources,$tag_link){	
		$out = get_option('sws_resources','');

		if( ''==$out || '1'==get_option('sws_rebuild_cache','') ){
			update_option('sws_rebuild_cache','');
			
			$replace = array(
				'{pluginurl}'=>WPCSS_URL,
				'{themeurl}'=>get_bloginfo('stylesheet_directory').'/',
				'{siteurl}'=>site_url(),
				'console.log'=>'//console.log'
			);	
	
			$out = '';
			if(!empty($tag_link)){
				foreach($tag_link as $src){
					$out.= $this->_replace($src,$replace)."\n";
				}
			}	
			
			$upload_dir = wp_upload_dir();
			//----
			$filename = wp_unique_filename( $upload_dir['path'], 'sws.css' );
			$filename_path = $upload_dir['path'].'/'.$filename;
			$filename_url = $upload_dir['url'].'/'.$filename;	
			
			$inline_styles = $this->_replace(implode("\n",$css),$replace); 		
			if( file_put_contents($filename_path, $inline_styles) ){
				$out.=sprintf('<link rel="stylesheet" href="%s" type="text/css" media="all"></link>',
					$filename_url
				);
			}else{
				$out.='<style>'.$inline_styles."</style>\n";
			}
			
			if(!empty($script_sources)){
				foreach($script_sources as $src){
					$out.=sprintf('<script type="text/javascript" src="%s"></script>',
						$this->_replace($src,$replace)
					);
				}
			}

			$filename = wp_unique_filename( $upload_dir['path'], 'sws.js' );
			$filename_path = $upload_dir['path'].'/'.$filename;
			$filename_url = $upload_dir['url'].'/'.$filename;

			$inline_scripts = $this->_replace(implode("\n",$js),$replace);
			if( file_put_contents($filename_path, $inline_scripts) ){
				$out.=sprintf('<script type="text/javascript" src="%s"></script>',
					$filename_url
				);
			}else{
				$out .= '<script>'.$inline_scripts."</script>\n";
			}
			
			update_option('sws_resources',$out);		
		}

		return $out;
	}
	
	function handle_ajax_content_suppport(){
		if(!$this->ajax_content_support)return;
		
		$shortcode_ids = $this->get_loaded_shortcode_ids();
		//TODO save all to a fixed file.
		//todo:allow admin to choose wich ones are supported.
		$ajax_supported_ids = $shortcode_ids;
		
		$scripts = array();
		$styles = array();		
		$css = array();
		$js = array();
		$script_sources = array();
		$tag_link = array();
		foreach($ajax_supported_ids as $id){
			$args=array();
			
			$script = get_post_meta($id,'sc_scripts',true);
			if(is_array($script)&&!empty($script)){
				$scripts = array_merge($scripts,$script);
			}
			$style = get_post_meta($id,'sc_styles',true);
			if(is_array($style)&&!empty($style)){
				$styles = array_merge($styles,$style);
			}
			
			$str = get_post_meta($id,'sc_css',true);
			$this->add_tag_content( $str, 'style', $css);
			$this->add_tag_link( $str, $tag_link );
			
			$str = get_post_meta($id,'sc_js',true);
			$this->add_tag_content( $str, 'script', $js);
			$this->add_script_sources( $str, $script_sources);
			
			
		}
		
		$scripts = array_unique($scripts);
		$styles = array_unique($styles);
		$script_sources = array_unique($script_sources);
			
		$scripts[]='sws-jquery-ui-tabs';//	
			
		if(!empty($scripts)){
			foreach($scripts as $s){
				wp_enqueue_script($s);
			}
		}

		if(!empty($styles)){
			foreach($styles as $s){
				wp_enqueue_style($s);
			}
		}
		
		global $sws_plugin;
		$ui_theme = $sws_plugin->get_option('preload_ui_theme','',true);
		if(!empty($ui_theme)){
			wp_enqueue_style($ui_theme);
		} 
		
		$this->head_resources = $this->generate_resources($css,$js,$script_sources,$tag_link);
		
		add_action('wp_head',array(&$this,'resources_for_ajax_pages'));
	}
	
	function resources_for_ajax_pages(){
		echo $this->head_resources;
	}
	
	function add_tag_content($str, $tag, &$arr) {
		$pattern="/<$tag ?.*>(.*)<\/$tag>/Uims";
		if( preg_match_all($pattern, $str, $matches) ){
			foreach($matches[1] as $m){
				if(''!=trim($m)){
					$arr[]=$m;
				}
			}
		}
	}	
	
	function add_script_sources($str, &$arr) {
		$pattern="/<script .*?(?=src)src=\"([^\"]+)\"/ims";
		if( preg_match_all($pattern, $str, $matches) ){
			foreach($matches[1] as $m){
				if(''!=trim($m)){
					$arr[]=$m;
				}
			}
		}
	}	
	
	function add_tag_link($str, &$arr) {
		$pattern="/<link( ?.*)>(.*)<\/link>/Uims";
		if( preg_match_all($pattern, $str, $matches) ){
			foreach($matches[0] as $m){
				if(''!=trim($m)){
					$arr[]=$m;
				}
			}
		}
	}	
	//this is directly modified from shortcodes.php
	//wpautop is interfering with some shortcodes so we need to do some of our shortcodes before autop
	function add_shortcode($tag, $func) {
		if ( is_callable($func) )
			$this->shortcode_tags[$tag] = $func;
	}	
	
	function get_shortcode_regex() {
		$shortcode_tags =& $this->shortcode_tags;
		$tagnames = array_keys($shortcode_tags);
		$tagregexp = join( '|', array_map('preg_quote', $tagnames) );
	
		// WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcodes()
		return '(.?)\[('.$tagregexp.')\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)';
	}
	
	function do_shortcode($content) {
		$shortcode_tags =& $this->shortcode_tags;
	
		if (empty($shortcode_tags) || !is_array($shortcode_tags))
			return $content;
	
		$pattern = $this->get_shortcode_regex();
		return preg_replace_callback('/'.$pattern.'/s', array(&$this,'do_shortcode_tag'), $content);
	}
	
	function do_shortcode_tag( $m ) {
		$shortcode_tags =& $this->shortcode_tags;
	
		// allow [[foo]] syntax for escaping a tag
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return substr($m[0], 1, -1);
		}
	
		$tag = $m[2];
		$attr = shortcode_parse_atts( $m[3] );
	
		if ( isset( $m[5] ) ) {
			// enclosing tag - extra parameter
			return $m[1] . call_user_func( $shortcode_tags[$tag], $attr, $m[5], $tag ) . $m[6];
		} else {
			// self-closing tag
			return $m[1] . call_user_func( $shortcode_tags[$tag], $attr, NULL,  $tag ) . $m[6];
		}
	}	
	//------------------------------------
	function _replace($str,$arr){
		foreach($arr as $key => $repl){
			$str = str_replace($key,$repl,$str);
		}
		return $str;
	}
	
	function sub_shortcode_handler($atts,$content=null,$code=""){
		$sc_id = $this->tag_to_id[$code];	
		$sc_fields = get_post_meta($sc_id,'sc_fields',true);
		$arr = array();
		$sc_template = '';
		if(is_array($sc_fields)&&count($sc_fields)>0){
			while($field=array_shift($sc_fields)){
				if(property_exists($field,'shortcode') && $field->shortcode==$code && in_array($field->type,array('data'))){
					$sc_template = $field->shortcode_template;
					$i=0;
					while( ($i++<$field->field_number) && ($f=array_shift($sc_fields)) ){
						$varname = strtolower($f->name);
						$arr[$varname]=$f->default;
					}
				}
			}
		}
		
		extract(shortcode_atts($arr, $atts));
		
		$content = trim($content)==''?$content:do_shortcode($content);
		
		$replace = array(
			'{id}'=>$sc_id,
			'{shortcode}'=>$code,
			'{pluginurl}'=>WPCSS_URL,
			'{themeurl}'=>get_bloginfo('stylesheet_directory').'/',
			'{siteurl}'=>site_url(),
			'{uid}'=> $this->uid++
		);	
		
		$fields = get_post_meta($sc_id,'sc_fields',true);
		if(is_array($fields)&&count($fields)>0){
			foreach($fields as $f){
				$varname = strtolower($f->name);
				if(@$f->content==1){
					$replace["{".$f->name."}"]=$content;
				}else{
					$replace["{".$f->name."}"]=$this->get_field_value($f,@$$varname,$code);//@$$varname;
				}
			}
		}	
			
		if(''==trim($sc_template)){
			return '';
		}
	
		$out = do_shortcode($sc_template);
		$out = $this->_replace($out,$replace);		
		$out = str_replace('timthumb.php','thumbnail.php',$out);		
		return $out;
	}

	function shortcode_handler($atts,$content=null,$code=""){
		$sc_id = $this->tag_to_id[$code];
		$fields = get_post_meta($sc_id,'sc_fields',true);
		$arr = array();
		if(is_array($fields)&&count($fields)>0){
			foreach($fields as $f){
				$varname = strtolower($f->name);
				$arr[$varname]=$f->default;
				if('ui_theme'==$f->type){
					$ui_theme_var = $f->name;
				}
			}
		}

		extract(shortcode_atts($arr, $atts));
		
		$replace = array(
			'{id}'=>$sc_id,
			'{shortcode}'=>$code,
			'{content}'=>$content,
			'{pluginurl}'=>WPCSS_URL,
			'{themeurl}'=>get_bloginfo('stylesheet_directory').'/',
			'{siteurl}'=>site_url(),
			'{uid}'=> $this->uid++
		);
				
		if(is_array($fields)&&count($fields)>0){
			foreach($fields as $f){
				$varname = strtolower($f->name);//wp shortcode api lowercases attributes. :s
				$replace["{".$f->name."}"]=$this->get_field_value($f,@$$varname,$code);
			}
		}		
	
		$out = get_post_meta($sc_id,'sc_template',true);
		$out = $this->php_handler($sc_id,$atts,$content,$code,$arr,$out);
		if(''==trim($out)){
			return '';
		}
		
		$out = $this->_replace($out,$replace);
		$out = ''==$out?'':do_shortcode($out);

		if($this->load_footer_resources){
			$this->added_footer[$code] = isset($this->added_footer[$code])?$this->added_footer[$code]:false;
			if(!$this->added_footer[$code]){
				$this->added_footer[$code]=true;
				add_action('wp_footer',array(&$this,'footer'));
				if(is_admin()){
					add_action('admin_footer',array(&$this,'footer'));
				}
				//-----------------------------------------------------------------------------------
				$sc_styles = get_post_meta($sc_id,'sc_styles',true);
				$sc_styles = is_array($sc_styles)&&count($sc_styles)>0?$sc_styles:array();
	
				if(isset($$ui_theme_var)){
					$sc_styles[]=$$ui_theme_var;
				}
	
				if(count($sc_styles)>0){
					ob_start();
					foreach($sc_styles as $style){
						wp_print_styles($style);
					}
					$this->footer['styles'][$code] = ob_get_contents();
					ob_end_clean();
				}	
				//-----------------------------------------------------------------------------------
				$sc_scripts = get_post_meta($sc_id,'sc_scripts',true);
				$sc_scripts = is_array($sc_scripts)?$sc_scripts:array();
				//--- provided to support shortcodes from the first release until the user updates the shortcodes.
				if(in_array($code,array('sws_overlay','sws_scrollable_basic','sws_scrollable_preview','sws_overlay_apple','sws_gmap3','sws_tooltip','fstip','accordion','uitabs','sws_code','sws_nivo_zoom','sws_nivo_slider','sws_toggle1','sws_toggle2','sws_toggle3'))){
					array_unshift($sc_scripts,'jquery-ui');
					array_unshift($sc_scripts,'sws-jquery-tools');
				}
				if(in_array($code,array('sws_ui_tabs'))){
					array_unshift($sc_scripts,'jquery-ui');
					array_unshift($sc_scripts,'sws-jquery-ui-tabs');
				}			
				//----
				if(count($sc_scripts)>0){				
					ob_start();
					foreach($sc_scripts as $script){
						wp_print_scripts($script);
					}
					$this->footer['scripts'][$code] = ob_get_contents();
					ob_end_clean();
				}	
				//-----------------------------------------------------------------------------------
				$css = get_post_meta($sc_id,'sc_css',true);
				$this->footer['css'][$code]= $this->_replace($css,$replace);
				
				
				$js = get_post_meta($sc_id,'sc_js',true);
				$this->footer['js'][$code]= $this->_replace($js,$replace);
			}		
		}

		$out = str_replace('timthumb.php','thumbnail.php',$out);
		return $out;						
	}
	
	function php_handler($sc_id,$atts,$content,$code,$field_defaults,$shortcode_template){
		$php = get_post_meta($sc_id,'sc_php',true);
		if(trim($php)=='')return $shortcode_template;
		extract(shortcode_atts($field_defaults, $atts));
		$output='';
		try{
			ob_start();
			eval($php);
			$output = ob_get_contents();
			ob_end_clean();			
		}catch(Exception $e){
			
		}
		$output = str_replace('timthumb.php','thumbnail.php',$output);
		return $output;
	}
	
	function footer(){		
		if(is_array($this->footer) && count($this->footer)>0){
			foreach($this->footer as $sws_type => $arr){
				if($sws_type=='css'){
					$css = '<style rel="styles-with-shortcodes" type="text/css">';
					foreach($arr as $i => $css_block){
						$css.=$this->remove_tag($css_block,'style');
					}
					$css.= '</style>';
					echo $css;
				}else{
					$footer = implode("\n",$arr);
					$footer = str_replace("<style>\r\n</style>","",$footer);
					$footer = str_replace("<script>\r\n</script>","",$footer);
					echo $footer;				
				}
			}
		}
	}	
	
	function remove_tag($str, $tags, $stripContent = false) {
	    $content = '';
	    if(!is_array($tags)) {
	        $tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
	        if(end($tags) == '') array_pop($tags);
	    }
	    foreach($tags as $tag) {
	        if ($stripContent)
	             $content = '(.+</'.$tag.'[^>]*>|)';
	         $str = preg_replace('#</?'.$tag.'[^>]*>'.$content.'#is', '', $str);
	    }
	    return $str;
	}
	
	function get_field_value($field,$value,$shortcode){
		if(!property_exists($field,'urlencode')&& false!==strpos($shortcode,'sws_picture_frame')){
			$value = urlencode($value);
		}
		return $value;
	}
}


?>