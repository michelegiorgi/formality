(function( $ ){

	var ui         = window.UltimateFields.UI,
		logicField = UltimateFields.Field.Conditional_Logic = {},
		logic      = UltimateFields.UI.Conditional_Logic = {};

	/**
	 * Saves the data about an individual field.
	 */
	logic.RuleModel = Backbone.Model.extend({
		defaults: {
			selector: '',
			compare: '!=',
			value:    ''
		}
	});

	/**
	 * The view of the rule will include selectors for field, comparator and value.
	 */
	logic.RuleView = Backbone.View.extend({
		className: 'uf-logic-rule',

		events: {
			'click .uf-logic-rule-remove': 'destroy'
		},

		/**
		 * Renders the whole rule.
		 */
		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'conditional-logic-rule' );

			this.$el.html( tmpl() );

			// Indicate that the field is not initialized in order
			// to allow fields to save values without triggering events.
			this.initialized = false;

			// Render all separate selectors
			this.renderFieldSelector();

			// Indicate that everything is already initialized
			this.initialized = true;

			// Once every sub-field has been initialized and has set it's value, send it top
			this.model.trigger( 'change' );
		},

		/**
		 * Renders the selector for choosing fields.
		 */
		renderFieldSelector: function() {
			var that    = this,
				$select = $( '<select />' ), levels, index, currentField;

			// This will hold all fields with their prefixes
			this.fields = {};

			// Some starting values
			currentField = this.field.datastore.get( 'name' );
			selected     = this.model.get( 'selector' ),
			levels       = ui.Context.get();
			index        = levels.length - 1;

			if( 1 == levels.length ) {
				var level = levels[ 0 ];

				that.addSelectOptions( $select, level.get( 'fields' ), function( field ) {
					if( currentField == field.getValue( 'name' ) ) {
						return false;
					}

					if( ! field.getHelper().getComparators() ) {
						return false;
					}

					that.fields[ field.getValue( 'name' ) ] = field;

					return {
						label: field.getValue( 'label' ),
						value: field.getValue( 'name' ),
						selected: field.getValue( 'name' ) == selected
					}
				});
			} else {
				index = levels.length;

				_.each( levels, function( level ) {
					var $group = $( '<optgroup />' )
						.attr( 'label', level.get( 'label' ) );

					that.addSelectOptions( $group, level.get( 'fields' ), function( field ) {
						var prefix;

						if( currentField == field.getValue( 'name' ) ) {
							return false;
						}

						if( ! field.getHelper().getComparators() ) {
							return false;
						}

						prefix = '../'.repeat( index );
						that.fields[ prefix + field.getValue( 'name' ) ] = field;

						return {
							label: field.getValue( 'label' ),
							value: prefix + field.getValue( 'name' ),
							selected: prefix + field.getValue( 'name' ) == selected
						};
					});

					$select.prepend( $group );

					index--;
				});
			}

			that.saveSelector( $select );
			$select.on( 'change', function() {
				that.saveSelector( $select );
			});

			$select.appendTo( this.$el.find( '.uf-logic-rule-field' ) );
		},

		/**
		 * Saves the value of the field select when it changes.
		 */
		saveSelector: function( $select ) {
			var selector = $select.val();

			if( selector != this.model.get( 'selector' ) ) {
				this.model.set({
					selector:   selector,
					compare: '',
					value:      ''
				}, {
					silent: ! this.initialized
				});
			}

			this.renderComparatorSelector();
			this.renderOperand();
		},

		/**
		 * Renders the selector for comparators.
		 */
		renderComparatorSelector: function() {
			var that    = this,
				$select = $( '<select />' ),
				field, selected;

			// Get the current field
			field = this.fields[ this.model.get( 'selector' ) ];
			selected = this.model.get( 'compare' );

			// Add the options to the select
			this.addSelectOptions( $select, field.getHelper().getComparators(), function( comparator ) {
				return {
					value:    comparator.compare,
					label:    comparator.label,
					selected: selected == comparator.compare
				};
			});

			$select.appendTo( this.$el.find( '.uf-logic-rule-comparator' ).empty() );

			that.saveComparator( $select );
			$select.on( 'change', function() {
				that.saveComparator( $select );
			});
		},

		/**
		 * Saves the value of the comparator select when it changes.
		 */
		saveComparator: function( $select ) {
			var selector = $select.val();

			if( selector != this.model.get( 'compare' ) ) {
				this.model.set( 'compare', selector, {
					silent: ! this.initialized
				});
			}

			this.renderOperand();
		},

		/**
		 * Adds the operand field.
		 */
		renderOperand: function() {
			var that    = this,
				field, helper, comparator, current;

			// Get the current field
			field      = this.fields[ this.model.get( 'selector' ) ];
			helper     = field.getHelper();
			current    = this.model.get( 'value' );
			comparator = _.findWhere( helper.getComparators(), {
				compare: this.model.get( 'compare' )
			});

			if( comparator.operand ) {
				// Add the element
				var view = helper.operand( current, that );
				view.$el.appendTo( this.$el.find( '.uf-logic-rule-value' ).show().empty() );
				view.render();

				view.model.datastore.on( 'change', function() {
					that.saveOperand( view.model.getValue() );
				});

				that.saveOperand( view.model.getValue() );
			} else {
				this.$el.find( '.uf-logic-rule-value' ).hide();
				that.saveOperand( false );
			}
		},

		/**
		 * Saves the value of the operand select when it changes.
		 */
		saveOperand: function( operand ) {
			if( operand != this.model.get( 'value' ) ) {
				this.model.set( 'value', operand, {
					silent: ! this.initialized
				});
			}
		},

		/**
		 * Adds options to a select field or an optgroup inside.
		 */
		addSelectOptions: function( $element, options, callback ) {
			var processor = function( option ) {
				var data = callback( option ), $option;

				// Maybe there is no data (ignore the option)
				if( ! data ) {
					return;
				}

				$option = $( '<option />' )
					.attr( 'value', data.value )
					.text( data.label )
					.appendTo( $element );

				if( ( 'selected' in data ) && data.selected ) {
					$option.prop({
						selected: true
					});
				}
			};

			if( options instanceof Backbone.Collection ) {
				options.each( processor );
			} else {
				_.each( options, processor );
			}
		},

		/**
		 * Removes the rule.
		 */
		destroy: function() {
			if( this.model ) this.model.destroy();
			this.remove();
		}
	});

	logic.Rules = Backbone.Collection.extend({
		model: logic.RuleModel
	});

	logic.GroupModel = Backbone.Model.extend({
		initialize: function( args ) {
			var that = this, rules;

			if( args && ( 'rules' in args ) ) {
				rules = args.rules;

				if( ! ( rules instanceof logic.Rules ) ) {
					rules = new logic.Rules( rules );
				}
			} else {
				rules = new logic.Rules();
			}

			this.set( 'rules', rules );

			// When there are no more rules, remove the group
			rules.on( 'remove', function() {
				if( 0 == rules.length ) {
					that.destroy();
				}
			});

			rules.on( 'add remove change', function() {
				that.trigger( 'change' );
			});
		}
	});

	logic.GroupView = Backbone.View.extend({
		className: 'uf-logic-group',

		events: {
			'click .uf-logic-group-add-rule': 'addNewRule',
			'click .uf-logic-group-remove': 'destroy'
		},

		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'conditional-logic-group' );

			this.$el.html( tmpl({

			}));

			// Whenever there are no elements, remove the group too
			this.model.on( 'destroy', function() {
				that.remove();
			});

			// Add all existing rules to the view
			if( this.model.get( 'rules' ).length ) {
				this.model.get( 'rules' ).each(function( rule ) {
					that.addRule( rule );
				});
			} else {
				this.addNewRule();
			}

			// Based on rules, toggle classes
			this.model.get( 'rules' ).on( 'all', function() {
				var method = that.model.get( 'rules' ).length == 1
					? 'addClass'
					: 'removeClass';

				that.$el[ method ]( 'uf-logic-group-single-rule' );
			});
		},

		/**
		 * Add a rule to the viewport.
		 */
		addRule: function( model ) {
			var that = this, view;

			// Display the actual rule
			view = new logic.RuleView({
				model: model
			});

			view.field = this.field;

			this.$el.find( '.uf-logic-group-rules' ).append( view.$el );
			view.render();
		},

		/**
		 * Adds a new rule to the group.
		 */
		addNewRule: function( e ) {
			var that = this, model, view;

			if( e ) e.preventDefault();

			// Create the model for the rule
			model = new logic.RuleModel();

			this.model.get( 'rules' ).add( model );

			this.addRule( model );
		},

		/**
		 * Destroys the model and view.
		 */
		destroy: function() {
			if( this.model ) this.model.destroy();
			this.remove();
		}
	});

	logic.Groups = Backbone.Collection.extend({
		model: logic.GroupModel,

		export: function() {
			var groups = [];

			this.each(function( group ) {
				var rules = [];

				group.get( 'rules' ).each(function( rule ) {
					rules.push({
						selector: rule.get( 'selector' ),
						compare:  rule.get( 'compare' ),
						value:    rule.get( 'value' )
					})
				});

				groups.push({
					rules: rules
				});
			});

			return groups;
		}
	});

	/**
	 * The condiitional logic field allows conditional rules
	 * to be created within the UI.
	 */
	logicField.View = UltimateFields.Field.View.extend({
		className: 'uf-conditional-logic',

		events: {
			'click .uf-logic-add-group': 'addNewGroup'
		},

		render: function() {
			var that    = this,
				context = UltimateFields.UI.Context.get(),
				count   = 0,
				tmpl, value;

			// Check the count of fields in the context
			context[ context.length - 1 ].get( 'fields' ).each(function( field ) {
				if( field.get( 'id' ) === that.model.get( 'field_id' ) ) {
					return;
				}

				count++;
			});

			if( 0 === count ) {
				tmpl = UltimateFields.template( 'conditional-logic-empty' );
				this.$el.html( tmpl() );
				return;
			}

			tmpl = UltimateFields.template( 'conditional-logic' );
			this.$el.html( tmpl() );

			// This will hold all added groups
			value = this.model.getValue();

			if( value instanceof logic.Groups ) {
				this.groups = value;
			} else {
				this.groups = new logic.Groups( value || [] );
			}

			// Add existing groups
			this.groups.each(function( group ) {
				that.addGroup( group );
			});

			// Listen to changes and save them
			this.groups.on( 'change add remove', function() {
				that.changed();
			});

			// Save initial values
			that.changed();

			return this.$el;
		},

		/**
		 * Handles changes in the data.
		 */
		changed: function() {
			this.model.setValue( this.groups.export() );

			// Toggle the message
			this.$el[ this.groups.length > 0 ? 'addClass' : 'removeClass' ]( 'has-groups' );
		},

		/**
		 * Adds a group to the view.
		 */
		addGroup: function( model ) {
			var that = this, view;

			// Display the actual rule
			view = new logic.GroupView({
				model: model
			});

			view.field = this.model;

			this.$el.find( '.uf-logic-groups' ).append( view.$el );

			view.render();
		},

		/**
		 * Adds a new group to the field.
		 */
		addNewGroup: function( e ) {
			var that = this, model;

			e.preventDefault();

			// Create the model for the rule
			model = new logic.GroupModel();
			this.groups.add( model );

			// Add the group to the view
			this.addGroup( model );
		}
	});

})( jQuery );
