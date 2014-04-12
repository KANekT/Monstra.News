<?php if(!isset($opt['display'])) {
    echo News::Breadcrumbs($item, $opt);
} ?>
<div id="news">
    <div class="media">
        <?php
        if ($item['parent'] != "")
        {
            $parent = $item['parent'] . DS;
            $item['parent'] = $item['parent'] . "/";
        }
        else
        {
            $parent = "";
        }
        $listPic = File::scan($opt['dir'] . $parent . $item['slug'] . DS . 'thumbnail');
        if ($listPic != null) : ?>
            <ul class="thumbnails span3">
            <?php foreach($listPic as $pic) : ?><li class="span3">
            <a class="cImg pull-left" rel="<?php echo $item["slug"] ?>" href="<?php echo $opt["url"].$item['parent'].$item["slug"].'/'.$pic ?>"><img class="img-polaroid" alt="<?php echo $item['name'] ?>" src="<?php echo $opt["url"].$item['parent'].$item["slug"].'/'.'thumbnail/'.$pic ?>"></a>
            </li><?php endforeach;?>
            </ul>
        <?php else: ?>
            <a class="cImg pull-left" href="<?php echo $opt["url"].'no_item.jpg';?>"><img class="img-polaroid" alt="<?php echo $item['name'] ?>" src="<?php echo $opt["url"].'no_item.jpg';?>"></a>
        <?php endif;?>
        <div class="media-body breadcrumb">
            <h4 class="media-heading"><?php echo $item['name'] ?></h4>
            <p><?php echo News::ContentById($item['id']); ?></p>
        </div>
    </div>
</div>
<?php if(!isset($opt['display'])) { ?>
    <p><br /><? echo News::Tags($item['slug']);?>
        <? echo News::Children($item['slug']); ?>
        <? echo News::Related(); ?>
    <br />
    <ul class="breadcrumb">
        <li><?php echo Date::format($item['date'], 'd.m.Y'); ?> <span class="divider">/</span></li>
        <li class="active"><?php echo __('Hits count', 'news').$item['hits'] ?></li>
    </ul>
<?php }
?>