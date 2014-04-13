<?php if(!isset($opt['display'])) {
    echo News::Breadcrumbs($item, $opt);
}
?>
<div id="news">
    <h1><?php echo $item['name'] ?></h1>
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
            <ul class="thumbnails col-md-3">
            <?php foreach($listPic as $pic) : ?><li class="col-md-3">
            <a class="cImg pull-left" rel="<?php echo $item["slug"] ?>" href="<?php echo $opt["url"].$item['parent'].$item["slug"].'/'.$pic ?>"><img class="pull-left img-responsive thumb margin10 img-thumbnail" alt="<?php echo $item['name'] ?>" src="<?php echo $opt["url"].$item['parent'].$item["slug"].'/'.'thumbnail/'.$pic ?>"></a>
            </li><?php endforeach;?>
            </ul>
        <?php else: ?>
            <a class="cImg pull-left" href="<?php echo $opt["url"].'no_item.jpg';?>"><img class="pull-left img-responsive thumb margin10 img-thumbnail" alt="<?php echo $item['name'] ?>" src="<?php echo $opt["url"].'no_item.jpg';?>"></a>
        <?php endif;?>
    <em><i class="glyphicon glyphicon-calendar"></i><?php echo Date::format($item['date'], "d.m.Y H:i:s"); ?></em>
    <article><p>
            <?php echo News::ContentById($item['id'], true); ?>
    </p></article>
</div>
<div class="clearfix"></div><div>
<?php if(!isset($opt['display'])) :?>
    <? echo News::Tags($item['slug']);?>
    <? echo News::Children($item['slug']); ?>
    <? echo News::Related(); ?>
    <br />
        <ul class="breadcrumb">
            <li><?php echo Date::format($item['date'], 'd.m.Y'); ?></li>
            <li class="active"><?php echo __('Hits count', 'news').$item['hits'] ?></li>
        </ul>
    </div>
<?php endif;
?>
</div>