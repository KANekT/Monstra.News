<h1><?php echo __('News', 'news');?></h1>

<div id="news">

    <?php if(count($records)>0):?>

    <ul class="thumbnails">
        <?php
        foreach($records as $item):
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

            $url_item = $opt["site_url"].'news/'.$item["slug"];
            ?>
            <li class="span12">
                <div class="thumbnail media">
                    <?php if ($listPic != null) { ?>
                        <a class="pull-left" href="<?php echo $url_item; ?>"><img class="img-polaroid" alt="<?php echo $item['name'] ?>" src="<?php echo $opt["url"].$item['parent'].$item["slug"].'/'.'thumbnail/'.$listPic[0] ?>"></a>
                    <?php }
                    else{ ?>
                        <a class="pull-left" href="<?php echo $url_item; ?>"><img class="img-polaroid" alt="<?php echo $item['name'] ?>" src="<?php echo $opt["url"].'no_item.jpg';?>"></a>
                    <?php }?>
                    <div class="media-body breadcrumb">
                        <h4 class="media-heading"><?php echo $item['name'] ?></h4>
                        <p><?php echo News::ContentById($item['id'], true); ?></p>
                        <p><i class="icon-calendar"></i><?php echo Date::format($item['date'], "d.m.Y H:i:s"); ?> <a href="<?php echo $url_item; ?>"><?php echo __('Read more', 'news') ?></a></p>
                    </div>
                </div>
            </li>
        <?php
        endforeach; ?>
    </ul>
    <?php endif;
    echo News::Tags();
    if (Request::get('tag')) {
        $page_url[0] = $opt["site_url"].'news/page/';
        $page_url[1] = '?tag='.Request::get('tag');
    }
    else{
        $page_url = $opt["site_url"].'news/page/';
    }
    ?>
    <?php echo News::paginator($opt['page'], $opt['pages'], $page_url);?>
</div><!-- /news -->