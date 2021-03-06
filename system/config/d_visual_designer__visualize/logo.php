<?php
//отображение блока в окне при выборе блока
$_['display']         = true;
//Порядковый номер
$_['sort_order']      = 2;
//Категория(content, social, structure)
$_['category'] = 'header';
//отображать название блока
$_['display_title']   = true;
//Может содержать дочерние блоки
$_['child_blocks']    = false;
//Уровень доступный для добавления блока
$_['level_min']        = 3;
$_['level_max']        = 7;
//Расположение кнопок управления
$_['control_position'] ='popup';
//Отображение кнопок управления
$_['display_control'] = true;
//Кнопка перетаскивания
$_['button_drag']     = true;
//Кнопка layout
$_['button_layout']     = false;
//Кнопка редатирования
$_['button_edit']     = true;
//Кнопка копирования
$_['button_copy']     = true ;
//Кнопка сворачивания
$_['button_collapse'] = true;
//Кнопка удаления
$_['button_remove']   = true;
//Доступен пре-рендер
$_['pre_render'] = true;
//Доступно сохранение в html
$_['save_html'] = false;
//Типы полей
$_['types']            = array(
    'name' => 'string',
    'logo' => 'string',
    'home' => 'string',
    'image' => 'string',
    'title' => 'string',
    'image_alt' => 'string',
    'image_title' => 'string',
    'link' => 'string',
    'parallax' => 'boolean',
    'parallax_height' => 'string',
    'width' => 'string',
    'height' => 'string',
    'style' => 'string',
    'align' => 'string',
    'animate' => 'string',
    'size' => 'string',
    'onclick' => 'string',
    'size_phone' => 'number',
    'size_tablet' => 'number',
    'align_phone' => 'string',
    'align_tablet' => 'string'
);
//Настройки по умолчанию
$_['setting'] = array(
    'name' => '',
    'logo' => '',
    'home' => '',
    'title' => '',
    'image_alt' => '',
    'image_title' => '',
    'link' => '',
    'parallax' => '0',
    'parallax_height' => '400px',
    'width' => '200px',
    'height' => '100px',
    'style' => '',
    'align' => 'center',
    'animate' => '',
    'size' => 'semi-responsive',
    'onclick' => 'popup',
    'size_phone' => '',
    'size_tablet' => '',
    'align_phone' => '',
    'align_tablet' => ''
);