<?php

add_action('wp_enqueue_scripts', 'xpsocial_enqueue_styles_forms');

function xpsocial_enqueue_styles_forms()
{
    $xp_input_height = get_option('xp_input_height') ? get_option('xp_input_height') : 50;
    $xp_input_border = get_option('xp_input_border') ? get_option('xp_input_border') : '0.5px solid #000';
    $xp_input_border_radius = get_option('xp_input_border_radius') ? get_option('xp_input_border_radius') : 3;
    $xp_bg_color = get_option('xp_bg_color') ? get_option('xp_bg_color') : '#fff';
    $xp_font_color = get_option('xp_font_color') ? get_option('xp_font_color') : '#000';
    $xp_div_color = get_option('xp_div_color') ? get_option('xp_div_color') : '#fff';
    $xp_divisor_color = get_option('xp_divisor_color') ? get_option('xp_divisor_color') : '#000';
    $xp_btn_height = get_option('xp_btn_height') ? get_option('xp_btn_height') : 50;
    $xp_btn_width = get_option('xp_btn_width') ? get_option('xp_btn_width') : 100;
    $xp_btn_color = get_option('xp_btn_color') ? get_option('xp_btn_color') : '#fff';
    $xp_btn_bgcolor = get_option('xp_btn_bgcolor') ? get_option('xp_btn_bgcolor') : '#000';
    $xp_btn_border_width = get_option('xp_btn_border_width') ? get_option('xp_btn_border_width') : 0;
    $xp_btn_border_color = get_option('xp_btn_border_color') ? get_option('xp_btn_border_color') : 0;
    $xp_btn_border_radius = get_option('xp_btn_border_radius') ? get_option('xp_btn_border_radius') : $xp_input_border_radius;
    $xp_select_height = $xp_input_height + 15;

    $custom_css = "
    .xpsocial-form {
        background-color: {$xp_div_color};
        padding: 12px;
    }

  
    .xpsocial-form input:not([type=checkbox]){
        height: {$xp_input_height}px;
        border: {$xp_input_border};
        border-radius: {$xp_input_border_radius}px;
        background-color: {$xp_bg_color};
    }
    .xpsocial-form input:not([type=checkbox]),
    #xpsocial-form input[type=date],
    #xpsocial-form input[type=email],
    #xpsocial-form input[type=number],
    #xpsocial-form input[type=password],
    #xpsocial-form input[type=search],
    #xpsocial-form input[type=tel],
    #xpsocial-form input[type=text],
    #xpsocial-form input[type=url],
    #xpsocial-form textarea,
    .register_form input[type=date],
    .register_form input[type=email],
    .register_form input[type=number],
    .register_form input[type=password],
    .register_form input[type=search],
    .register_form input[type=tel],
    .register_form input[type=text],
    .register_form input[type=url],
    .register_form textarea {
        width: 100%;
        height: {$xp_input_height}px;
        padding: 5px;
        box-sizing: border-box;
        border: {$xp_input_border};
    }
   .register_form select{
        height: {$xp_input_height}px;
        width: 100%;
        padding: 5px;
        border: {$xp_input_border} !important;
    }
    * Estilo para las opciones del select */
    .register_form select option {
        padding: 10px;
        background-color: #fff;
        color: {$xp_font_color};
    }

    .select-container span {
        display: flex;
    }
    .select-container span :first-child  {
        flex-grow: 2;
        width: unset;
        padding-right:20px;
    }.select-container span :last-child  {
        flex-grow: 3;
    }
    /* Estilo para el contenedor del select */
    .register_form .select-container {
        position: relative;
        display: flex;
        flex-direction: column;
        flex-wrap: nowrap;
        justify-content: flex-end;
    }

    /* Estilo para el ícono de la flecha del select (opcional) */
    .register_form .select-container select::after {
        content: '▼';
        font-size: 0.8em;
        position: absolute;
        top: 70%;
        right: 10px;
        transform: translateY(-50%);
        pointer-events: none;
    }
    .xpsocial-form label {
        color: {$xp_font_color};
    }
   .divisor {
        color: {$xp_divisor_color};
    }
    .divisor:after, .divisor:before {
        background-color: {$xp_divisor_color};
    }
    .xpsocial-form input#wp-submit, .xp_button {
        min-height: {$xp_btn_height}px;
        width: {$xp_btn_width}%;
        color: {$xp_btn_color};
        background-color: {$xp_btn_bgcolor};
        border: {$xp_btn_border_width}px solid {$xp_btn_border_color};
        border-radius: {$xp_btn_border_radius}px;
        cursor:pointer;
    }";

    wp_add_inline_style('wp-block-library', $custom_css);
}
