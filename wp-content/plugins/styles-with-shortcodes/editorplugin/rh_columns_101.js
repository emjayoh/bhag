(function() {
    tinymce.create('tinymce.plugins.rh_columns', {
		
        init : function(ed, url) {		
			var t = this, mouse = {};
			t.url = url;
			t._createButtons(ed);		
		
            ed.addButton('rh_columns', {
                title : 'Insert columns',
                image : url+'/img/columns.png',
                onclick : function() {
        			return rhcol_add_row(ed);
                }
            });

			//clicks outside the editor	
			jQuery('html').click(function(e){
				if( jQuery(e.target).is('#rhcol_buttons') || jQuery(e.target).parent().is('#rhcol_buttons') )
					return;
				jQuery('#rhcol_buttons').hide();
			});

			ed.onVisualAid.add(function(ed,e,s){
				ed.getWin().onscroll=function(){
					jQuery('#rhcol_buttons').hide();
				};
			});
					
			// show editimage buttons
			ed.onMouseDown.add(function(ed, e) {
				var target = e.target;
				//handle a situation where clicking on a rhcol but the selection is row-fluid row
				try {
					if( ed.dom.getAttrib(ed.selection.getNode(), 'class').indexOf('row-fluid')>=0 ){
						ed.selection.select( target );
					}				
				}catch(e){
				
				}
				
				//if the space was manually removed, clicking should place it back
				var node = ed.getBody().lastChild;
				if( node.nodeName=='BODY' || node.nodeName=='DIV' || node.nodeName=='P'){//limit execution a bit
					if(jQuery(node).is('.row-fluid')){
						var c = jQuery(node).after('<p>&nbsp;</p>').next();
						ed.selection.select(c.get(0));		
					}else if( jQuery(node).is('p') && jQuery(node).html()=='' ){
						jQuery(node).html('&nbsp;');
					}				
				}

				var node = jQuery(e.target);
				if( node.hasClass('rhcol') ){
					rhcol = node;
				}else if( node.parent().hasClass('rhcol') ){
					rhcol = node.parent();
				}else if( node.parents('.rhcol').length>0 ){
					rhcol = node.parents('.rhcol');
				}else{
					jQuery('#rhcol_buttons').fadeOut();
					//ed.execCommand('mceRepaint');//bug: this disables the image edit icons when clicking on image.
					return;
				}
				
				target = rhcol.get(0);

				var selection_node = jQuery(ed.selection.getNode());
				if( !jQuery(selection_node).is('.rhcol') && jQuery(selection_node).parents('.rhcol').length==0 ){//mousedown inside rhcol, but selection remained outside
					//handle a situation where certain content is not selectable inside the column and thus the column buttons do not work.
					//selection is outside even though column clicked					
					var c = jQuery('<span id="caret_pos_holder">&nbsp;</span>').appendTo(rhcol);
					ed.selection.select(c.get(0));
					c.remove();					
				}
				
				if ( ed.dom.getAttrib(target, 'class').indexOf('mceItem') == -1 ) {
					mouse = {
						x: e.clientX,
						y: e.clientY,
						img_w: target.clientWidth,
						img_h: target.clientHeight
					};

					ed.plugins.wordpress._showButtons(target, 'rhcol_buttons');
				}
			});
			
			ed.onKeyDown.add(function(ed, e) {
				if( jQuery('#rhcol_buttons').is(':visible') )
					jQuery('#rhcol_buttons').fadeOut();
	
				//handle wp3.5 adding an extra cell
				if( e.keyCode == 13 && !e.shiftKey) {				
				 	if( jQuery(ed.selection.getNode()).is('.rhcol') ){
						e.shiftKey = true;//automatic shift+enter behaviour.
					}
				}
			});
			
			ed.onChange.add(function(ed, l) {
				var node_el = ed.selection.getNode();
				var node = jQuery( node_el );
				if( node.is('.rhcol') && ! node.is('div') ){		
					var name = (node[0].className.match(/(^|\s)(span[^\s]*)/) || [,,''])[2];
				    if(name){
						var wrapping_node = node.wrap('<div></div>').parent();
				        node.removeClass(name).removeClass('rhcol');
				    	wrapping_node.addClass(name).addClass('rhcol');
						ed.selection.select(node_el, true);
						ed.selection.collapse(false);
					}					
				}
			});
        },
        createControl : function(n, cm) {
            return null;
        },
		_createButtons : function(ed) {
			var t = this, DOM = tinymce.DOM, addButton, removeButton, increaseButton, decreaseButton;
			var bw = '32';
			var bh = '32';
			DOM.remove('rhcol_buttons');

			DOM.add(document.body, 'div', {
				id : 'rhcol_buttons',
				style : 'display:none;'
			});

			addButton = DOM.add('rhcol_buttons', 'img', {
				src : t.url+'/img/column_add.png',
				id : 'rhcol_add',
				width : bw,
				height : bh,
				title : 'Add column'//ed.getLang('rhcol.add')
			});

			jQuery(addButton).on('mousedown', function(e) {
				var ed = tinyMCE.activeEditor;
				rh_column_add(ed);
			});

			removeButton = DOM.add('rhcol_buttons', 'img', {
				src : t.url+'/img/column_remove.png',
				id : 'rhcol_remove',
				width : bw,
				height : bh,
				title : 'Remove column'//ed.getLang('rhcol.remove')
			});

			jQuery(removeButton).on('mousedown', function(e) {
				var ed = tinyMCE.activeEditor;
				rh_column_remove(ed);
				jQuery('#rhcol_buttons').hide();
			});

			decreaseButton = DOM.add('rhcol_buttons', 'img', {
				src : t.url+'/img/column_decrease.png',
				id : 'rhcol_decrease',
				width : bw,
				height : bh,
				title : 'Decrease column'//ed.getLang('rhcol.decrease')
			});

			jQuery(decreaseButton).on('mousedown', function(e) {
				var ed = tinyMCE.activeEditor;
				t.editor_rhcol_modify_col(ed,false);
			});
			
			increaseButton = DOM.add('rhcol_buttons', 'img', {
				src : t.url+'/img/column_increase.png',
				id : 'rhcol_increase',
				width : bw,
				height : bh,
				title : 'Increase column'//ed.getLang('rhcol.increase')
			});

			jQuery(increaseButton).on('mousedown', function(e) {
				var ed = tinyMCE.activeEditor;
				t.editor_rhcol_modify_col(ed,true);
			});

		},		
        getInfo : function() {
            return {
                longname : "Visual columns",
                author : 'Alberto Lau',
                authorurl : 'http://plugins.righthere.com/',
                infourl : 'http://plugins.righthere.com/',
                version : "1.0.0"
            };
        },
		editor_rhcol_modify_col : function (ed,increase){
			 try {
				var node = jQuery(ed.selection.getNode());
				if(node.length>0){
					if( node.hasClass('rhcol') ){
						rhcol = node;
					}else if( node.parent().hasClass('rhcol') ){
						rhcol = node.parent();
					}else if( node.parents('.rhcol').length>0 ){
						rhcol = node.parents('.rhcol');
					}else{
						return false;
					}
				}

				ed.undoManager.typing = true; //force catpure undo data
				ed.undoManager.add();
			
				var arr = rhcol.attr('class').match(/span([0-9]{1,2})/);
				if(arr.length==2){
					var span_index = parseInt( arr[1] );
					if(increase){
						span_index++;
					}else{
						span_index--;
					}
					span_index = span_index>12?1:span_index;
					span_index = span_index<1?12:span_index;
				
					rhcol
						.removeClass( arr[0] )
						.addClass( 'span'+span_index )
					;
				}					
			}catch(e){

			}
			return false;
		}		
    });
    tinymce.PluginManager.add('rh_columns', tinymce.plugins.rh_columns);
})();


function rhcol_add_row(ed){
	if( jQuery(ed.selection.getNode()).hasClass('row-fluid') ){
		//handle a situation where the caret is placed in the row-fluid holder and add row is pressed.
		ed.execCommand('mceInsertContent', false, '<div class="span6 rhcol"><p>&nbsp;</p></div>' );		
		return false;
	} 

	var fluidColumns = '<div class="row-fluid"><div class="span6 rhcol"><p>&nbsp;</p></div><div class="span6 rhcol"><p>&nbsp;</p></div></div>';
	ed.execCommand('mceInsertContent', false, fluidColumns );
	
	var node = ed.getBody().lastChild;
	if(jQuery(node).is('.row-fluid')){
		var c = jQuery(node).after('<p>&nbsp;</p>').next();
		ed.selection.select(c.get(0));		
	}else if( jQuery(node).is('p') && jQuery(node).html()=='' ){
		jQuery(node).html('&nbsp;');
	}
	return false;
}

function rhcol_get_total_span( rhcol ){
	if( rhcol.parent().hasClass('row-fluid') ){
		var row = rhcol.parent();
	}else if( rhcol.parents('.row-fluid').length>0 ){
		var row = rhcol.parents('.row-fluid');
	}else{
		var row = false;
	}
	
	if(false!=row){
		var total_span = 0;
		row.children('.rhcol').each(function(i,el){
			var arr = jQuery(el).attr('class').match(/span([0-9]{1,2})/);
			if(arr.length==2){
				total_span += parseInt( arr[1] );
			
			}							
		});
		return total_span;
	}
	return false;
}

function rh_column_add(ed){
	var singleFluidColumn = '<div class="rhcol"><p>&nbsp;</p></div>';
	var node = jQuery(ed.selection.getNode());
	if(node.length>0){
		if( node.hasClass('rhcol') ){
			rhcol = node;
		}else if( node.parent().hasClass('rhcol') ){
			rhcol = node.parent();
		}else if( node.parents('.rhcol').length>0 ){
			rhcol = node.parents('.rhcol');
		}else{
			return false;
		}
	}

	ed.undoManager.typing = true; //force catpure undo data
	ed.undoManager.add();
	
	var span_class = 'span6';
	
	var total_span=rhcol_get_total_span( rhcol );
	if(total_span<12){
		var diff_span = 12-total_span;
		var span_class = 'span'+diff_span;	
	}else{
		var arr = rhcol.attr('class').match(/span([0-9]{1,2})/);
		if(arr.length==2){
			var span_index = parseInt( arr[1] );
			if(span_index>1){
				var new_node_span_index = parseInt( span_index/2 );
				var cur_node_new_index = span_index - new_node_span_index;
				var span_class = 'span'+new_node_span_index;
				rhcol
					.removeClass( arr[0] )
					.addClass( 'span'+ cur_node_new_index )
				;
			}
		}					
	}					

	rhcol.after( jQuery(singleFluidColumn).addClass(span_class) );					
	return false;
}

function rh_column_remove(ed){
	var node = jQuery(ed.selection.getNode());
	if(node.length>0){
		if( node.hasClass('rhcol') ){
			rhcol = node;
		}else if( node.parent().hasClass('rhcol') ){
			rhcol = node.parent();
		}else if( node.parents('.rhcol').length>0 ){
			rhcol = node.parents('.rhcol');
		}else{
			return false;
		}
	}
	
	ed.undoManager.typing = true; //force catpure undo data
	ed.undoManager.add();

	var container = rhcol.parent();
	
	rhcol.remove();
	if(container.is('.row-fluid') && container.children().length==0){
		container.remove();
	}else{
		var c = jQuery('<span id="caret_pos_holder">&nbsp;</span>').appendTo( container.children().first() );
		ed.selection.select(c.get(0));
		c.remove();
		ed.execCommand("mceRepaint");				
	}
	
	return true;
}

