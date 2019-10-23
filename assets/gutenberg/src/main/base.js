//add half-width class to formality blocks
  var el = wp.element.createElement;
  var formalityBlockWidth = wp.compose.createHigherOrderComponent( function( BlockListBlock ) {
    return function( props ) {
      if(props.attributes.halfwidth) {
        var newProps = lodash.assign({}, props, { className: "wp-block--halfwidth" });
      } else {
        var newProps = props
      }
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
    /*
    if(!wp.data.select('core/edit-post').isEditorPanelOpened('taxonomy-panel-formality_meta')) {
      wp.data.dispatch('core/edit-post').toggleEditorPanelOpened('taxonomy-panel-formality_meta')
    }
    */
    // check all preferences -> wp.data.dispatch('core/edit-post').getPreferences()
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
      'formality/multiple'
    ];
    blocks.forEach(function(block){
      wp.blocks.unregisterBlockType(block);
    })
  }


//launch functions on domready  
  wp.domReady( function() {
    if(document.body.classList.contains('post-type-formality_form')) {
      forcePanel();
    } else {
      removeBlocks();
    }
  });