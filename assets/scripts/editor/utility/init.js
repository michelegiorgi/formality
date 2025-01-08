/**
 * Formality editor page load
 */

const { select, dispatch } = wp.data

//add half-width class to formality blocks
const halfWidthFields = () => {
  var el = wp.element.createElement
  var formalityBlockWidth = wp.compose.createHigherOrderComponent(( BlockListBlock ) => {
    return (props) => {
      var newProps = props.attributes.halfwidth ? lodash.assign({}, props, { className: 'wp-block--halfwidth' }) : props
      return el( BlockListBlock, newProps )
    };
  }, 'formality_block-width' )
  wp.hooks.addFilter( 'editor.BlockListBlock', 'formality_block-width', formalityBlockWidth )
}

//force panel open
const forcePanel = () => {
  //force sidebar open
  if(!select('core/edit-post').isEditorSidebarOpened()) {
    dispatch('core/edit-post').openGeneralSidebar('edit-post/document')
  }
  //force panel open
  // check all preferences -> select('core/edit-post').getPreferences()
  if(!select('core/editor').isEditorPanelEnabled('formality-sidebar/formality-sidebar')) {
    dispatch('core/edit-post').toggleEditorPanelEnabled('formality-sidebar/formality-sidebar')
  }
  if(!select('core/editor').isEditorPanelOpened('formality-sidebar/formality-sidebar')) {
    dispatch('core/edit-post').toggleEditorPanelOpened('formality-sidebar/formality-sidebar')
  }
}

//trigger footer click
const formFooter = () => {
  document.addEventListener('click', (e) => {
    if(e.target instanceof HTMLElement && e.target.matches('.block-editor-inserter')) {
      dispatch('core/block-editor').clearSelectedBlock()
      const settingToggle = document.querySelector('.formality-toggle-settings')
      if(settingToggle) settingToggle.click()
      const footerToggle = document.querySelector('.formality-toggle-footer:not(.is-opened) .components-panel__body-toggle')
      if(footerToggle) footerToggle.click()
    }
  }, false)
}

export let pageLoad = () => {
  halfWidthFields()
  wp.domReady(() => {
    formFooter()
    forcePanel()
  })
}
