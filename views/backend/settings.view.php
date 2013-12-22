<h2><?php echo __('Settings news', 'news');?></h2>

<?php
$resize = array(
    'width'   => __('Respect to the width', 'news'),
    'height'  => __('Respect to the height', 'news'),
    'crop'    => __('Similarly, cutting unnecessary', 'news'),
    'stretch' => __('Similarly with the expansion', 'news'),
);
echo (
    Form::open().
        '<div class="row-fluid show-grid">'.
        '<div class="span3">'.
        Form::label('limit', __('News per page (website)', 'news')).
        Form::input('limit', Option::get('news_limit')).
        Form::label('width_thumb', __('Width thumbnails (px)', 'news')).
        Form::input('width_thumb', Option::get('news_w')).
        Form::label('height_thumb', __('Height thumbnails (px)', 'news')).
        Form::input('height_thumb', Option::get('news_h')).
        Form::label('resize', __('Resize', 'news')).
        Form::select('resize', $resize, Option::get('news_resize')).Html::Br().
        Form::submit('news_submit_settings', __('Save', 'news'), array('class' => 'btn')).Html::Nbsp(2).
        Form::submit('news_submit_settings_cancel', __('Cancel', 'news'), array('class' => 'btn')).
        '</div>'.
        '<div class="span3">'.
        Form::label('limit_admin', __('News per page (admin)', 'news')).
        Form::input('limit_admin', Option::get('news_limit_admin')).
        Form::label('width_orig', __('Original width (px, max)', 'news')).
        Form::input('width_orig', Option::get('news_wmax')).
        Form::label('height_orig', __('Original height (px, max)', 'news')).
        Form::input('height_orig', Option::get('news_hmax')).
        Form::hidden('csrf', Security::token()).Html::Br(3).
        '</div>'.
        '</div>'.
        Form::close()
);
?>