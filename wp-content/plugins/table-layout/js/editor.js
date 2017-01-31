(function()
{
/*
INDEX
1. Main
2. Options
3. Filters
4. Shortcodes
5. Components
6. Controls
7. Screens
8. Ajax
10. jQuery functions

------------------------------------------------------------------------------------------------------------------------
 1. Main
------------------------------------------------------------------------------------------------------------------------
*/

var MMTL_Editor =
{
	_editors : {},

	create : function( elem, textarea_id, options )
	{
		var editor_id = textarea_id;

		var editor = new MMTL_Editor( editor_id, elem, textarea_id, options );

		this._editors[ editor_id ] = editor;

		this.eventManager.dispatch( 'editor_added', editor );
	},

	get : function( editor_id )
	{
		if ( ! this._editors.hasOwnProperty( editor_id ) )
		{
			return null;
		};

		return this._editors[ editor_id ];
	},

	remove : function( editor_id )
	{
		var editor = this.get( editor_id );

		if ( ! editor )
		{
			return;
		};

		delete this._editors[ editor_id ];

		editor.destroy();

		this.eventManager.dispatch( 'editor_removed', editor );

		delete editor;
	}
};

MMTL_Editor.eventManager =
{
	dispatch : function( type, args )
	{
		jQuery( document ).trigger( this._prefix( type ), args );
	},

	add : function( type, callback )
	{
		jQuery( document ).on( this._prefix( type ), callback );
	},

	remove : function( type, callback )
	{
		var me = this;

		if ( typeof type === 'undefined' )
		{
			var events = jQuery( document ).data( 'events' );

			jQuery.each( events, function( type, handlers )
			{
				if ( type.indexOf( this._prefix() ) !== 0 )
				{
					return true;
				};

				me.remove( type );
			});

			return;
		};

		if ( typeof callback === 'undefined' )
		{
			jQuery( document ).off( this._prefix( type ) );
		}

		else
		{
			jQuery( document ).off( this._prefix( type ), callback );
		}
	},

	_prefix : function( type )
	{
		type = type || '';

		if ( type.indexOf( 'mmtl-editor:' ) !== 0 )
		{
			type = 'mmtl-editor:' + type;
		};

		return type;
	}
};

MMTL_Editor.helpers =
{
	toHTML : function( elem )
	{
		return jQuery( '<div></div>' ).append( elem ).html();
	},

	getFormData : function( wrap )
	{
		var $wrap = jQuery( wrap );

		if ( ! $wrap.is('form') )
		{
			$wrap = $wrap.find( ':input' );
		}

		var data = {};

		jQuery.each( $wrap.serializeArray(), function( i, obj )
		{
			if ( data.hasOwnProperty( obj.name ) )
			{
				data[ obj.name ] = jQuery.makeArray( data[ obj.name ] );
				data[ obj.name ].push( obj.value );
			}

			else
			{
				data[ obj.name ] = obj.value;
			}
		});

		return data;
	},

	initWPEditor : function( textarea_id )
	{
		// mce

		if ( typeof tinymce === 'undefined' || typeof tinyMCEPreInit === 'undefined' )
		{
			return;
		};

		// Removes instance
		
		tinymce.execCommand( 'mceRemoveEditor', true, textarea_id );

		// instantiates

		var init = tinyMCEPreInit.mceInit[ textarea_id ];

		var $wrap = jQuery( '#wp-' + textarea_id + '-wrap' );

		if ( ( $wrap.hasClass( 'tmce-active' ) || ! tinyMCEPreInit.qtInit.hasOwnProperty( textarea_id ) ) && ! init.wp_skip_init )
		{
			tinymce.init( init );

			if ( ! window.wpActiveEditor )
			{
				window.wpActiveEditor = textarea_id;
			}
		}

		// TODO : quicktags not showing

		if ( typeof quicktags !== 'undefined' )
		{
			quicktags( tinyMCEPreInit.qtInit[ textarea_id ] );

			if ( ! window.wpActiveEditor )
			{
				window.wpActiveEditor = textarea_id;
			}
		}

		window.switchEditors && window.switchEditors.go( textarea_id, 'tmce' );
	}
};

MMTL_Editor = jQuery.extend( function( id, elem, textarea_id, options )
{
	this._id = id;
	this._elem = jQuery( elem );
	this._textarea  = jQuery( '#' + textarea_id );

	this._elem
		.addClass( 'mmtl-editor' )
		.attr( 'id', 'mmtl-' + this.id() + '-wrap' );

	this.options    = new MMTL_Options( this );
	this.events     = new MMTL_Events( this );
	this.filters    = new MMTL_Filters( this );
	this.shortcodes = new MMTL_Shortcodes( this );
	this.components = new MMTL_Components( this );
	this.controls   = new MMTL_Controls( this );
	this.ajax       = new MMTL_Ajax( this );

	this.options._options = jQuery.extend( this.options.getDefaults(), options );

	MMTL_Editor.eventManager.dispatch( 'setup', this );

	// parses main template

	var controls = this.apply_filters( 'header_controls', [] );

	var data =
	{
		controls : this.controls.getHTML( controls )
	};

	this._elem.html( wp.template( 'mmtl-main' )( data ) );

	this.update();

	this.trigger( 'init' );

}, MMTL_Editor );

jQuery.extend( MMTL_Editor.prototype,
{
	constructor : MMTL_Editor,

	_elem : null,
	_textarea  : null,
	options : null,
	events : null,
	filters : null,
	shortcodes : null,
	components : null,
	controls : null,
	ajax : null,

	id : function()
	{
		return this._id;
	},

	option : function( key, value )
	{
		if ( typeof value === 'undefined' )
		{
			return this.options.get( key );
		}

		this.options.set( key, value );
	},

	getContent : function()
	{
		return this.apply_filters( 'content', this.components.toShortcode() );
	},

	setContent : function( content )
	{
		var parsed = this.do_shortcode( content );

		this._elem.find( '.mmtl-content' ).html( parsed );
	},

	add_shortcode : function()
	{
		this.shortcodes.add.apply( this.shortcodes, arguments );
	},

	do_shortcode : function()
	{
		return this.shortcodes.do.apply( this.shortcodes, arguments );
	},

	add_filter : function()
	{
		this.filters.add.apply( this.filters, arguments );
	},

	apply_filters : function()
	{
		return this.filters.apply.apply( this.filters, arguments );
	},

	add_control : function()
	{
		return this.controls.add.apply( this.controls, arguments );
	},

	add_screen : function()
	{
		this.screens.add.apply( this.screens, arguments );
	},

	get_screen : function()
	{
		return this.screens.get.apply( this.screens, arguments );
	},

	trigger : function()
	{
		this.events.dispatchEvent.apply( this.events, arguments );
	},

	on : function()
	{
		this.events.addListener.apply( this.events, arguments );
	},

	doAjax : function()
	{
		return this.ajax.sendRequest.apply( this.ajax, arguments );
	},

	update : function()
	{
		var content = this.apply_filters( 'source_content', this._textarea.val() );

		this.setContent( content );

		this.trigger( 'update', [ content ] );
	},

	updateSource : function()
	{
		var old_source = this._textarea.val();

		var new_source = this.getContent();

		this._textarea.val( new_source );

		this.trigger( 'source_update', [ new_source, old_source ] );
	},

	doLog : function()
	{
		if ( ! this.option( 'debug' ) )
		{
			return;
		};

		var args = Array.prototype.slice.call( arguments );

		args.unshift( '[mmtl_editor#' + this.id() + ']' );

		console.log.apply( console, args );
	},

	destroy : function()
	{
		this.events.dispatchEvent( 'destroy' );
		this.events.removeListeners();

		this._elem
			.removeClass( 'mmtl-editor' )
			.removeAttr( 'id' )
			.empty()
			.off();
	}
});

/*
------------------------------------------------------------------------------------------------------------------------
 2. Options
------------------------------------------------------------------------------------------------------------------------
*/

function MMTL_Options( editor )
{
	this._ed = editor;

	this._options = {}
}

MMTL_Options.prototype =
{
	constructor : MMTL_Options,
	_ed : null,
	_options : {},

	get : function( name )
	{
		if ( ! this._options.hasOwnProperty( name ) )
		{
			return null;
		};

		return this._options[ name ];
	},

	set : function( name, value )
	{
		var me = this;

		if ( typeof name === 'object' )
		{
			jQuery.each( name, function( k, v )
			{
				me.set( k, v );
			});

			return;
		};

		if ( ! this._options.hasOwnProperty( name ) )
		{
			return;
		};

		var oldValue = this._options[ name ];

		if ( oldValue === value )
		{
			return;
		};

		this._options[ name ] = value;

		this._ed.trigger( 'option_update', [ this._ed, value, oldValue ] );
	},

	getDefaults : function()
	{
		return this._ed.apply_filters( 'default_options', {} );
	},
};

/*
------------------------------------------------------------------------------------------------------------------------
 2. Events
------------------------------------------------------------------------------------------------------------------------
*/

function MMTL_Events( editor )
{
	this._ed = editor;
}

MMTL_Events.prototype =
{
	constructor : MMTL_Events,
	_ed : null,

	dispatchEvent : function( type )
	{
		this._ed._elem.trigger.apply( this._ed._elem, arguments );
	},

	addListener : function( type, callback )
	{	
		this._ed._elem.on.apply( this._ed._elem, arguments );
	},

	removeListeners : function()
	{
		this._ed._elem.off();
	}
};

/*
------------------------------------------------------------------------------------------------------------------------
 3. Filters
------------------------------------------------------------------------------------------------------------------------
*/

function MMTL_Filters( editor )
{
	this._ed = editor;

	this._filters =  {};
}

MMTL_Filters.prototype =
{
	constructor : MMTL_Filters,

	_ed : null,
	_filters : {},

	add : function( tag, callback, priority )
	{
		if ( typeof priority === 'undefined' )
		{
			priority = 10;
		};

		if ( ! this._filters.hasOwnProperty( tag ) )
		{
			this._filters[ tag ] = {};
		};

		var filters = this._filters[ tag ];

		if ( ! filters.hasOwnProperty( priority ) )
		{
			filters[ priority ] = [];
			
			// sorts filters on priority

			var keys = Object.keys( filters ).sort(), sorted = {};

			jQuery.each( keys, function( i, key )
			{
				sorted[ key ] = filters[ key ];
			});

			filters = sorted;
		};

		filters[ priority ].push( callback );
	},

	apply : function( tag, value )
	{
		if ( this._filters.hasOwnProperty( tag ) )
		{
			var args = Array.prototype.slice.call( arguments ).slice( 1 );

			jQuery.each( this._filters[ tag ], function( priority, filters )
			{
				jQuery.each( filters, function( i, filter )
				{
					args[0] = filter.apply( this, args );
				});
			});

			value = args[0];
		};

		return value;
	}
};

/*
------------------------------------------------------------------------------------------------------------------------
 4. Shortcodes
------------------------------------------------------------------------------------------------------------------------
*/

function MMTL_Shortcodes( editor )
{
	this._ed = editor;

	this._shortcode_tags = {};
}

MMTL_Shortcodes.prototype =
{
	constructor : MMTL_Shortcodes,

	_ed : null,
	_shortcode_tags : {},

	get : function( tag )
	{
		return this._shortcode_tags[ tag ];
	},

	getTags : function()
	{
		return Object.keys( this._shortcode_tags );
	},

	hasTag : function( tag )
	{
		return this._shortcode_tags.hasOwnProperty( tag );
	},

	add : function( tag, callback )
	{
		this._shortcode_tags[ tag ] = callback;
	},

	do : function( content )
	{
		content = this._ed.apply_filters( 'sanitize_content', content );

		var me = this, replacement;

		jQuery.each( this._shortcode_tags, function( tag, callback )
		{
			content = wp.shortcode.replace( tag, content, function( shortcode )
			{
				// creates copy of shortcode object
				// cause of problem when updating shortcodes with same text.
				// same shortcode instance?

				shortcode = me._ed.shortcodes.copyShortcodeObject( shortcode );

				replacement = callback( shortcode.attrs.named, shortcode.content || '' );

				replacement = me._ed.apply_filters( 'shortcode_replacement', replacement, shortcode );

				// makes sure replacement is String

				replacement = MMTL_Editor.helpers.toHTML( replacement );

				return replacement;
			});
		});

		return content;
	},

	copyShortcodeObject : function( shortcode )
	{
		return new wp.shortcode(
		{
			tag     : shortcode.tag,
			attrs   : shortcode.attrs.named,
			type    : shortcode.type,
			content : shortcode.content || ''
		});
	}
};

/*
------------------------------------------------------------------------------------------------------------------------
 5. Components
------------------------------------------------------------------------------------------------------------------------
*/

function MMTL_Components( editor )
{
	this._ed = editor;
}

MMTL_Components.prototype =
{
	constructor : MMTL_Components,

	_ed : null,
	_counter : 0,
	_data : {},

	create : function( shortcode, content )
	{
		var id = ++this._counter;

		var data = this.getData( shortcode.tag );

		var meta = this._ed.apply_filters( 'component_meta', [], shortcode );

		var component = wp.template( 'mmtl-component' )(
		{
			id       : id,
			meta     : this.getMetaHTML( meta ),
			tag      : shortcode.tag,
			type     : data.type,
			content  : content || '',
			controls : this._ed.controls.getHTML( data.controls )
		});

		this._data[ id ] = shortcode;

		component = this._ed.apply_filters( 'component', component, shortcode );

		return component;
	},

	get : function( component_id )
	{
		var $component = this._ed._elem.find( '.mmtl-component[data-id="' + component_id + '"]' );

		if ( $component.length == 0 )
		{
			return null;
		};

		return $component;
	},

	getData : function( shortcode_tag )
	{
		var components = this._ed.option( 'components' );

		return components[ shortcode_tag ] || null;
	},

	getMetaHTML : function( meta )
	{
		html = '';

		jQuery.each( meta, function( i, data )
		{
			data = jQuery.extend(
			{
				title : '',
				type : '',
				text : ''
			}, data );

			html += jQuery.trim( wp.template( 'mmtl-meta' )( data ) );
		});

		return html;
	},

	add : function( component, parentComponent, index, context )
	{
		var defaultContainer = this._ed._elem.find( '.mmtl-content' ), container, me = this;

		if ( typeof parentComponent === 'undefined' || ! parentComponent || jQuery( parentComponent ).length == 0 )
		{
			container = defaultContainer;
		}

		else if ( jQuery( parentComponent ).hasClass( 'mmtl-component' ) )
		{
			container = jQuery( parentComponent ).find( '> .mmtl-component-inner > .mmtl-component-content' );
		}

		else if ( jQuery( parentComponent ).closest( '.mmtl-component-content' ).length > 0 )
		{
			container = jQuery( parentComponent ).closest( '.mmtl-component-content' );
		}

		else
		{
			container = defaultContainer;
		}

		if ( typeof index === 'undefined' || index === false )
		{
			index = jQuery( container ).children().length;
		};

		if ( index <= 0 )
		{
			jQuery( container ).prepend( component );
		}

		else if ( index >= jQuery( container ).children().length )
		{
			jQuery( container ).append( component );
		}

		else
		{
			jQuery( component ).insertBefore( jQuery( container ).children().eq( index ) );
		}

		jQuery( component ).find( '.mmtl-component' ).andSelf().each( function()
		{
			me._ed.trigger( 'component_added', [ this, context ] );
		});
	},

	remove : function( component )
	{
		// removes models attached to component

		var parent, index, me = this;

		var components = jQuery( component ).find( '.mmtl-component' ).andSelf();

		components.each(function()
		{
			parent = jQuery(this).closest( '.mmtl-component' );
			index  = jQuery(this).index();

			me._ed.components.removeShortcode( this );

			jQuery(this).remove();
			
			me._ed.trigger( 'component_removed', [ this, parent, index ] );
		});
	},

	copy : function( component )
	{
		var content = this.toShortcode( component );

		return this._ed.do_shortcode( content );
	},

	toShortcode : function( component )
	{
		var me = this;

		if ( typeof component === 'undefined' )
		{
			// iterates through top level components

			var str = '';

			this._ed._elem.find( '.mmtl-content > .mmtl-component' ).each(function()
			{
				str += me._ed.components.toShortcode( this );
			});

			return str;			
		};

		var shortcode = this.getShortcode( component );

		var content = jQuery( component ).find( '> .mmtl-component-inner > .mmtl-component-content' );

		var str = '';

		str += '[' + shortcode.tag;

		jQuery.each( shortcode.attrs.named, function( key, value )
		{
			// replaces double quotes by single quotes
			value = value.replace( /"/g, "'" );

			str += ' ' + key + '="' + value + '"';
		});

		if ( shortcode.type === 'single' )
		{
			return str + ']';
		}

		else if ( shortcode.type === 'self-closing' )
		{
			return str + ' /]';
		}

		str += ']';

		if ( content.find( '> .mmtl-component' ).length > 0 )
		{
			jQuery.each( content.find( '> .mmtl-component' ), function()
			{
				str += me._ed.components.toShortcode( this );
			});

			shortcode.content = '';
		}

		else
		{
			str += shortcode.content || '';
		}

		str += '[/' + shortcode.tag + ']';

		return str;
	},

	getShortcode : function( component )
	{
		var id = jQuery( component ).data( 'id' );
		
		return this._data[ id ];
	},

	setShortcode : function( component, shortcode )
	{
		var id = jQuery( component ).data( 'id' );
		
		var old_shortcode = this._data[ id ];

		this._data[ id ] = shortcode;

		this._ed.trigger( 'shortcode_update', [ shortcode, old_shortcode, component ] );
	},

	removeShortcode : function( component )
	{
		var id = jQuery( component ).data( 'id' );

		jQuery( component ).attr( 'data-id', '' );

		delete this._data[ id ];
	}
};

/*
------------------------------------------------------------------------------------------------------------------------
 6. Controls
------------------------------------------------------------------------------------------------------------------------
*/

function MMTL_Controls( editor )
{
	this._ed = editor;
}

MMTL_Controls.prototype =
{
	constructor: MMTL_Controls,

	_ed : null,
	_controls : {},

	add : function( id, options )
	{
		var control = jQuery.extend(
		{
			id : id,
			text : '',
			title : '',
			icon : '',
			click : function(){}
		}, options );

		this._controls[ control.id ] = control;
	},

	get : function( id )
	{
		var me = this;

		if ( typeof id === 'undefined' )
		{
			return this._controls;
		};

		if ( typeof id === 'object' )
		{
			var controls = [], control;

			jQuery.each( id, function( i, key )
			{
				control = me.get( key );

				if ( ! control )
				{
					return true;
				};

				controls.push( control );
			});

			return controls;
		};

		if ( ! this._controls.hasOwnProperty( id ) )
		{
			return null;
		};

		return this._controls[ id ];
	},

	getHTML : function( id )
	{
		if ( typeof id !== 'undefined' && typeof id !== 'object' )
		{
			id = [ id ];
		};

		var controls = this.get( id );

		var html = '';

		jQuery.each( controls, function( i, control )
		{
			html += wp.template( 'mmtl-control' )( control );
		});

		return html;
	}
};

/*
------------------------------------------------------------------------------------------------------------------------
 8. Ajax
------------------------------------------------------------------------------------------------------------------------
*/

function MMTL_Ajax( editor )
{
	this._ed = editor;

	this._ed.add_filter( 'default_options', function( options )
	{
		return jQuery.extend( options,
		{
			noncename : '_wpnonce',
			nonce : '',
			ajaxurl : ''
		});
	});
}

MMTL_Ajax.prototype =
{
	constructor : MMTL_Ajax,

	_ed : null,

	sendRequest : function( action, data, success )
	{
		var me = this;

		var loader = this._ed._elem.find( '.mmtl-loader' ).show();

		if ( action )
		{
			var actionPrefix = 'mmtl_';

			if ( action.indexOf( actionPrefix ) !== 0 )
			{
				action = actionPrefix + action;
			};

			data = jQuery.extend( data,
			{
				action : action,
				[ this._ed.option( 'noncename' ) ] : this._ed.option( 'nonce' )
			});
		};

		return jQuery.post( this._ed.option( 'ajaxurl' ), data, success )

			.always( function( data, textStatus, errorThrown )
			{ 
				loader.hide();
			});
	}
};

/*
------------------------------------------------------------------------------------------------------------------------
 10. jQuery functions
------------------------------------------------------------------------------------------------------------------------
*/

jQuery.fn.mmtl_prepare_tabs = function( options )
{
    var options = jQuery.extend(
    {
        separator : 'h3',
        heading : null,
        not : '',
    }, options );
 
    return this.each(function( e )
    {
        var $nav = jQuery( '<ul></ul>' );
 
        jQuery(this).find( options.separator ).each(function()
        {
        	var $header;

        	if ( options.heading )
        	{
        		$header = jQuery( this ).find( options.heading );
        	}

        	else
        	{
        		$header = jQuery( this );
        	}

            var tabID = 'tab-' + $header.text().replace( /\s+/g, '-' ).toLowerCase();
 
            var $wrap = jQuery('<div></div>');
 
            $wrap
                .attr( 'id', tabID );
 
            $header.nextUntil( options.separator ).andSelf().wrapAll( $wrap );
        
            var $navItem = jQuery('<li><a></a></li>');
 
            $navItem
                .find( 'a' )
                    .attr( 'href', '#' + tabID )
                    .text( $header.text() );
 
            $nav.append( $navItem );
        });
 
        if ( $nav.find( '> li' ).length > 0 )
        {
            jQuery(this).prepend( $nav );
        };
    });
};

/* ------------------------------------------------------------------------------------------------------------------ */

window.MMTL_Editor = MMTL_Editor;

})();