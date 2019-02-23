<% if( ! hide_label ) { %>
<div class="uf-field-label uf-media-setting-label">
	<label>
		<%= label %><% if( required ){ %><span class="uf-field-star">*</span><% } %>
	</label>

	<% if( 'label' == description_position && description.length ){ %>
	<div class="uf-field-description"><%= description %></div>
	<% } %>
</div>
<% } %>

<div class="uf-field-input-wrap uf-media-setting-wrap">
	<div class="uf-field-input"></div>

	<% if( 'label' != description_position && description ){ %>
	<div class="uf-field-description"><%= description %></div>
	<% } %>

	<div class="uf-field-validation-message"></div>
</div>