
import {SortableContainer, SortableElement} from 'react-sortable-hoc';
import cloneDeep from 'clone-deep';

let el = wp.element.createElement;
let c = wp.components;

Array.prototype.move = function (from, to) {
  //console.log(from, to);
  this.splice(to, 0, this.splice(from, 1)[0]);
};

const countNonEmpty = function (object) {
    let c = 0;
    for (let key in object)
        if (object.hasOwnProperty(key) && ((typeof object[key] === 'string' && object[key].length) || typeof object[key] === 'number' || typeof object[key] === 'boolean'))
            c++;

    return c;
};

const repeaterData = (value, returnArray = false, removeEmpty = true) => {
    if (typeof value === 'string' && !returnArray)
        return value; //If it hasn't been rendered yet it's still a string
    else if (typeof value === 'string')
        return JSON.parse(value);

    value = cloneDeep(value);

    value = value.filterMap((v) => {
        delete v._key;
        if (!removeEmpty || countNonEmpty(v) !== 0) {
            return v;
        }
    });
    return returnArray ? value : JSON.stringify(value);
};

const SortableItem = SortableElement(({value, parentValue, index, onChangeChild, template, removeText, onRemove, addOnNonEmpty}) => {
    return el('div', {className: 'repeater-row-wrapper'}, [
        el('div', {className: 'repeater-row-inner'}, template(value, (v) => {
            onChangeChild(v, index)
        })),
        el('div', {className: 'button-wrapper'},
          addOnNonEmpty && index === parentValue.length - 1 ? null : [ 
            el('div', {className: 'repeater-row-move'}, "" ),
            el(c.Button, {
              className: 'repeater-row-remove is-button is-default',
              onClick: () => { onRemove(index) }
            }, removeText ? removeText : '-')
          ]
        )
    ])
});
const SortableList = SortableContainer(({items, id, template, onChangeChild, removeText, onRemove, addOnNonEmpty}) => {
    return el('div', {className: 'repeater-rows'}, items.map((value, index) => {
            return el(SortableItem, {
                key: id + '-repeater-item-' + value._key,
                index,
                value,
                parentValue: items,
                onChangeChild,
                template,
                removeText,
                onRemove,
                addOnNonEmpty
            })
        }
    ));
});
c.RepeaterControl = wp.compose.withInstanceId(function (_ref) {
    let value = [{}],
        max = _ref.max,
        addOnNonEmpty = !_ref.addText,
        removeOnEmpty = !!_ref.removeOnEmpty,
        instanceId = _ref.instanceId,
        id = "inspector-repeater-control-".concat(instanceId);
    if (typeof _ref.value === 'string') {
        try {
            const parsed = JSON.parse(_ref.value);
            value = Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            value = [];
        }
    } else {
        value = cloneDeep(_ref.value); //Clone value else we would mutate the state directly
    }

    const onRemove = (i) => {
        if (value.length > 0) {
            value.splice(i, 1);
            if (value.length === 0) {
                onAdd();
            } else {
                onChangeValue(value);
            }
        }
    };
    let key = 0; //This is the key of each element, it must be unique
    value.map((v) => {
        if (typeof v._key === 'undefined')
            v._key = key++;
        else {
            key = v._key;
        }
    });

    const onAdd = () => {
        if (!max || value.length < max) {
            value.push({_key: ++key});
            onChangeValue(value);
        }
    };
    const onChangeValue = (v) => {
        return _ref.onChange(v);
    };
    const onChangeChild = (v, i) => {
        value[i] = v;
        if (i === value.length - 1) {
            if (addOnNonEmpty && countNonEmpty(v) > 1) {
                onAdd()
            } else if (removeOnEmpty && countNonEmpty(v) <= 1) {
                onRemove(i)
            } else {
                onChangeValue(value);
            }
        } else if (value.length > 1 && removeOnEmpty && countNonEmpty(v) <= 1) {
            onRemove(i)
        } else {
            onChangeValue(value);
        }
    };

    if (value.length === 0) {
        onAdd();
    } else {
        const last = value[value.length - 1];
        if (addOnNonEmpty && countNonEmpty(last) > 1) {
            onAdd()
        }
    }

    const onSortEnd = ({oldIndex, newIndex}) => {
        value.move(oldIndex, newIndex);
        onChangeValue(value);
    };

    return el(c.BaseControl, {
            label: _ref.label,
            id: id,
            help: _ref.help,
            className: _ref.className
        }, [
            el(SortableList, {
                key: id + '-sortable-list',
                id: id,
                items: value,
                lockAxis: 'y',
                helperContainer: function () {
                    //This is an awaiting PR in react-sortable-hoc, until implemented, jQuery has to do the job :(
                    return jQuery(".edit-post-sidebar").get(0)
                },
                template: _ref.children,
                removeText: _ref.removeText,
                addOnNonEmpty,
                onRemove,
                onChangeChild,
                onSortEnd
            }),
            !addOnNonEmpty && (!max || value.length < max) ? el(c.Button, {
                    className: 'repeater-row-add is-button is-default is-large',
                    onClick: onAdd
                },
                _ref.addText ? _ref.addText : '+') : null
        ]
    );
});