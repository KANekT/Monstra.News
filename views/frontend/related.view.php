<?php
if (count($related_posts) > 0)
{
?>
    <p>
        <b><?php echo __('Related', 'news'); ?>:</b>
        <div>
            <?php foreach($related_posts as $related_post) { ?>
                <a href="<?php echo Option::get('siteurl').News::$path.$related_post['slug']; ?>"><?php echo $related_post['name']; ?></a><br>
            <?php } ?>
        </div>
    </p>
<?php
}