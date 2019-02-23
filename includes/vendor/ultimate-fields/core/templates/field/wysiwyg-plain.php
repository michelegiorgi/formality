<div id="wp-<%= mceID %>_id-wrap" class="wp-core-ui wp-editor-wrap html-active">
	<link rel='stylesheet' id='editor-buttons-css'  href='http://ra.do/uf-test/wp-includes/css/editor.min.css?ver=4.7.1' type='text/css' media='all' />
	<div id="wp-<%= mceID %>_id-editor-tools" class="wp-editor-tools hide-if-no-js">
		<div id="wp-<%= mceID %>_id-media-buttons" class="wp-media-buttons"><?php do_action( 'media_buttons', $id . '_id' ) ?></div>
		<div class="wp-editor-tabs"></div>
	</div>

	<div id="wp-<%= mceID %>_id-editor-container" class="wp-editor-container">
		<div id="qt_<%= mceID %>_id_toolbar" class="quicktags-toolbar"></div>
		<textarea class="wp-editor-area" rows="<%= rows %>" cols="40" name="<%= mceID %>_name" id="<%= mceID %>_id"></textarea>
	</div>
</div>