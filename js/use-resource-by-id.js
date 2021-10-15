/* global wp:false */
/* global React:false */
( function() {
	var apiFetch = wp.apiFetch;
	var useSelect = wp.data.useSelect;
	var useEffect = React.useEffect;

	/**
	 * Dictionary of requested items: keep an in-memory list of the type (if known)
	 * for each requested ID, to limit unnecessary API requests.
	 */
	var typeById = {};

	/**
	 * Query for a post entity resource without knowing its post type.
	 *
	 * @param {number} id Numeric ID of a post resource of unknown subtype.
	 * @returns {object|undefined} The requested post object, if found and loaded.
	 */
	function useResourceById( id ) {
		const type = typeById[ id ];

		useEffect( function() {
			if ( typeById[ id ] ) {
				return;
			}

			apiFetch( {
				path: '/wp/v2/search?type=post&include=' + id + '&_fields=id,subtype',
			} ).then( ( result ) => {
				if ( result.length ) {
					typeById[ id ] = result[0].subtype;
				}
			} );
		}, [ id ] );

		return useSelect( function( select ) {
			if ( ! type ) {
				return undefined;
			}
			return select( 'core' ).getEntityRecord( 'postType', type, id );
		}, [ id, type ] );
	}

	window.useResourceById = useResourceById;
} )();
