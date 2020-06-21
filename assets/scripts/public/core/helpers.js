export let uid = ($element, form = true) => {
  /* eslint-disable no-unused-vars */
  if($element) {
    if($element.is("form")) {
      window.formality.uid = $element.attr("data-uid")
    } else {
      window.formality.uid = $element.closest("form.formality").attr("data-uid")
    }
  }
  if(form) {
    return 'form.formality[data-uid="' + window.formality.uid + '"]'
  } else {
    return window.formality.uid
  }
  /* eslint-enable no-unused-vars */
}
export let el = (name, parent = true, child = "") => {
  const el = {
    form: "formality",
    section: "formality__section",
    section_header: "formality__section__header",
    field: "formality__field",
    label: "formality__label",
    field_focus: "formality__field--focus",
    field_filled: "formality__field--filled",
    field_required: "formality__field--required",
    field_error: "formality__field--error",
    field_success: "formality__field--success",
    field_disabled: "formality__field--disabled",
    input: "formality__input",
    input_errors: "formality__input__errors",
    message: "formality__message",
    media: "formality__media",
    nav: "formality__nav",
    nav_list: "formality__nav__list",
    nav_section: "formality__nav__section",
    nav_anchor: "formality__nav__anchor",
    nav_legend: "formality__nav__legend",
    nav_hints: "formality__nav__hints",
    actions: "formality__actions",
    button: "formality__btn",
    submit: "formality__btn--submit",
    result: "formality__result",
    result_success: "formality__result__success",
    result_error: "formality__result__error",
    cta: "formality__cta",
    sidebar: "formality__sidebar",
  }
  if(parent==true) {
    return "." + el[name] + child
  } else if(parent==false) {
    return el[name] + child
  } else if(parent=="uid") {
    if(name=="form") {
      return uid() + child
    } else {
      return uid() + " ." + el[name] + child
    }
  } else {
    return parent + " ." + el[name] + child
  }
}
export let isIn = (elem, centerH = true) => {
  if (elem instanceof jQuery){ elem = elem[0] }
  const distance = elem.getBoundingClientRect()
  let height = window.innerHeight || document.documentElement.clientHeight
  height = centerH ? height * .75 : height
  return (
    distance.top >= 0 &&
    distance.left >= 0 &&
    distance.bottom <= height &&
    distance.right <= (window.innerWidth || document.documentElement.clientWidth)
  )
}
export let focusFirst = (delay = 10) => {
  const $tofocus = $(el("section", true, ":first-child :input")).filter(function(){ return !this.value; }).first()
  if($tofocus.length && isIn($tofocus)) {
    setTimeout(function(){
      $tofocus.focus()
    }, delay)
  }
}
export let isMobile = () => {
  let hasTouchScreen = false
  if ("maxTouchPoints" in navigator) { 
    hasTouchScreen = navigator.maxTouchPoints > 0
  } else if ("msMaxTouchPoints" in navigator) {
    hasTouchScreen = navigator.msMaxTouchPoints > 0
  } else {
    let mQ = window.matchMedia && matchMedia("(pointer:coarse)")
    if (mQ && mQ.media === "(pointer:coarse)") {
      hasTouchScreen = !!mQ.matches
    } else if ('orientation' in window) {
      hasTouchScreen = true // deprecated, but good fallback
    } else {
      let UA = navigator.userAgent;
      hasTouchScreen = (
        /\b(BlackBerry|webOS|iPhone|IEMobile)\b/i.test(UA) ||
        /\b(Android|Windows Phone|iPad|iPod)\b/i.test(UA)
      )
    }
  }
  return hasTouchScreen
}