<div id="wp-<%= mceID %>_id-wrap" class="wp-core-ui wp-editor-wrap tmce-active" data-mce-id="<%= mceID %>">
    <div id="wp-<%= mceID %>_id-editor-tools" class="wp-editor-tools hide-if-no-js">
        <div id="wp-<%= mceID %>_id-media-buttons" class="wp-media-buttons"><?php do_action( 'media_buttons', $id . '_id' ) ?></div>
            <div class="wp-editor-tabs">
                <button type="button" id="<%= mceID %>_id-tmce" class="wp-switch-editor switch-tmce" data-wp-editor-id="<%= mceID %>_id"><?php _e( 'Visual' ) ?></button>
                <button type="button" id="<%= mceID %>_id-html" class="wp-switch-editor switch-html" data-wp-editor-id="<%= mceID %>_id"><?php _e( 'Text' ) ?></button>
            </div>
        </div>

        <div id="wp-<%= mceID %>_id-editor-container" class="wp-editor-container">
            <div id="qt_<%= mceID %>_id_toolbar" class="quicktags-toolbar"></div>
            <textarea class="wp-editor-area" rows="<%= rows %>" autocomplete="off" cols="40" id="<%= mceID %>_id"></textarea>
        </div>
    </div>
</div>