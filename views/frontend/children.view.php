<?php
if (count($items) > 0) :
?>
<p>
    <b><?php echo __('Children news', 'news'); ?>:</b>
    <div>
        <?php foreach($items as $item) { ?>
            <a href="<?php echo Option::get('siteurl').News::$path.$item['parent'].'/'.$item['slug']; ?>"><?php echo $item['name']; ?></a><br>
        <?php } ?>
    </div>
</p>
<?php
endif;