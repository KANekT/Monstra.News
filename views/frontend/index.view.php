<h1><?php echo __('News', 'news');?></h1>

<div id="news">

    <?php if(count($records)>0):?>

        <?php
        foreach($records as $item):
        ?>
        <div class="row">
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

            $url_item = $opt["site_url"].News::$path.$item["slug"];
            ?>
            <div class="col-md-12 newsShort">
                <h1><?php echo $item['name'] ?></h1>
                <?php if ($listPic != null) { ?>
                    <a class="pull-left" href="<?php echo $url_item; ?>"><img class="pull-left img-responsive thumb margin10 img-thumbnail" alt="<?php echo $item['name'] ?>" src="<?php echo $opt["url"].$item['parent'].$item["slug"].'/'.'thumbnail/'.$listPic[0] ?>"></a>
                <?php }
                else{ ?>
                    <a class="pull-left" href="<?php echo $url_item; ?>"><img class="pull-left img-responsive thumb margin10 img-thumbnail" alt="<?php echo $item['name'] ?>" src="<?php echo $opt["url"].'no_item.jpg';?>"></a>
                <?php }?>

                <em><i class="glyphicon glyphicon-calendar"></i><?php echo Date::format($item['date'], "d.m.Y H:i:s"); ?></em>
                <article><p>
                        <?php echo News::ContentById($item['id'], true); ?>
                    </p></article>
                <p><a href="<?php echo $url_item; ?>"><?php echo __('Read more', 'news') ?></a></p>
            </div>
        </div><!-- /newsRow -->
        <?php
        endforeach; ?>
    <?php endif;
    echo News::Tags();
    if (Request::get('tag')) {
        $page_url[0] = $opt["site_url"].'/news/page/';
        $page_url[1] = '?tag='.Request::get('tag');
    }
    else{
        $page_url = $opt["site_url"].News::$path.'page/';
    }
    ?>
    <?php echo News::paginator($opt['page'], $opt['pages'], $page_url);?>
</div><!-- /news -->