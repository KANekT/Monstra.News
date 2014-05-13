<?php foreach($records as $item):?>
    <li><a href="<?php echo $opt['site_url'].News::$path.$item['slug'];?>"><?php echo $item['name'];?></a></li>
<?php endforeach;?>