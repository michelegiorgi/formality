//add half-width class to formality blocks
  var el = wp.element.createElement;
  var formalityBlockWidth = wp.compose.createHigherOrderComponent( function( BlockListBlock ) {
    return function( props ) {
      // eslint-disable-next-line no-undef
      var newProps = props.attributes.halfwidth ? lodash.assign({}, props, { className: "wp-block--halfwidth" }) : props;
      return el( BlockListBlock, newProps );
    };
  }, 'formality_block-width' );
  wp.hooks.addFilter( 'editor.BlockListBlock', 'formality_block-width', formalityBlockWidth );

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

//remove formality blocks from other post type editor
  function removeBlocks() {
    let blocks = [
      'formality/text',
      'formality/email',
      'formality/textarea',
      'formality/step',
      'formality/select',
      'formality/message',
      'formality/number',
      'formality/switch',
      'formality/multiple',
      'formality/rating',
    ];
    blocks.forEach(function(block){
      wp.blocks.unregisterBlockType(block)
    })
  }

//trigger footer click
  function formFooter() {
    $('.edit-post-layout').on('click', '.block-list-appender', function(e){
      if(!$(e.target).is('button')) {
        wp.data.dispatch('core/editor').clearSelectedBlock();
        //wp.data.dispatch('core/edit-post').openGeneralSidebar('edit-post/document')
        $('.formality-toggle-settings').click()
        $('.formality-toggle-footer:not(.is-opened) .components-panel__body-toggle').click()
      }
    })
  }

//launch functions on domready  
  wp.domReady( function() {
    if(document.body.classList.contains('post-type-formality_form')) {
      forcePanel()
      formFooter()
    } else {
      removeBlocks()
    }
  });