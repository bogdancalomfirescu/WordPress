(function()
{

/*
INDEX
-----
- Plugins
- General
- Sticky header
- Full Screen
- Component Toggle
- Component Sorting
- Media
- Component Source
- Row Shortcode
- Column Shortcode
- Heading Shortcode
- Icon Shortcode
- Button Shortcode
- Text Shortcode
- Logging
- Editor activation/deactivation

------------------------------------------------------------------------------------------------------------------------
 Plugins
------------------------------------------------------------------------------------------------------------------------
*/

jQuery.fn.mmtl_equalHeight = function()
{
	var maxHeight = 0;

	// clears height when set
	jQuery(this).css( 'height', 'auto' );

	this.each(function()
	{
		var height = jQuery(this).outerHeight( false );

		if ( height > maxHeight  )
		{
			maxHeight = height;
		};
	});
	
	jQuery(this).css( 'height', maxHeight );

	return this;
};

/*
------------------------------------------------------------------------------------------------------------------------
 General
------------------------------------------------------------------------------------------------------------------------
*/

MMTL_Editor.eventManager.add( 'setup', function( event, ed )
{
	// translates shortcode callback return value to component

	ed.add_filter( 'shortcode_replacement', function( replacement, shortcode )
	{
		return ed.components.create( shortcode, replacement );
		
	}, 15 );

	// strips backslashes from option value

	ed.add_filter( 'sanitize_options', function( options )
	{
		if ( typeof options === 'object' )
		{
			var o = {};

			jQuery.each( options, function( key, value )
			{
				o[ key ] = value.replace(/\\/g, '');
			});

			options = o;
		};

		return options;

	}, 1 );

	// control click

	ed._elem.on( 'click', 'a.mmtl-control', function( event )
	{
		var control = ed.controls.get( jQuery(this).data( 'type' ) );

		var $component = jQuery(this).closest( '.mmtl-component' );

		control.click( $component );

		jQuery( this ).blur();

		return false;
	});

	// add component button

	ed.on( 'init', function( event )
	{
		ed._elem.on( 'click', '.mmtl-add-component-button', function( event )
		{
			var $button = jQuery( this );

			$button.blur();

			var tag = $button.data( 'type' );

			if ( ed.shortcodes.hasTag( tag ) )
			{
				var shortcode = new wp.shortcode( { tag : tag } );

				var component = ed.components.create( shortcode );

				ed.components.add( component, null );

				ed.updateSource();
				ed.update();
			};

			return false;
		});
	});

	// controls

	ed.add_control( 'add',
	{
		text : ed.option( 'control_label_add' ),
		title : ed.option( 'control_label_add' ),
		icon : 'plus',
		click : function( component )
		{
			var shortcode = ed.components.getShortcode( component );

			var data = ed.components.getData( shortcode.tag );

			if ( data.accepts.length == 0 )
			{
				return;
			};

			if ( data.accepts.length == 1 )
			{
				var tag = data.accepts[0];

				var child = ed.components.create( new wp.shortcode( { tag : tag } ) );

				var sc = ed.components.getShortcode( child );

				ed.components.add( child, component, false );

				ed.updateSource();
				ed.update();

				return;
			};

			ed.doAjax( 'get_components_screen', { id : shortcode.tag }, function( response )
			{
				$elem = jQuery( response.data );

				$elem.find( '.mmtl-component' )
					.mmtl_equalHeight();

				jQuery.featherlight( $elem,
				{
					namespace : 'mmtl-lightbox',
					persist : true
				});

				$elem.find('.mmtl-component').on( 'click', function( event )
				{
					var tag = jQuery( this ).data( 'type' );

					var child = ed.components.create( new wp.shortcode( { tag : tag } ) );

					ed.components.add( child, component, false, 'picklist' );

					jQuery.featherlight.close();

					return false;
				});
			});
		}
	});

	ed.add_control( 'copy',
	{
		text : ed.option( 'control_label_copy' ),
		title : ed.option( 'control_label_copy' ),
		icon : 'admin-page',
		click : function( component )
		{
			var copy = ed.components.copy( component );

			ed.components.add( copy, jQuery( component ).parent(), jQuery( component ).index() + 1 );

			ed.updateSource();
			ed.update();
		}
	});

	ed.add_control( 'delete',
	{
		text : ed.option( 'control_label_delete' ),
		title : ed.option( 'control_label_delete' ),
		icon : 'trash',
		click : function( component )
		{
			if ( ! window.confirm( ed.options.get( 'confirm_delete' ) ) )
			{
				return;
			};

			ed.components.remove( component );

			ed.updateSource();
		}
	});

	ed.add_control( 'add_before',
	{
		text : ed.option( 'control_label_add' ),
		title : ed.option( 'control_label_add' ),
		icon : 'plus',
		click : function( component )
		{
			var $component = jQuery( component );
			var shortcode = ed.components.getShortcode( $component );

			var sc = new wp.shortcode( { tag : shortcode.tag } );
			var c = ed.components.create( sc );

			ed.components.add( c, $component.parent(), $component.index() );

			ed.updateSource();
			ed.update();
		}
	});

	ed.add_control( 'add_after',
	{
		text : ed.option( 'control_label_add' ),
		title : ed.option( 'control_label_add' ),
		icon : 'plus',
		click : function( component )
		{
			var $component = jQuery( component );
			var shortcode = ed.components.getShortcode( $component );

			var sc = new wp.shortcode( { tag : shortcode.tag } );
			var c = ed.components.create( sc );

			ed.components.add( c, $component.parent(), $component.index() + 1 );

			ed.updateSource();
			ed.update();
		}
	});

	ed.add_filter( 'default_options', function( options )
	{
		return jQuery.extend( options,
		{
			confirm_delete : 'Are you sure you want to delete this component?',
			control_label_add    : 'Add',
			control_label_edit   : 'Edit',
			control_label_copy   : 'Copy',
			control_label_delete : 'Delete'
		});
	});
});

/*
------------------------------------------------------------------------------------------------------------------------
 Settings Page
------------------------------------------------------------------------------------------------------------------------
*/

function openSettingsPage( component, ed )
{
	var shortcode = ed.components.getShortcode( component );

	// set needed to render page

	var page_data = jQuery.extend( {}, shortcode.attrs.named,
	{
		content : shortcode.content || '',
		_page : shortcode.tag
	});

	ed.doLog( 'page data', page_data );

	ed.doAjax( 'get_settings_page', page_data, function( response )
	{		
		var $page = jQuery( response.data );

		jQuery.featherlight( $page,
		{
			namespace : 'mmtl-lightbox',
			persist : true,
			beforeOpen : function( event )
			{
				$submit = $page.find( 'form p.submit' ).remove();

				// tabs

				$page.find( '.mmtl-screen-content' ).mmtl_prepare_tabs(
				{
					separator : 'h3'
				}).tabs();

				// sets submit button outside of tabs

				$page.find( 'form' ).append( $submit );

				// submit handler

				$page.find( 'form' ).on( 'submit', function( event )
				{
					ed.trigger( 'settings_submit', [ $page, shortcode, component ] );

					// sanitizes input

					var form_data = jQuery( this ).serialize();

					ed.doLog( 'form data', form_data );

					ed.doAjax( null, form_data, function( response )
					{
						input = ed.apply_filters( 'sanitize_options', response.data, shortcode, component );
						
						if ( typeof input !== 'object' )
						{
							return;
						};

						ed.doLog( 'input', input )

						// updates shortcode

						var args = { tag : shortcode.tag };

						if ( input.hasOwnProperty( 'content' ) )
						{
							args.content = input.content;

							delete input.content;
						}

						args.attrs = input;

						var updatedSortcode = new wp.shortcode( args );

						ed.doLog( 'updatedSortcode', updatedSortcode );

						ed.components.setShortcode( component, updatedSortcode );

						ed.trigger( 'settings_update', [ $page, shortcode, component, input ] );

						ed.updateSource();
						ed.update();

					});

					jQuery.featherlight.close();

					return false;
				})
			}
		});

		ed.trigger( 'settings_page', [ $page, shortcode, component ] );
	});
}

MMTL_Editor.eventManager.add( 'setup', function( event, ed )
{
	ed.add_control( 'edit',
	{
		text : ed.option( 'control_label_edit' ),
		title : ed.option( 'control_label_edit' ),
		icon : 'edit',
		click : function( component )
		{
			openSettingsPage( component, ed );
		}
	});

	ed.on( 'component_added', function( event, component, context )
	{
		if ( context == 'picklist' )
		{
			openSettingsPage( component, ed );
		};
	});
});

/*
------------------------------------------------------------------------------------------------------------------------
 Sticky header
------------------------------------------------------------------------------------------------------------------------
*/

function stickHeader( ed, stick )
{
	if ( typeof stick === 'undefined' )
	{
		stick = true;
	};

	var $header = ed._elem.find( '.mmtl-header' )

	if ( ! stick )
	{
		$header.trigger( 'sticky_kit:detach' )

		ed._elem.removeClass( 'mmtl-header-stuck' )

		return;
	};

	$header
		.stick_in_parent(
		{
			offset_top: jQuery( '#wpadminbar' ).outerHeight(),
			sticky_class : 'mmtl-stuck'
		})

		.on( 'sticky_kit:stick', function( e )
		{
			ed._elem.addClass( 'mmtl-header-stuck' );
		})

		.on( 'sticky_kit:unstick', function( e )
		{
			ed._elem.removeClass( 'mmtl-header-stuck' );
		});
}

MMTL_Editor.eventManager.add( 'setup', function( event, ed )
{
	ed.on( 'init', function( event )
	{
		stickHeader( ed );
	});
	
	ed.on( 'fullscreen_toggle', function( event, active )
	{
		if ( active )
		{
			stickHeader( ed, false );
		}

		else
		{
			stickHeader( ed );
		}
		
	});
});

/*
------------------------------------------------------------------------------------------------------------------------
 Full Screen
------------------------------------------------------------------------------------------------------------------------
*/

function fullScreenKeyPress( event )
{
	var ed = event.data.ed;

	if ( jQuery( '.mmtl-lightbox' ).is( ':visible' ) )
	{
		return;
	};

	if ( event.keyCode == 27 )
	{
		toggleFullScreen( ed );
	};
}

function toggleFullScreen( ed )
{
	var $control = ed._elem.find( '.mmtl-control-fullscreen' );

	var $overlay = jQuery( '#mmtl-overlay' );

	if ( ! jQuery( 'body' ).hasClass( 'mmtl-full-screen' ) )
	{
		$overlay.data( 'mmtl_editor',
		{
			wrap : ed._elem.parent()
		});

		$overlay.empty().append( ed._elem );

		jQuery( 'body' ).addClass( 'mmtl-full-screen' );

		$control
			.removeClass( 'dashicons-editor-expand' )
			.addClass( 'dashicons-editor-contract' );

		jQuery( document ).on( 'keyup', { ed : ed }, fullScreenKeyPress );

		ed.trigger( 'fullscreen_toggle', [ true ] );
	}

	else
	{
		var data = $overlay.data( 'mmtl_editor' );

		data.wrap.append( ed._elem );

		jQuery.removeData( $overlay, 'mmtl_editor' );

		$overlay.empty();

		$control
			.removeClass( 'dashicons-editor-contract' )
			.addClass( 'dashicons-editor-expand' );

		jQuery( 'body' ).removeClass( 'mmtl-full-screen' );

		jQuery( document ).off( 'keyup', fullScreenKeyPress );

		ed.trigger( 'fullscreen_toggle', [ false ] );
	}
}

MMTL_Editor.eventManager.add( 'setup', function( event, ed )
{
	ed.add_control( 'fullscreen',
	{
		text : ed.option( 'control_label_fullscreen' ),
		title : ed.option( 'control_label_fullscreen' ),
		icon : 'editor-expand',
		click : function()
		{
			toggleFullScreen( ed );
		}
	});

	ed.add_filter( 'header_controls', function( controls )
	{
		controls.push( 'fullscreen' );

		return controls;
	}, 15 );

	ed.on( 'init', function()
	{
		jQuery( 'body' ).append( '<div id="mmtl-overlay"></div>' );
	});

	ed.on( 'destroy', function()
	{
		jQuery( 'body' ).find( '> #mmtl-overlay' ).remove();
	});

	ed.add_filter( 'default_options', function( options )
	{
		return jQuery.extend( options,
		{
			control_label_fullscreen : 'Toggle full screen'
		});
	});
});

/*
------------------------------------------------------------------------------------------------------------------------
 Component Toggle
------------------------------------------------------------------------------------------------------------------------
*/

function toggle( $component )
{
	var $control = $component.find( '> .mmtl-component-inner > .mmtl-component-header .mmtl-control-toggle' );

	if ( $component.hasClass( 'mmtl-closed' ) )
	{
		$component
			.removeClass( 'mmtl-closed' );

		$control
			.removeClass( 'dashicons-arrow-down' )
			.addClass( 'dashicons-arrow-up' );
	}

	else
	{
		$component.addClass( 'mmtl-closed' );

		$control
			.removeClass( 'dashicons-arrow-up' )
			.addClass( 'dashicons-arrow-down' );
	};
}

MMTL_Editor.eventManager.add( 'setup', function( event, ed )
{
	ed.add_control( 'toggle',
	{
		text : ed.option( 'control_label_toggle' ),
		title : ed.option( 'control_label_toggle' ),
		icon : 'arrow-up',
		click : function( component )
		{
			toggle( component );
		}
	});

	ed.on( 'init', function( event )
	{
		ed._elem.on( 'click', '.mmtl-component > .mmtl-component-inner > .mmtl-component-header:has( .mmtl-control-toggle )', function( event )
		{
			event.stopPropagation();

			var $component = jQuery(this).closest( '.mmtl-component' );

			toggle( $component );

			return false;
		});
	});

	ed.add_filter( 'default_options', function( options )
	{
		return jQuery.extend( options,
		{
			control_label_toggle : 'Toggle'
		});
	});
});

/*
------------------------------------------------------------------------------------------------------------------------
 Component Meta
------------------------------------------------------------------------------------------------------------------------
*/

MMTL_Editor.eventManager.add( 'setup', function( event, ed )
{
	ed.add_filter( 'component_meta', function( meta, shortcode )
	{
		if ( [ 'mmtl-row', 'mmtl-col' ].indexOf( shortcode.tag ) != -1 )
		{
			// Background Image

			var bg_image = shortcode.get( 'bg_image' );

			if ( bg_image )
			{
				// adds placeholder

				meta.push( { title : ed.option( 'meta_title_bg_image' ), type : 'bg_image', text : '' } );
			};

			// ID

			var id = shortcode.get( 'id' );

			if ( id )
			{
				meta.push( { title: ed.option( 'meta_title_id' ), type : 'id', text : id } );
			};

			// Class

			var htmlClass = shortcode.get( 'class' );

			if ( htmlClass )
			{
				jQuery.each( htmlClass.split( ' ' ), function( i, text )
				{
					meta.push( { title : ed.option( 'meta_title_class' ), type : 'class', text : text } );
				});
			};
		};

		return meta;
	});

	// loads background image preview

	ed.on( 'update', function()
	{
		loadBackgroundImagePreview( ed );
	});
});

function loadBackgroundImagePreview( ed )
{
	var urls = {}, $component, shortcode;

	// gets urls from rows

	ed._elem.find( '.mmtl-component[data-type="mmtl-row"], .mmtl-component[data-type="mmtl-col"]' ).each( function()
	{
		$component = jQuery( this );

		shortcode = ed.components.getShortcode( $component );

		bg_image = shortcode.get( 'bg_image' );

		if ( ! bg_image || bg_image.indexOf( 'http://' ) !== 0 )
		{
			return true;
		};

		urls[ $component.data( 'id' ) ] = bg_image;
	});

	if ( jQuery.isEmptyObject( urls ) )
	{
		return;
	};

	// gets thumbail url

	var args = { attachment : urls };

	ed.doAjax( 'get_attachment_sizes', args, function( response )
	{
		if ( ! response.success )
		{
			return;
		};

		var sizes = response.data;

		jQuery.each( sizes, function( component_id, data )
		{
			// checks if thumbail exists
			// breaks loop if not. no need to continue when thumbnail is not found

			if ( ! data.hasOwnProperty( 'thumbnail' ) )
			{
				return false;
			};

			$component = ed.components.get( component_id );

			if ( ! $component )
			{
				return true;
			};

			$component.find( '.mmtl-meta[data-type="bg_image"]' )
				.css( 'background-image',  'url(' + data.thumbnail.file + ')' );
		});
	});
}

/*
------------------------------------------------------------------------------------------------------------------------
 Component Sorting
------------------------------------------------------------------------------------------------------------------------
*/

function setComponentSorting( ed )
{
	ed._elem.find( '.mmtl-editor .ui-sortable' ).sortable( 'destroy' );

	var defaults =
	{
		connectWith : '',
		placeholder : 'mmtl-placeholder',

		start : function( event, ui )
		{
			jQuery( ui.placeholder )
				.append( '<div class="mmtl-placeholder-inner"><div class="mmtl-placeholder-content"></div></div>' );

			ed._elem.addClass( 'mmtl-is-sorting' );

			ui.item.addClass( 'mmtl-is-sorting' );
		},

		update : function( event, ui )
		{
			ed.updateSource();
		},

		sort : function( event, ui )
		{
			var $component = jQuery( ui.item );

			// treats placeholder like a component
			jQuery( ui.placeholder )
				.removeClass()
				.addClass( 'mmtl-placeholder' )
				.addClass( $component.attr('class') )
					.removeClass( 'ui-sortable-handle' )
					.attr( 'data-type', $component.attr( 'data-type' ) )
					.find( '.mmtl-placeholder-inner' )
						.height( $component.innerHeight() )
		},

		stop : function( event, ui )
		{
			jQuery( ui.placeholder ).empty();

			var $component = jQuery( ui.item );

			ed._elem.removeClass( 'mmtl-is-sorting' );

			ui.item.removeClass( 'mmtl-is-sorting' );
		}
	};

	// TODO : not OOP

	var $main = jQuery( '.mmtl-content' );

	$main.sortable( defaults ).disableSelection();

	var $rows = ed._elem.find( '.mmtl-component[data-type="mmtl-row"] > .mmtl-component-inner > .mmtl-component-content' );

	$rows.sortable( jQuery.extend( defaults, { connectWith : $rows } ) ).disableSelection();

	var $cols = ed._elem.find( '.mmtl-component[data-type="mmtl-col"] > .mmtl-component-inner > .mmtl-component-content' );

	$cols.sortable( jQuery.extend( defaults, { connectWith : $cols } ) ).disableSelection();
}

MMTL_Editor.eventManager.add( 'setup', function( event, ed )
{
	ed.on( 'init', function( event )
	{
		setComponentSorting( ed );
	});

	ed.on( 'update', function( event )
	{
		setComponentSorting( ed );
	});

	ed.on( 'component_added', function( event )
	{
		setComponentSorting( ed );
	});
});

/*
------------------------------------------------------------------------------------------------------------------------
 Media
------------------------------------------------------------------------------------------------------------------------
*/

MMTL_Editor.eventManager.add( 'setup', function( event, ed )
{
	ed.on( 'settings_page', function( event, $page, shortcode, component )
	{
		$page.find( '.mmtl-media' ).each(function()
		{
			var $wrap = jQuery( this );

			var $addButton = $wrap.find( '.mmtl-media-add' );
			var $removeButton = $wrap.find( '.mmtl-media-remove' );
			var $field = $wrap.find( '.mmtl-media-field' );
			var $image = $wrap.find( '.mmtl-media-image' );

			$addButton.on( 'click', function( event )
			{
				// called when user has selected media item
				wp.media.editor.send.attachment = function( properties, attachment )
		        {
		        	// TODO : check mime type

		        	// sets url
		        
		        	var size = attachment.sizes[ properties.size ];

		        	$field.val( size.url );

		        	// sets preview

		        	var thumbnail = attachment.sizes.thumbnail;

		        	ed.doLog( 'image sizes', attachment.sizes );

					$wrap.addClass( 'mmtl-has-image' );

					$image
						.removeClass( 'mmtl-image-h mmtl-image-v' )
						.addClass( thumbnail.width > thumbnail.height ? 'mmtl-image-h' : 'mmtl-image-v' )
						.attr( 'src', thumbnail.url )
						.show();
		        };

	        	wp.media.editor.open( 'mmtl_editor_bg_image' );

				return false;
			});

			$removeButton.on( 'click', function( event )
			{
				$image
					.removeAttr( 'src' )
					.hide();

				$wrap.removeClass( 'mmtl-has-image' );

				$field.val('');

				return false;
			});

			if ( $field.val() )
			{
				var url = $field.val();

				ed.doAjax( 'get_attachment_sizes', { attachment : url }, function( response )
				{
					if ( response.success )
					{
						var thumbnail = response.data.thumbnail;

						$wrap.addClass( 'mmtl-has-image' );
						
						$image
							.removeClass( 'mmtl-image-h mmtl-image-v' )
							.addClass( thumbnail.width > thumbnail.height ? 'mmtl-image-h' : 'mmtl-image-v' )
							.attr( 'src', thumbnail.file )
							.show();
					};
				});
			};
		});
	});
});

})();

/*
------------------------------------------------------------------------------------------------------------------------
 Component Source
------------------------------------------------------------------------------------------------------------------------
*/

(function()
{
	function openComponentSourceEdit( component, ed )
	{
		var shortcode = ed.components.getShortcode( component );

		var source = shortcode.string()

		var data = 
		{
			source : source
		};

		var $page = jQuery( wp.template( 'mmtl-component-source-edit' )( data ) );
		var $form   = $page.find( 'form' );
		var $input  = $form.find( 'textarea[name="source"]' );
		var $output = $form.find( '.notice' );

		$output.hide();

		$form.on( 'submit', function( event )
		{
			event.preventDefault();

			var input = $input.val();

			// checks if update is needed

			if ( input != source )
			{
				// validates input

				var result = wp.shortcode.next( shortcode.tag, input, 0 );

				if ( ! result 
						|| ! result.hasOwnProperty( 'shortcode' ) 
						|| ! result.shortcode instanceof wp.shortcode 
						|| typeof shortcode.content === 'undefined'  )
				{
					$output.show();

					return false;
				};

				// updates dom

				var html = ed.do_shortcode( result.shortcode.string() );

				jQuery( component ).replaceWith( html );

				ed.updateSource();
				ed.update();
			};

			jQuery.featherlight.close();
			
		});

		jQuery.featherlight( $page,
		{
			namespace : 'mmtl-lightbox',
			persist : true,
			afterOpen : function()
			{
				$input.focus(function(){ this.select() }).focus();
			}
		});
	}

	MMTL_Editor.eventManager.add( 'setup', function( event, ed )
	{
		ed.add_control( 'source',
		{
			text : ed.option( 'control_label_source' ),
			title : ed.option( 'control_label_source' ),
			icon : 'editor-code',
			click : function( component )
			{
				openComponentSourceEdit( component, ed );
			}
		});

		ed.on( 'shortcode_update', function( event, shortcode, old_shortcode, component )
		{

		});
	});

})();

/*
------------------------------------------------------------------------------------------------------------------------
 Row Shortcode
------------------------------------------------------------------------------------------------------------------------
*/

(function()
{
	function getColumnLayout( row, ed )
	{
		var $cols = jQuery( row ).find( '> .mmtl-component-inner > .mmtl-component-content > .mmtl-component[data-type="mmtl-col"]' );

		var layout = [], shortcode;

		jQuery.each( $cols, function( i, col )
		{
			shortcode = ed.components.getShortcode( col );
				
			var width = shortcode.get( 'width' );

			if ( ! width )
			{
				return true;
			};

			layout.push( width );
		});

		return layout.join( ' + ' );
	};

	function setColumnLayout( row, layout, ed )
	{
		if ( layout )
		{
			layout = layout.replace( /\s+/g, '' ).split( '+' );
		}

		else
		{
			layout = [];
		};

		var $cols = jQuery( row ).find( '> .mmtl-component-inner > .mmtl-component-content > .mmtl-component[data-type="mmtl-col"]' );
		var col, shortcode;

		jQuery.each( layout, function( i, width )
		{
			// updates column

			if ( i < $cols.length )
			{
				col = $cols.eq( i );
				
				shortcode = ed.components.getShortcode( col );

				shortcode.set( 'width', width );

				ed.components.setShortcode( col, shortcode );
			}

			// creates column 

			else
			{
				shortcode = new wp.shortcode(
				{
					tag : 'mmtl-col',
					attrs :
					{
						width : width
					},
					type : 'closed'
				});

				col = ed.components.create( shortcode );

				ed.components.add( col, row );
			};
		});

		// deletes columns

		jQuery.each( $cols.filter( ':gt(' + ( layout.length - 1 ) + ')' ), function()
		{
			ed.components.remove( this );
		});
	};

	MMTL_Editor.eventManager.add( 'setup', function( event, ed )
	{
		ed.add_shortcode( 'mmtl-row', function( attrs, content )
		{
			return ed.do_shortcode( content );
		});

		ed.on( 'settings_page', function( event, $page, shortcode, component )
		{
			if ( shortcode.tag != 'mmtl-row' )
			{
				return;
			};

			$page.find( 'input[name="layout"]' ).val( getColumnLayout( component, ed ) );

			$page.find( '.mmtl-layout a' ).click( function( event )
			{
				var $button = jQuery( this );

				var layout = $button.attr( 'title' );

				$page.find( ':input[name="layout"]' ).val( layout );

				$page.find( 'a' ).removeClass( 'mmtl-active' );

				$button.addClass( 'mmtl-active' );

				return false;
			});

			var layout = $page.find( 'input[name="layout"]' ).val();
			
			$page.find( '.mmtl-layout a[title="' + layout + '"]' ).addClass( 'mmtl-active' );
		});

		ed.on( 'settings_update', function( event, $page, shortcode, component )
		{
			if ( shortcode.tag == 'mmtl-row' )
			{
				setColumnLayout( component, $page.find(':input[name="layout"]').val(), ed );
			};
			
		});	

		ed.add_filter( 'component', function( component, shortcode )
		{
			// adds component class 

			if ( shortcode.tag == 'mmtl-row' )
			{
				var $component = jQuery( component );

				$component
					.find( '> .mmtl-component-inner > .mmtl-component-content' )
						.addClass( 'mmtl-row' );

				component = MMTL_Editor.helpers.toHTML( $component );
			};

			return component;
		});

		ed.add_filter( 'default_options', function( options )
		{
			return jQuery.extend( options,
			{
				meta_title_id       : 'ID',
				meta_title_class    : 'Class',
				meta_title_bg_image : 'Background image'
			});
		});
		
		ed.add_filter( 'source_content', function( content )
		{
			content = jQuery.trim( content );

			if ( content && content.indexOf( '[mmtl-row' ) !== 0 )
			{
				// TODO : not OOP

				content = '[mmtl-row][mmtl-col width="1/1"][mmtl-text]' + content + '[/mmtl-text][/mmtl-col][/mmtl-row]';

				ed.on( 'init', function( event )
				{
					ed.updateSource();
				});
			};

			return content;
			
		}, 5 );
	});

})();

/*
------------------------------------------------------------------------------------------------------------------------
 Column Shortcode
------------------------------------------------------------------------------------------------------------------------
*/

(function()
{
	function getColumnWidth( span )
	{
		switch( span )
		{
			case 1  : return '1/12';
			case 2  : return '1/6';
			case 3  : return '1/4';
			case 4  : return '1/3';
			case 5  : return '5/12';
			case 6  : return '1/2';
			case 7  : return '7/12';
			case 8  : return '2/3';
			case 9  : return '3/4';
			case 10 : return '5/6';
			case 11 : return '11/12';
			default : return '1/1';
		}
	};

	function getColumnSpan( width )
	{
		switch( width )
		{
			case '1/12'  : return 1;
			case '1/6'   : return 2;
			case '1/4'   : return 3;
			case '1/3'   : return 4;
			case '5/12'  : return 5;
			case '1/2'   : return 6;
			case '7/12'  : return 7;
			case '2/3'   : return 8;
			case '3/4'   : return 9;
			case '5/6'   : return 10;
			case '11/12' : return 11;
			default : return 12;
		}
	};

	MMTL_Editor.eventManager.add( 'setup', function( event, ed )
	{
		ed.add_shortcode( 'mmtl-col', function( attrs, content )
		{
			return ed.do_shortcode( content );
		});

		ed.add_control( 'col_decrease_width',
		{
			text : ed.option( 'control_label_col_decrease_width' ),
			title : ed.option( 'control_label_col_decrease_width' ),
			icon : 'arrow-left',
			click : function( component )
			{
				var $col = jQuery( component );

				var shortcode = ed.components.getShortcode( $col );

				var span = getColumnSpan( shortcode.get( 'width' ) );

				if ( span <= 1 )
				{
					return;
				};

				$col.removeClass( 'mmtl-col-sm-' + span );

				span--;

				$col.addClass( 'mmtl-col-sm-' + span );

				var width = getColumnWidth( span );

				shortcode.set( 'width', width );

				$col.find( '.mmtl-control-col_width' )
					.text( width );

				ed.updateSource();
			}
		});

		ed.add_control( 'col_increase_width',
		{
			text : ed.option( 'control_label_col_increase_width' ),
			title : ed.option( 'control_label_col_increase_width' ),
			icon : 'arrow-right',
			click : function( component )
			{
				var $col = jQuery( component );

				var shortcode = ed.components.getShortcode( $col );

				var span = getColumnSpan( shortcode.get( 'width' ) );

				if ( span >= 12 )
				{
					return;
				};

				$col.removeClass( 'mmtl-col-sm-' + span );

				span++;

				$col.addClass( 'mmtl-col-sm-' + span );

				var width = getColumnWidth( span );

				shortcode.set( 'width', width );

				$col.find( '.mmtl-control-col_width' )
					.text( width );

				ed.updateSource();
			}
		});

		ed.add_control( 'col_width',
		{
			text : ed.option( 'control_label_col_width' ),
			title : ed.option( 'control_label_col_width' ),
			icon : '',
			click : function(){}
		});

		ed.add_filter( 'component', function( component, shortcode )
		{
			if ( shortcode.tag == 'mmtl-col' )
			{
				var $component = jQuery( component );

				$component.addClass( 'mmtl-col' )

				// offset

				if ( value = shortcode.get( 'offset' ) )
				{
					$component.addClass( 'mmtl-col-sm-offset-' + getColumnSpan( value ) );
				}

				// width

				if ( ! shortcode.get( 'width' ) )
				{
					shortcode.set( 'width', '1/1' );
				};

				var width = shortcode.get( 'width' );

				$component.addClass( 'mmtl-col-sm-' + getColumnSpan( width ) );

				$component.find( '.mmtl-control-col_width' )
					.text( width );

				component = $component.get(0);
			};

			return component;
		});

		ed.add_filter( 'component_meta', function( meta, shortcode )
		{
			if ( shortcode.tag == 'mmtl-col' )
			{
				// push

				if ( value = shortcode.get( 'push' ) )
				{
					meta.push( { title : ed.option( 'meta_title_push' ), type : 'push', text : value + ' <span class="dashicons dashicons-arrow-right"></span> ' } );
				};

				// pull

				if ( value = shortcode.get( 'pull' ) )
				{
					meta.push( { title : ed.option( 'meta_title_pull' ), type : 'pull', text : '<span class="dashicons dashicons-arrow-left"></span> ' + value } );
				};
			};

			return meta;
		});

		ed.add_filter( 'default_options', function( options )
		{
			return jQuery.extend( options,
			{
				meta_title_push : 'Push',
				meta_title_pull : 'Pull',
				control_label_col_width  : 'Width',
				control_label_col_increase_width : 'Increase width',
				control_label_col_decrease_width : 'Decrease width'
			});
		});
	});

})();

/*
------------------------------------------------------------------------------------------------------------------------
 Heading Shortcode
------------------------------------------------------------------------------------------------------------------------
*/

(function()
{
	MMTL_Editor.eventManager.add( 'setup', function( event, ed )
	{
		ed.add_shortcode( 'mmtl-heading', function( attrs, content )
		{
			var data = jQuery.extend(
			{
				content : content
			}, attrs );

			return wp.template( 'mmtl-shortcode-heading' )( data );
		});
	});

})();

/*
------------------------------------------------------------------------------------------------------------------------
 Icon Shortcode
------------------------------------------------------------------------------------------------------------------------
*/

(function()
{
	MMTL_Editor.eventManager.add( 'setup', function( event, ed )
	{
		ed.add_shortcode( 'mmtl-icon', function( attrs, content )
		{
			var data = jQuery.extend( {}, attrs ); // makes sure we have an object

			return wp.template( 'mmtl-shortcode-icon' )( data );
		});

		ed.on( 'settings_page', function( event, $page, shortcode, component )
		{
			if ( shortcode.tag != 'mmtl-icon' )
			{
				return;
			};

			var $picker = $page.find( '.mmtl-icon-picker' );
			var $output = $picker.find( '.mmtl-icon-picker-output' );
			var $icons = $picker.find( '.mmtl-icon-picker-icons' );
			var $select = $picker.find( 'select.mmtl-icon-picker-lib' );
			var $field = $page.find( ':input[name="icon"]' );
			var $search = $picker.find( '.mmtl-icon-picker-search' );

			$output.hide();

			$search.prop( 'disabled', true );

			$select.on( 'change', function( event )
			{
				var lib_id = jQuery( this ).val();

				$search.val( '' );

				if ( ! lib_id )
				{
					$icons.empty();

					$search.prop( 'disabled', true );

					return false;
				};

				$output.find('p').empty();

				ed.doAjax( 'get_icon_picker', { lib_id : lib_id }, function( response )
				{
					if ( ! response.success )
					{
						$output.find( 'p' ).text( response.data );
						$output.show();

						return;
					};

					var html = response.data;
					
					$icons.html( html );

					$icons.find( 'li' ).on( 'click', function()
					{	
						$icon = jQuery( this );

						$icons.find( 'li.mmtl-active' ).removeClass( 'mmtl-active' );

						$icon.addClass( 'mmtl-active' );

						$field.val( $icon.data( 'id' ) );
					});

					// sets active icon

					var active = $field.val();

					if ( active )
					{
						$icons.find( 'li[data-id="' + active + '"]' )
							.addClass( 'mmtl-active' );
					};

					$search.prop( 'disabled', false );
				});

			}).trigger( 'change' );

			// search

			$search.keyup(function( event )
			{
				var s = jQuery(this).val();

				$icons.find( 'li' ).show().filter(function()
				{
					return jQuery(this).data('id').indexOf( s ) == -1;

				}).hide();
			});
		});
	});

})();

/*
------------------------------------------------------------------------------------------------------------------------
 Button Shortcode
------------------------------------------------------------------------------------------------------------------------
*/

(function()
{
	MMTL_Editor.eventManager.add( 'setup', function( event, ed )
	{
		ed.add_shortcode( 'mmtl-button', function( attrs, content )
		{
			var data = jQuery.extend(
			{
				content : content
			}, attrs );

			return wp.template( 'mmtl-shortcode-button' )( data );
		});
	});

})();

/*
------------------------------------------------------------------------------------------------------------------------
 Space Shortcode
------------------------------------------------------------------------------------------------------------------------
*/

(function()
{
	MMTL_Editor.eventManager.add( 'setup', function( event, ed )
	{
		ed.add_shortcode( 'mmtl-space', function( attrs, content )
		{
			var data = jQuery.extend(
			{
				content : content
			}, attrs );

			return wp.template( 'mmtl-shortcode-space' )( data );
		});
	});

})();

/*
------------------------------------------------------------------------------------------------------------------------
 Text Shortcode
------------------------------------------------------------------------------------------------------------------------
*/

(function()
{
	MMTL_Editor.eventManager.add( 'setup', function( event, ed )
	{
		ed.add_shortcode( 'mmtl-text', function( attrs, content )
		{
			var data = jQuery.extend(
			{
				content : wp.editor.autop( content )
			}, attrs ); // makes sure we have an object

			return wp.template( 'mmtl-shortcode-html' )( data );
		});

		ed.on( 'settings_page', function( event, $page, shortcode, component )
		{
			if ( shortcode.tag != 'mmtl-text' )
			{
				return;
			};

			$page.find( ':input[name="content"]' ).val( shortcode.content || '' );

			MMTL_Editor.helpers.initWPEditor( 'mmtl_content' );
		});

		ed.on( 'settings_submit', function( event, $page, shortcode, component )
		{
			if ( shortcode.tag != 'mmtl-text' )
			{
				return;
			};

			switchEditors.go( 'mmtl_content', 'html' );
		});
	});

})();

/*
------------------------------------------------------------------------------------------------------------------------
 Logging
------------------------------------------------------------------------------------------------------------------------
*/

MMTL_Editor.eventManager.add( 'setup', function( event, ed )
{
	ed.on( 'update', function( e, new_source, old_source )
	{
		ed.doLog( e.type );
	});

	ed.on( 'source_update', function( e, new_source, old_source )
	{
		ed.doLog( e.type );
	});
});

/*
------------------------------------------------------------------------------------------------------------------------
 Editor activation/deactivation
------------------------------------------------------------------------------------------------------------------------
*/

(function()
{
	function activate_editor( textarea_id )
	{
		var $textarea = jQuery( 'textarea#' + textarea_id );

		// stops when textarea can't be found

		if ( $textarea.length === 0 )
		{
			return false;
		};

		// checks if textarea is part of wp editor

		var $wp_editor = jQuery( '#wp-' + textarea_id + '-wrap' );

		if ( $wp_editor.length == 0 )
		{
			return false;
		}

		// switches to text view when mce is active

		if ( $wp_editor.hasClass( 'tmce-active' ) )
		{
			if ( typeof tinymce !== 'undefined' )
			{
				var mce = tinymce.get( textarea_id );

				if ( mce && mce.initialized )
				{
					switchEditors.go( textarea_id, 'html' );
				}

				else
				{
					// TODO : unbind event handlers

					tinymce.on( 'SetupEditor', function( editor )
					{
						if ( editor.id == 'content' )
						{
							editor.on( 'init', function()
							{
								switchEditors.go( textarea_id, 'html' );
							});
						}
					});
				}
			};
		};

		// creates editor

		var $wrap = $wp_editor.closest( '#postdivrich' );
		
		var $mmtl_wrap = jQuery( '<div class="postbox"></div>' );

		$mmtl_wrap.insertAfter( $wrap.hide() );

		MMTL_Editor.create( $mmtl_wrap, textarea_id, MMTL_Options );

		jQuery( 'body' ).removeClass( 'mmtl-inactive' ).addClass( 'mmtl-active' );

		return true;
	};

	function deactivate_editor( textarea_id )
	{
		var $mmtl_wrap = jQuery( '#mmtl-' + textarea_id + '-wrap' );

		// checks if active

		if ( $mmtl_wrap.length == 0 )
		{
			return false;
		};

		// checks if textarea is part of wp editor

		var $wp_editor = jQuery( '#wp-' + textarea_id + '-wrap' ), $wrap;

		if ( $wp_editor.length == 0 )
		{
			return false;
		};

		$wrap = $wp_editor.closest( '#postdivrich' );

		// sets 'Visual' view.
		// also cause of problem: sometimes 'Visual' view mixed with 'Text' view
		// changing view fixes this

		switchEditors.go( textarea_id, 'html' );
		switchEditors.go( textarea_id, 'tmce' );

		// removes editor

		MMTL_Editor.remove( textarea_id );

		$mmtl_wrap.remove();

		$wrap.show();

		jQuery( 'body' ).removeClass( 'mmtl-active' ).addClass( 'mmtl-inactive' );

		return true;
	};

	function do_request( args, done )
	{
		args = jQuery.extend( args,
		{
			[ MMTL_Options.noncename ] : MMTL_Options.nonce
		});

		return jQuery.post( MMTL_Options.ajaxurl, args, done );
	};

	jQuery( document ).ready(function()
	{
		jQuery( '.mmtl-activate' ).on( 'click', function( event )
		{
			var active = activate_editor( MMTL_Options.post_editor_id );

			// saves editor state

			if ( active )
			{
				do_request( { action : 'mmtl_set_editor_state', post_id : MMTL_Options.post_id, active : 1 } );
			};

			return false;
		});

		jQuery( '.mmtl-deactivate' ).on( 'click', function( event )
		{
			var inactive = deactivate_editor( MMTL_Options.post_editor_id );

			// saves editor state
			
			if ( inactive ) 
			{
				do_request( { action : 'mmtl_set_editor_state', post_id : MMTL_Options.post_id, active : 0 } );
			};

			return false;
		});

		if ( jQuery( 'body' ).hasClass( 'mmtl-active' ) )
		{
			activate_editor( MMTL_Options.post_editor_id );
		}
	});

})();
