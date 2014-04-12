<?php foreach($records as $item): ?>
    <li><b><?php echo Date::format($item['date'], 'd.m.Y');?></b>
		<p><?php echo News::ContentById($item['id']); ?>
			<a href="<?php echo $opt['site_url'];?>news/<?php echo $item['slug'];?>"><?php echo __('Read more', 'news') ?></a>
		</p>
	</li>
<?php endforeach;?>