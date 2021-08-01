//add half-width class to formality blocks
  function halfWidthFields() {
    var el = wp.element.createElement;
    var formalityBlockWidth = wp.compose.createHigherOrderComponent( function( BlockListBlock ) {
      return function( props ) {
        // eslint-disable-next-line no-undef
        var newProps = props.attributes.halfwidth ? lodash.assign({}, props, { className: "wp-block--halfwidth" }) : props;
        return el( BlockListBlock, newProps );
      };
    }, 'formality_block-width' );
    wp.hooks.addFilter( 'editor.BlockListBlock', 'formality_block-width', formalityBlockWidth );
  }

//force panel open
  function forcePanel() {
    //force sidebar open
    if(!wp.data.select('core/edit-post').isEditorSidebarOpened()) {
      wp.data.dispatch('core/edit-post').openGeneralSidebar('edit-post/document')
    }
    //force panel open
    // check all preferences -> wp.data.select('core/edit-post').getPreferences()
    if(!wp.data.select('core/edit-post').isEditorPanelEnabled('formality-sidebar/formality-sidebar')) {
      wp.data.dispatch('core/edit-post').toggleEditorPanelEnabled('formality-sidebar/formality-sidebar')
    }
    if(!wp.data.select('core/edit-post').isEditorPanelOpened('formality-sidebar/formality-sidebar')) {
      wp.data.dispatch('core/edit-post').toggleEditorPanelOpened('formality-sidebar/formality-sidebar')
    }

  }

//trigger footer click
  function formFooter() {
    $(document).on('click', '.block-list-appender', function(e){
      if(!$(e.target).is('button')) {
        wp.data.dispatch('core/block-editor').clearSelectedBlock
        $('.formality-toggle-settings').click()
        $('.formality-toggle-footer:not(.is-opened) .components-panel__body-toggle').click()
      }
    })
  }

export function pageLoad() {
  halfWidthFields()
  //launch functions on domready
  wp.domReady(function() {
    forcePanel()
    formFooter()
  });
}
