<?php

/**
 * Form rendering functions
 *
 * @link       https://formality.dev
 * @since      1.0.0
 *
 * @package    Formality
 * @subpackage Formality/public
 */

class Formality_Form {

  private $formality;
  private $version;

  public function __construct( $formality, $version ) {
    $this->formality = $formality;
    $this->version = $version;
    $this->form_id = get_the_ID();
    $this->form_meta = get_post_meta($this->form_id);
  }

  public function option($single, $default = "") {
    return $single ? ( isset($this->form_meta["_formality_" . $single]) && $this->form_meta["_formality_" . $single][0] ? $this->form_meta["_formality_" . $single][0] : $default ) : $this->form_meta;
  }
  
  public function fields() {
    $render = new Formality_Fields($this->formality, $this->version);
    $fields = "";
    $index = 0;
    if(has_blocks()) {
      $blocks = parse_blocks(get_the_content());
      foreach ( $blocks as $block ) {
        if($block['blockName']) {
          $index++;
          $type = str_replace("formality/","",$block['blockName']);
          $field = $render->field($type, $block["attrs"], $this->option("type"), $index);
          $fields .= $field;
        }
      }
    }
    $fields = '<div class="formality__main">' . $fields . '</section></div>';
    return $fields;
  }
  
  public function actions() {
    $buttons = '<div class="formality__actions">';
    $buttons .= '<button type="button" class="formality__btn formality__btn--prev">'. __('Previous', 'formality') . '</button>';
    $buttons .= '<button type="button" class="formality__btn formality__btn--next">'. __('Next', 'formality') . '</button>';
    $buttons .= '<button type="submit" class="formality__btn formality__btn--submit">'. ( $this->option("send_text") ? sanitize_text_field($this->option("send_text")) : __('Send', 'formality')) . '</button>';
    $buttons .= '</div>';
    return $buttons;
  }

  public function nav() {
    $nav = '<nav class="formality__nav"><ul class="formality__nav__list"></ul><ul class="formality__nav__hints formality__nav__hints--less"></ul></nav>';
    return $nav;
  }
  
  public function result() {
    $thankyou_default = __("Thank you", "formality");
    $thankyou_message_default = __("Your data has been successfully submitted. You are very important to us, all information received will always remain confidential. We will contact you as soon as possible.", "formality");
    $error_default = __("Error", "formality");
    $error_message_default = __("Something went wrong and we couldn't save your data. Please retry later or contact us by e-mail or phone.", "formality");
    $result = '<div class="formality__result">';
    $result .= '<div class="formality__result__success"><h3>' . ( $this->option("thankyou") ? $this->option("thankyou") : $thankyou_default ) . '</h3><p>' . ( $this->option("thankyou_message") ? $this->option("thankyou_message") : $thankyou_message_default ) . '</p></div>';
    $result .= '<div class="formality__result__error"><h3>' . ( $this->option("error")  ? $this->option("error") : $error_default ) . '</h3><p>' . ( $this->option("error_message")  ? $this->option("error_message") : $error_message_default ) . '</p></div>';
    $result .= '</div>';
    return $result;
  }

  public function header($hide_title=false) {
    $logo = $this->option("logo_id");
    //$logo = $logo ? wp_get_attachment_image($logo, "full") : file_get_contents(plugin_dir_url(__DIR__) . "assets/images/logo.svg");
    $logo = $logo ? wp_get_attachment_image($logo, "full") : '';
    $header = '<header class="formality__header">';
    $header .= '<div class="formality__logo">' . $logo . '</div>';
    if(!$hide_title) { $header .= '<h2 class="formality__title">' . get_the_title() . '</h2>'; }
    $header .= '</header>';
    return $header;
  }
  
  public function body() {
    $body = '<div class="formality__body">' . $this->fields() . $this->nav() . '</div>';
    return $body;
  }
    
  public function footer() {
    $footer = '<footer class="formality__footer">' . $this->actions() . $this->result() . $this->credits() . '</footer>';
    return $footer;
  }

  public function credits() {
    $credits = nl2br($this->option("custom_credits"));
    if($this->option("enable_credits")) {
      if($credits) { $credits .= '<br>'; };      
      $credits .= str_replace("Formality", '<a rel="noopener noreferrer" target="_blank" href="https://formality.dev"><svg width="45px" height="30px" viewBox="0 0 45 30" version="1.1"><path d="M26.2434082,6 L36.7565918,6 C41.3093004,6 45,9.69069957 45,14.2434082 C45,18.7961168 41.3093004,22.4868164 36.7565918,22.4868164 L26.2434082,22.4868164 C21.6906996,22.4868164 18,18.7961168 18,14.2434082 C18,9.69069957 21.6906996,6 26.2434082,6 Z M26.2212891,19.3434082 C29.0379413,19.3434082 31.3212891,17.0600604 31.3212891,14.2434082 C31.3212891,11.426756 29.0379413,9.1434082 26.2212891,9.1434082 C23.4046368,9.1434082 21.1212891,11.426756 21.1212891,14.2434082 C21.1212891,17.0600604 23.4046368,19.3434082 26.2212891,19.3434082 Z"></path><polygon points="0 22.078125 0 0 15.65625 0 15.65625 3.640625 3.96875 3.640625 3.96875 9.21875 14.28125 9.21875 14.28125 12.859375 3.96875 12.859375 3.96875 22.078125"></polygon></svg></a>', __('Made with Formality', 'formality'));
      $credit_bg = sprintf(__("Photo by %s on Unsplash", "formality"), '<a rel="noopener noreferrer" target="_blank" href="' . $this->option("credits_url") .'">' . $this->option("credits") . '</a>');
      $credits .= $this->option("credits") ? ( '&nbsp; — &nbsp;' . str_replace("Unsplash", '<a rel="noopener noreferrer" href="https://unsplash.com" target="_blank">Unsplash</a>', $credit_bg) ) : '';
    }
    $credits = $credits ? '<div class="formality__credits">' . $credits . '</div>' : '';
    return $credits;
  }

  public function style($embed=false, $id=true) {
    $style = '<style>' . ( $embed ? ( $id ? '#' : '.' ) . 'formality-' . $this->form_id : ':root' ) . ' {';
    $style .= '--formality_col1: ' . $this->option("color1") . ';';
    $style .= '--formality_col2: ' . $this->option("color2") . ';';
    $style .= '--formality_col3: ' . $this->option("color3") . ';';
    $style .= '--formality_bg: ' . $this->option("color2") . ';';
    $style .= '--formality_fontsize: ' . $this->option("fontsize") . 'px;';
    $style .= '--formality_border: ' . ($this->option("fontsize") < 18 ? 1 : 2) . 'px;';
    $style .= '--formality_logo_height: ' . $this->option("logo_height") . 'em;';
    $bg = $this->option("bg_id");
    if($bg && (!$this->option("template"))) {
      $bg = wp_get_attachment_image_src($bg, "full");
      $bg_url = $bg[0];
    } else {
      $bg_url = $this->option("bg");
    }
    if($bg_url) {
      $opacity = sprintf('%02d',$this->option("overlay_opacity"));
      $style .= '--formality_bg_url: url(' . $bg_url . ');';
      $style .= '--formality_bg_opacity: ' . ( $opacity == '100' ? '1' : '0.' . $opacity ) . ';';
      $style .= '--formality_bg_position: ' . $this->option("position") . ';';
      $style .= '}</style>';
      $style .= $embed ? '' : '<div class="formality__bg"></div>';
    } else {
      $style .= '}</style>';
    }
    return $style;
  }

  public function sidebar($cta_label="", $invert_colors=false, $align="left", $hidden=false) {
    $sidebar = '<div class="formality__cta-wrap formality__cta-wrap--align-' . $align . ( $hidden ? ' formality__cta-wrap--hidden' : '' ) . '"><a id="formality-' . $this->form_id . '" href="'. get_permalink($this->form_id) .'" class="formality-' . $this->form_id . ' formality__cta' . ($invert_colors ? " formality__cta--invert" : "") . '">' . $cta_label . '</a></div>';
    return $sidebar;
  }
  
  public function print($embed=false, $attributes = array() ) {
    $include_bg = isset($attributes['include_bg']) ? $attributes['include_bg'] : false;
    $hide_title = isset($attributes['hide_title']) ? $attributes['hide_title'] : false;
    if(isset($attributes['sidebar'])&&$attributes['sidebar']) {
      $form = $this->sidebar($attributes['cta_label'], $attributes['invert_colors'], $attributes['align'], $attributes['disable_button']) . $this->style($embed, false);
    } else {
      $form = '<form id="formality-' . $this->form_id . '" data-id="' . $this->form_id . '" data-uid="' . uniqid() . '" class="formality formality--first-loading formality--' . $this->option("type") . ( $include_bg ? " formality--includebg" : "" ) . ' formality--' . $this->option("style") . ' formality--layout-' . $this->option("bg_layout", "standard") . '" autocomplete="off" novalidate><div class="formality__wrap">' . $this->header($hide_title) . $this->body() . $this->footer() . '</div></form>' . $this->style($embed);
    }
    return $form;
  } 

}
