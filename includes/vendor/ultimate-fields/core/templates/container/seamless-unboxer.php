<script type="text/javascript">
(function() {
	var all = document.querySelectorAll( '.uf-container' ), uf  = all[ all.length - 1 ], box = uf, children, i;

	while( ! box.classList.contains( 'postbox' ) ) box = box.parentNode;
	box.classList.remove( 'postbox' );
	box.classList.add( 'uf-container-seamless' );
	children = box.querySelectorAll( '.handlediv,.hndle' );
	for(i=0; i<children.length; i++)
		box.removeChild( children[ i ] );
})();
</script>
