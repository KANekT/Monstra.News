<ol class="breadcrumb">
    <li><a href="<?php echo $opt["site_url"].News::$path;?>"><?php echo __('News', 'news');?></a></li>
    <?php if ($item['parent'] != ""):
        echo '<li><a href="'.$opt["site_url"].News::$path.$item['parent'].'">'.$item['parent_name'].'</a></li>';
    endif; ?>
    <li class="active"><?php echo $item['name'] ?></li>
</ol>