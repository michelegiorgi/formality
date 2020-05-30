import { el } from './helpers'
const { __ } = wp.i18n

export default {
  init() {
    $(el("nav_hints"))
      .attr("data-more", __("Show more hints", "formality"))
      .attr("data-less", __("Show less hints", "formality"))
      .on("mousedown touchstart", function(e){
        e.preventDefault()
        $(this).toggleClass(el("nav_hints", false, "--less"))
      })
  },
  clear() {
    $(el("nav_hints")).html("")
  },
  show($field) {
    const type = $field.attr("data-type")
    const label = $field.find(el("label", true, ":first")).text()
    const hints = this.getHints(type, label)
    $(el("nav_hints")).html(hints)
  },
  getHints(type, label) {
    const inputTypes = {
      'text': [0, 2],
      'textarea': [1, 2],
      'email': [0, 2],
      'number': [5, 0, 2],
      'select': [3, 6],
      'multiple': [4, 7],
      'switch': [7, 0],
      'rating': [4, 0],
    }
    const hints = [
      { //0
        "text": __("Press enter or tab to proceed to next field", "formality"),
        "icons": [ "keyboard_return", "keyboard_tab" ],
      },{ //1
        "text": __("Press tab to proceed to next field", "formality"),
        "icons": [ "keyboard_tab" ],
      },{ //2
        "text": __('Press backspace to reset this field and return to previous field', "formality"),
        "icons": [ "keyboard_backspace" ],
      },{ //3
        "text": __("Press up or down arrows to choose your option", "formality"),
        "icons": [ "keyboard_arrow_up", "keyboard_arrow_down" ],
      },{ //4
        "text": __("Press left or right arrows to choose your option", "formality"),
        "icons": [ "keyboard_arrow_left", "keyboard_arrow_right" ],
      },{ //5
        "text": __("Press up and down arrows to increment or decrement your value", "formality"),
        "icons": [ "keyboard_arrow_up", "keyboard_arrow_down" ],
      },{ //6
        "text": __("Press enter to confirm your option and proceed to next field", "formality"),
        "icons": [ "keyboard_return" ],
      },{ //7
        "text": __("Press space to confirm your option", "formality"),
        "icons": [ "space_bar" ],
      },
    ]
    const fieldArray = inputTypes[type]
    let htmlHints = '<li><h6>' + label + '</h6></li>'
    fieldArray.forEach(function(e) {
      let icons = ""
      hints[e].icons.forEach(function(e) {
        icons += '<i>' + e + '</i>'
      })
      htmlHints += '<li><span>' + hints[e].text + '</span>' + icons + '</li>'
    });
    return htmlHints
  },
}