<li>
    <div class="fileinput fileinput-new" data-provides="fileinput">
        <div class="fileinput-preview thumbnail" style="width: 200px; height: 150px;"></div>
        <div>
            <span class="btn btn-file">
                <span class="fileinput-new"><?php echo __('Select image', 'news'); ?></span>
                <span class="fileinput-exists"><?php echo __('Change', 'news'); ?></span>
                <?php echo Form::file('news_img'.date("His"))?>
            </span>
            <a href="#" class="btn fileinput-exists" data-dismiss="fileinput"><?php echo __('Remove', 'news'); ?></a>
        </div>
    </div>
</li>
