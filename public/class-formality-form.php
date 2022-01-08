<?php

/**
 * Form rendering functions
 *
 * @link       https://formality.dev
 * @since      1.0
 * @package    Formality
 * @subpackage Formality/public
 * @author     Michele Giorgi <hi@giorgi.io>
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
    $fields = '<div class="fo__main">' . $fields . '</section></div>';
    return $fields;
  }

  public function actions() {
    $buttons = '<div class="fo__actions">';
    $buttons .= '<button type="button" class="fo__btn fo__btn--prev">'. __('Previous', 'formality') . '</button>';
    $buttons .= '<button type="button" class="fo__btn fo__btn--next">'. __('Next', 'formality') . '</button>';
    $buttons .= '<button type="submit" class="fo__btn fo__btn--submit">'. ( $this->option("send_text") ? sanitize_text_field($this->option("send_text")) : __('Send', 'formality')) . '</button>';
    $buttons .= '</div>';
    return $buttons;
  }

  public function nav() {
    $nav = '<nav class="fo__nav"><ul class="fo__nav__list"></ul><ul class="fo__nav__hints fo__nav__hints--less"></ul></nav>';
    return apply_filters('formality_form_nav', $nav, $this->form_id);
  }

  public function result() {
    $thankyou_default = __("Thank you", "formality");
    $thankyou_message_default = __("Your data has been successfully submitted. You are very important to us, all information received will always remain confidential. We will contact you as soon as possible.", "formality");
    $error_default = __("Error", "formality");
    $error_message_default = __("Something went wrong and we couldn't save your data. Please retry later or contact us by e-mail or phone.", "formality");
    $result = '<div class="fo__result">';
    $result .= '<div class="fo__result__success"><h3>' . ( $this->option("thankyou") ? $this->option("thankyou") : $thankyou_default ) . '</h3><p>' . ( $this->option("thankyou_message") ? $this->option("thankyou_message") : $thankyou_message_default ) . '</p></div>';
    $result .= '<div class="fo__result__error"><h3>' . ( $this->option("error")  ? $this->option("error") : $error_default ) . '</h3><p>' . ( $this->option("error_message")  ? $this->option("error_message") : $error_message_default ) . '</p></div>';
    $result .= '</div>';
    return apply_filters('formality_form_result', $result, $this->form_id);
  }

  public function header($hide_title=false) {
    $logo_id = $this->option("logo_id");
    $logo = $logo_id ? wp_get_attachment_image($logo_id, "full") : '';
    $header = '<header class="fo__header">';
    $header .= '<div class="fo__logo">' . $logo . '</div>';
    if(!$hide_title) { $header .= '<h2 class="fo__title">' . get_the_title() . '</h2>'; }
    $header .= '</header>';
    return apply_filters('formality_form_header', $header, $this->form_id);
  }

  public function body() {
    $body = '<div class="fo__body">' . $this->fields() . $this->nav() . '</div>';
    return apply_filters('formality_form_body', $body, $this->form_id);
  }

  public function footer() {
    $footer = '<footer class="fo__footer">' . $this->actions() . $this->result() . $this->credits() . '</footer>';
    return apply_filters('formality_form_footer', $footer, $this->form_id);
  }

  public function credits() {
    $credits = nl2br($this->option("custom_credits"));
    if($this->option("enable_credits")) {
      if($credits) { $credits .= '<br>'; };
      $credits .= str_replace("Formality", '<a rel="noopener noreferrer" target="_blank" href="https://formality.dev"><svg width="45px" height="30px" viewBox="0 0 45 30" version="1.1"><path d="M26.2434082,6 L36.7565918,6 C41.3093004,6 45,9.69069957 45,14.2434082 C45,18.7961168 41.3093004,22.4868164 36.7565918,22.4868164 L26.2434082,22.4868164 C21.6906996,22.4868164 18,18.7961168 18,14.2434082 C18,9.69069957 21.6906996,6 26.2434082,6 Z M26.2212891,19.3434082 C29.0379413,19.3434082 31.3212891,17.0600604 31.3212891,14.2434082 C31.3212891,11.426756 29.0379413,9.1434082 26.2212891,9.1434082 C23.4046368,9.1434082 21.1212891,11.426756 21.1212891,14.2434082 C21.1212891,17.0600604 23.4046368,19.3434082 26.2212891,19.3434082 Z"></path><polygon points="0 22.078125 0 0 15.65625 0 15.65625 3.640625 3.96875 3.640625 3.96875 9.21875 14.28125 9.21875 14.28125 12.859375 3.96875 12.859375 3.96875 22.078125"></polygon></svg></a>', __('Made with Formality', 'formality'));
      $credit_bg = sprintf(__("Photo by %s on Unsplash", "formality"), '<a rel="noopener noreferrer" target="_blank" href="' . $this->option("credits_url") .'">' . $this->option("credits") . '</a>');
      $credits .= $this->option("credits") ? ( '&nbsp; — &nbsp;' . str_replace("Unsplash", '<a rel="noopener noreferrer" href="https://unsplash.com" target="_blank">Unsplash</a>', $credit_bg) ) : '';
    }
    $credits = $credits ? '<div class="fo__credits">' . $credits . '</div>' : '';
    return apply_filters('formality_form_credits', $credits, $this->form_id);
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
    $style .= '--formality_radius: ' . $this->option("border_radius") . 'px;';
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
      $style .= $embed ? '' : '<div class="fo__bg"></div>';
    } else {
      $style .= '}</style>';
    }
    return apply_filters('formality_form_style', $style, $this->form_id);
  }

  public function sidebar($cta_label="", $invert_colors=false, $align="left", $hidden=false) {
    $sidebar = '<div class="fo__cta-wrap fo__cta-wrap--align-' . $align . ( $hidden ? ' fo__cta-wrap--hidden' : '' ) . '"><a id="formality-' . $this->form_id . '" href="'. get_permalink($this->form_id) .'" class="formality-' . $this->form_id . ' fo__cta' . ($invert_colors ? " fo__cta--invert" : "") . '">' . $cta_label . '</a></div>';
    return apply_filters('formality_form_sidebar', $sidebar, $this->form_id);
  }

  public function print($embed=false, $attributes = array() ) {
    $include_bg = isset($attributes['include_bg']) ? $attributes['include_bg'] : false;
    $hide_title = isset($attributes['hide_title']) ? $attributes['hide_title'] : false;
    if(isset($attributes['sidebar'])&&$attributes['sidebar']) {
      $form = $this->sidebar($attributes['cta_label'], $attributes['invert_colors'], $attributes['align'], $attributes['disable_button']) . $this->style($embed, false);
    } else {
      $form = '<form id="formality-' . $this->form_id . '" data-id="' . $this->form_id . '" data-uid="' . uniqid() . '" class="fo fo--first-loading fo--' . $this->option("type") . ( $include_bg ? " fo--includebg" : "" ) . ' fo--' . $this->option("style") . ' fo--layout-' . $this->option("bg_layout", "standard") . '" autocomplete="off" novalidate><div class="fo__wrap">' . $this->header($hide_title) . $this->body() . $this->footer() . '</div></form>' . $this->style($embed);
    }
    return $form;
  }

}
