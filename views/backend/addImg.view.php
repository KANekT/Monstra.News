<li>
    <div class="fileupload fileupload-new" data-provides="fileupload">
        <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
        <div>
            <span class="btn btn-file">
                <span class="fileupload-new"><?php echo __('Select image', 'news'); ?></span>
                <span class="fileupload-exists"><?php echo __('Change', 'news'); ?></span>
                <?php echo Form::file('news_img'.date("His"))?>
            </span>
            <a href="#" class="btn fileupload-exists" data-dismiss="fileupload"><?php echo __('Remove', 'news'); ?></a>
        </div>
    </div>
</li>