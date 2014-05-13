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
    Form::hidden('csrf', Security::token())
);
?>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <?php
            echo (
                Form::label('limit', __('News per page (website)', 'news')).
                Form::input('limit', Option::get('news_limit'), array('class' => 'form-control'))
            );
            ?>
        </div>
        <div class="form-group">
            <?php
            echo (
                Form::label('width_thumb', __('Width thumbnails (px)', 'news')).
                Form::input('width_thumb', Option::get('news_w'), array('class' => 'form-control'))
            );
            ?>
        </div>
        <div class="form-group">
            <?php
            echo (
                Form::label('height_thumb', __('Height thumbnails (px)', 'news')).
                Form::input('height_thumb', Option::get('news_h'), array('class' => 'form-control'))
            );
            ?>
        </div>
        <div class="form-group">
            <?php
            echo (
                Form::label('resize', __('Resize', 'news')).
                Form::select('resize', $resize, Option::get('news_resize'), array('class' => 'form-control'))
            );
            ?>
        </div>
        <div class="form-group">
            <?php
            echo (
                Form::submit('news_submit_settings', __('Save', 'news'), array('class' => 'btn btn-primary')).Html::Nbsp(2).
                Form::submit('news_submit_settings_cancel', __('Cancel', 'news'), array('class' => 'btn btn-default'))
            );
            ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <?php
            echo (
                Form::label('limit_admin', __('News per page (admin)', 'news')).
                Form::input('limit_admin', Option::get('news_limit_admin'), array('class' => 'form-control'))
            );
            ?>
        </div>
        <div class="form-group">
            <?php
            echo (
                Form::label('width_orig', __('Original width (px, max)', 'news')).
                Form::input('width_orig', Option::get('news_wmax'), array('class' => 'form-control'))
            );
            ?>
        </div>
        <div class="form-group">
            <?php
            echo (
                Form::label('height_orig', __('Original height (px, max)', 'news')).
                Form::input('height_orig', Option::get('news_hmax'), array('class' => 'form-control'))
            );
            ?>
        </div>
        <div class="form-group">
            <?php
            echo (
                Form::label('is_main', __('News is Main Plugin', 'news')).Html::br().
                Form::checkbox('is_main', '1', Option::get('news_is_main') > 0 ? true : false )
            );
            ?>
        </div>
    </div>
</div>

<?
echo Form::close();
?>