(function() {
	var customize;

	window.UltimateFields = window.UltimateFields || {};

	customize = {
		bound: {},

		bind: function( settingName, callback ) {
			var bound = customize.bound;

			if( ! ( settingName in customize.bound ) ) {
				bound[ settingName ] = [];
			}

			if( -1 == bound[ settingName ].indexOf( callback ) ) {
				bound[ settingName ].push( callback );
			}
		},

		unbind: function( settingName, callback ) {
			var index, bound = customize.bound;

			if( ! ( settingName in customize.bound ) ) {
				return;
			}

			if( -1 == ( index = bound[ settingName ].indexOf( callback ) ) ) {
				return;
			}

			bound[ settingName ].splice( index, 1 );
		},

		receive: function( data ) {
			var setting = data.setting,
				bound   = customize.bound,
				i;

			if( ! ( setting in bound ) )
				return;

			for( i=0; i<bound[ setting ].length; i++ ) {
				bound[ setting ][ i ]( data.value, data.context );
			}
		}
	}

	window.UltimateFields.customize = customize;

	if( 'preview' in wp.customize ) {
		wp.customize.preview.bind( 'uf.context', customize.receive );
	} else {
		wp.customize.bind( 'preview-ready', function() {
			wp.customize.preview.bind( 'uf.context', customize.receive );
		});
	}
})();
