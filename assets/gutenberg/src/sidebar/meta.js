
const { __ } = wp.i18n;

const { 
  registerPlugin,
} = wp.plugins;

const { 
  PluginSidebar,
} = wp.editPost;

const { 
  createElement,
} = wp.element;

const { 
  ColorPalette,
  PanelBody,
  PanelRow,
  Button,
  TextControl,
  ToggleControl,
  ButtonGroup,
  BaseControl
} = wp.components;

const { 
  RichText,
  MediaUpload,
  InspectorControls
} = wp.editor;

registerPlugin( 'my-plugin-sidebar', {
    render: function() {
        return createElement( PluginSidebar,
            {
                name: 'my-plugin-sidebar',
                icon: 'admin-post',
                title: 'My plugin sidebar',
            },
            'Meta field'
        );
    },
} );