<h2><?php echo __('New news', 'news'); ?></h2>
<br />

<?php
echo (
    Form::open(null, array('class' => 'form_validate','enctype' => 'multipart/form-data')).
    Form::hidden('csrf', Security::token())
);
?>

<ul class="nav nav-tabs">
    <li <?php if (Notification::get('news')) { ?>class="active"<?php } ?>><a href="#news" data-toggle="tab"><?php echo __('Item', 'news'); ?></a></li>
    <li <?php if (Notification::get('metadata')) { ?>class="active"<?php } ?>><a href="#metadata" data-toggle="tab"><?php echo __('Metadata', 'news'); ?></a></li>
    <li <?php if (Notification::get('settings')) { ?>class="active"<?php } ?>><a href="#settings" data-toggle="tab"><?php echo __('Settings', 'news'); ?></a></li>
    <li <?php if (Notification::get('img')) { ?>class="active"<?php } ?>><a href="#img" data-toggle="tab"><?php echo __('Image', 'news'); ?></a></li>
    <li><a href="<?php echo Url::base(); ?>/index.php?id=news"><?php echo __('Return to Index', 'news'); ?></a></li>
</ul>

<div class="tab-content tab-page">
    <div class="tab-pane <?php if (Notification::get('news')) { ?>active<?php } ?>" id="news">
        <div class="form-group">
            <?php
            echo (
                Form::label('news_name', __('Name', 'news')).
                Form::input('news_name', $item['name'], array('class' => 'required form-control'))
            );
            ?>
        </div>
        <div class="form-group">
            <?php
            echo (
                Form::label('news_slug', __('Name (slug)', 'news')).
                Form::input('news_slug', $item['slug'], array('class' => 'form-control'))
            );
            ?>
        </div>
    </div>
    <div class="tab-pane <?php if (Notification::get('metadata')) { ?>active<?php } ?>" id="metadata">
        <div class="form-group">
            <?php
            echo (
                Form::label('news_keywords', __('Keywords', 'news')).
                Form::input('news_keywords', $item['keywords'], array('class' => 'form-control'))
            );
            ?>
        </div>
        <div class="form-group">
            <?php
            echo (
                Form::label('news_title', __('Title', 'news')).
                Form::input('news_title', $item['title'], array('class' => 'form-control'))
            );
            ?>
        </div>
        <div class="form-group">
            <?php
            echo (
                Form::label('news_description', __('Description', 'news')).
                Form::textarea('news_description', $item['description'], array('class' => 'form-control'))
            );
            ?>
        </div>
        <div class="form-group">
            <?php
            echo (
                Form::label('news_robots', __('Search Engines Robots', 'news')).
                Html::br(1).
                'no Index'.Html::nbsp().Form::checkbox('news_robots_index', 'index', $item['robots_index']).Html::nbsp(2).
                'no Follow'.Html::nbsp().Form::checkbox('news_robots_follow', 'follow', $item['robots_follow'])
            );
            ?>
        </div>
    </div>
    <div class="tab-pane <?php if (Notification::get('settings')) { ?>active<?php } ?>" id="settings">
        <div class="row">
            <div class="col-md-3">
                <?php
                echo (
                    Form::label('news_parent', __('Parent', 'news')).
                    Form::select('news_parent', $opt['list'], $item['parent'], array('class' => 'form-control'))
                );
                ?>
            </div>
            <div class="col-md-3">
                <?php
                echo (
                    Form::label('news_template', __('Template', 'news')).
                    Form::select('news_template', $opt['templates'], $item['template'], array('class' => 'form-control'))
                );
                ?>
            </div>
            <div class="col-md-3">
                <?php
                echo (
                    Form::label('news_status', __('Status', 'news')).
                    Form::select('news_status', $opt['status'], $item['status'], array('class' => 'form-control'))
                );
                ?>
            </div>
            <div class="col-md-3">
                <?php
                echo (
                    Form::label('news_access', __('Access', 'news')).
                    Form::select('news_access', $opt['access'], $item['access'], array('class' => 'form-control'))
                );
                ?>
            </div>
        </div>
    </div>
    <div class="tab-pane <?php if (Notification::get('img')) { ?>active<?php } ?>" id="img">
        <div><ul class="list-inline img-upload">
                <li>
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                        <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
                        <div>
                                <span class="btn btn-file">
                                    <span class="fileupload-new"><?php echo __('Select image', 'news'); ?></span>
                                    <span class="fileupload-exists"><?php echo __('Change', 'news'); ?></span>
                                    <?php echo Form::file('news_file')?>
                                </span>
                            <a href="#" class="btn fileupload-exists" data-dismiss="fileupload"><?php echo __('Remove', 'news'); ?></a>
                        </div>
                    </div>
                </li>
            </ul></div>
        <div class="row">
            <div class="col-md-3">
                <span class="btn btn-warning" data-action="aai"><?php echo __('Add another image', 'news'); ?></span>
            </div>
        </div>
    </div>
</div>
<div class="tab-page">
    <?php echo (
        Form::label('news_short', __('News Short', 'news')).
        Form::textarea('news_short', Html::toText($item['short']), array('class' => 'required form-control', 'style' => 'width: 100%; height: 100px;'))
    )
    ?>
</div>
<div class="row">
    <div class="col-md-12">
        <?php Action::run('admin_editor', array(Html::toText($item['content']))); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <div class="input-group">
                <?php
                echo (
                Form::input('news_tags', $item['tags'], array('class' => 'form-control'))
                );
                ?>
                <span class="input-group-addon add-on">
                            <?php echo __('Tags', 'news'); ?>
                        </span>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <?php
        echo (
            Form::submit('add_news_and_exit', __('Save and exit', 'news'), array('class' => 'btn btn-primary')).Html::nbsp(2).
            Form::submit('add_news', __('Save', 'news'), array('class' => 'btn btn-primary')).Html::nbsp(2).
            Html::anchor(__('Cancel', 'news'), 'index.php?id=news', array('title' => __('Cancel', 'news'), 'class' => 'btn btn-default'))
        );
        ?>
    </div>
    <div class="col-md-6">
        <div class="pull-right">
            <div class="input-group datapicker">
                <?php //echo __('Author', 'news'); ?><?php echo Form::input('news_author', $opt['author'], array('class' => 'form-control')); ?>
                <?php echo Form::input('news_date', $item['date'], array('class' => 'form-control')); ?>
                <span class="input-group-addon add-on">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
        <?php echo Form::close(); ?>
    </div>
</div>