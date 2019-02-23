(function( $ ){

    var registerBlockType = wp.blocks.registerBlockType,
        container         = UltimateFields.Container,
		gutenbergBlock    = container.Gutenberg_Block = {};

    /**
	 * A basic model for taxonomy containers.
	 */
 	gutenbergBlock.Model = container.Base.Model.extend({});

	/**
	 * Handles the views for editing terms.
	 */
 	gutenbergBlock.View = UltimateFields.Container.Base.View.extend({
        render: function() {
            var that   = this;

            // Add a heading
            this.$el.append( $( '<h2 class="uf-block-title" />' ).text( this.model.get( 'title' ) ) );

            // Add the fields
            var $fields = $( '<div class="uf-fields uf-block-fields" />' );
            this.$el.append( $fields );
 			this.addFields( $fields );
        }
    });

    /**
     * This class handles the registration of blocks with Gutenberg.
     */
    gutenbergBlock.Connector = function( container ) {
        this.settings = container.settings;
        this.data     = container.data;

        this.initialize();
    }

    _.extend( gutenbergBlock.Connector.prototype, {
        initialize: function() {
            var connector = this;

            // Prepare the attributes for the block
            var atts = {};
            _.each( this.settings.fields, function( field ) {
                atts[ field.name ] = {}
            });

            // Create the blockType
            registerBlockType( 'ultimate-fields/' + this.settings.block_id, {
                title: this.settings.title,
                icon: this.settings.icon,
                category: this.settings.category,
                attributes: atts,

                edit: function( props ) {
                    return React.createElement( 'div', {
                        className: 'uf-block-wrapper',
                        ref: function( element ) {
                            connector.startContainer( element, props );
                        }
                    });
                },

                save: function() {
                    return null;
                }
            });
        },

        /**
         * Once the DOM element of a block is mounted, this will initialize a container inside.
         */
        startContainer: function( element, props ) {
            if( ! element || element.querySelector( '.uf-fields' ) ) {
                return;
            }

            var settings =  {
                type:     'Gutenberg_Block',
                settings: this.settings
            };

            var data = _.extend( {}, this.data, props.attributes );

            var initialized = UltimateFields.initializeContainer( $( element ), settings, data );
            initialized.datastore.on( 'change', function() {
                var updated = _.extend( {}, props.attributes, initialized.datastore.toJSON() );
                props.setAttributes( updated );
            });
        }
    });

    // Connect all blocks
    _.each( ( ultimate_fields_gutenberg_blocks || [] ), function( container ) {
        new gutenbergBlock.Connector( container );
    });

})( jQuery );
