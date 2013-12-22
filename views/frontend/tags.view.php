<?php foreach($tags as $tag) { ?>
    <a href="<?php echo Option::get('siteurl'); ?>news?tag=<?php echo $tag; ?>"><span class="label label-important" data-original-title=""><?php echo $tag; ?></span></a>
<?php } ?>