(function()
{
	var MMTL_Updater =
	{
		_options : {},
		_actionIndex : -1,

		init : function( elem, options )
		{	
			var me = this;

			this._elem = jQuery( elem );
			this._options = jQuery.extend( this.getDefaultOptions(), options );

			this._loader = this._elem.find( '.mmtl-loader' );
			this._output = this._elem.find( '.mmtl-output' );
			this._submit = this._elem.find( 'input[type="submit"]' );

			this.on( 'action_before', function( event, action, index )
			{
				me._elem.find( '.mmtl-ajax-hide-on-before' ).hide();

				me._submit
					.prop( 'disabled', true )
					.hide();

				me._loader.show();
			});

			this.on( 'action_complete_success', function( event, action, index, data )
			{
				me._output.append( data );

				if ( me._actionIndex + 1 >= me.option('actions').length )
				{
					return;
				}

				me._actionIndex++;

				setTimeout( function()
				{
					me.doAction( me._actionIndex );
				}, 1000 );
				
			});

			this.on( 'action_complete_error', function( event, action, index )
			{
				
			});

			this.on( 'action_complete', function( event, action, index )
			{
				if ( index < me._actionIndex )
				{
					return;
				}

				me._submit
					.prop( 'disabled', false );

				me._elem.find( '.mmtl-ajax-hide-on-complete' ).hide();
				me._elem.find( '.mmtl-ajax-show-on-complete' ).show();

				me._loader.hide();
			});

			this.on( 'action_error', function( event, action, index )
			{
				
			});

			this._elem.find( 'form' ).submit(function( event )
			{
				me._actionIndex = 0;

				me.doAction( me._actionIndex );

				return false;
			});
		},

		option : function( key )
		{
			return this._options[ key ];
		},

		getAction : function( index )
		{
			var actions = this.option( 'actions' );

			if ( index < 0 || index > actions.length )
			{
				return null;
			};

			return actions[ index ];
		},

		doAction : function( index )
		{
			var action = this.getAction( index );

			if ( ! action )
			{
				return;
			};

			var args =
			{
				action : 'mmtl_updater_process_action',
				action_id : action,
				[ this.option( 'noncename' ) ] : this.option( 'nonce' )
			};

			var me = this;

			this.trigger( 'action_before', [ action, index ] );

			return jQuery.post( this.option( 'ajaxurl' ), args )

				.done( function( response )
				{
					if ( ! response.hasOwnProperty( 'success' ) || ! response.success )
					{
						me.trigger( 'action_complete_error', [ action, index, response.data ] );

						return;
					};

					me.trigger( 'action_complete_success', [ action, index, response.data ] );
				})

				.fail( function()
				{
					me.trigger( 'action_error', [ action, index ] );
				})

				.always( function()
				{
					me.trigger( 'action_complete', [ action, index ] );
				})
		},

		trigger : function()
		{
			var args = Array.prototype.slice.call( arguments );

			if ( args.length > 0 )
			{
				args[0] = 'mmtl_updater:' + args[0];
			};

			jQuery( document ).trigger.apply( jQuery( document ), args );
		},

		on : function()
		{
			var args = Array.prototype.slice.call( arguments );

			if ( args.length > 0 )
			{
				args[0] = 'mmtl_updater:' + args[0];
			};

			jQuery( document ).on.apply( jQuery( document ), args );
		},

		getDefaultOptions : function()
		{
			return {
				ajaxurl : '',
				nonce : '',
				noncename : '',
				actions : []
			};
		}
	};

	jQuery( document ).ready(function()
	{
		MMTL_Updater.init( '#mmtl-updater-screen', MMTL_Updater_Options );
	});

})();