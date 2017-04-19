(function( $ ) {
	'use strict';

	CherryJsCore.utilites.namespace( 'cherry_socialize' );

	CherryJsCore.cherry_socialize = {
		init: function() {

			if ( CherryJsCore.status.is_ready ) {
				this.document_ready();

			} else {
				CherryJsCore.variable.$document.on( 'ready', this.document_ready.bind( this ) );
			}
		},

		document_ready: function() {}
	}

	CherryJsCore.cherry_socialize.init();

}( jQuery ) );