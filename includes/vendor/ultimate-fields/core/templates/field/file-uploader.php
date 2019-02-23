<div class="uf-uploader-drop"></div>
<div class="uf-uploader-progressbar"></div>
<% if( multiple ) { %>
<a href="#" class="uf-uploader-trigger"><?php _e( 'Select files', 'ultimate-fields' ) ?></a>
<p class="uf-uploader-text"><?php _e( '... or drag them here', 'ultimate-fields' ) ?></p>
<% } else { %>
<a href="#" class="uf-uploader-trigger"><?php _e( 'Select file', 'ultimate-fields' ) ?></a>
<p class="uf-uploader-text"><?php _e( '... or drag it here', 'ultimate-fields' ) ?></p>
<% } %>