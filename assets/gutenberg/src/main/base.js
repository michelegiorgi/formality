var el = wp.element.createElement;

var formalityBlockWidth = wp.compose.createHigherOrderComponent( function( BlockListBlock ) {
    return function( props ) {
      if(props.attributes.input_halfwidth) {
        var newProps = lodash.assign({}, props, {
          className: "wp-block--halfwidth",
        });
      } else {
        var newProps = props
      }
      return el(
        BlockListBlock,
        newProps
      );
    };
}, 'formality_block-width' );

wp.hooks.addFilter( 'editor.BlockListBlock', 'formality_block-width', formalityBlockWidth );