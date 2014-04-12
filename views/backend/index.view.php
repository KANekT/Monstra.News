<?php
$token = Security::token();
echo Form::hidden('csrf', $token)
?>
<style>
    input[type="radio"], input[type="checkbox"]
    {margin: 0;}
</style>
<div class="row-fluid">
    <div class="span12">
        <h2><?php echo __('News', 'news'); ?>
            <div class="btn-group">
                <?php echo Html::anchor(__('Create news', 'news'), 'index.php?id=news&action=add_news', array('title' => __('Create new news', 'news'), 'class' => 'btn btn-primary')) ?>
            </div>
            <div class="btn-group">
                <?php echo Html::anchor(__('Settings', 'news'), 'index.php?id=news&action=settings', array('title' => __('Settings', 'news'), 'class' => 'btn btn-default')) ?>
                <?php echo Html::anchor(__('Example Code', 'news'), '#exampleCode', array('role' => 'button', 'data-toggle' => 'modal', 'class' => 'btn btn-default'));?>
            </div>
            <div class="btn-group">
                <span class="btn btn-default"><?php echo __('Sorting', 'news'); ?></span>
                <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li <?php echo ($opt['sort'] == "date") ? 'class="active"' : ''?>><?php echo Html::anchor(__('by date', 'news'), 'index.php?id=news&page='.$opt['page'].'&sort=date&order='.$opt['order']);?></li>
                    <li <?php echo ($opt['sort'] == "id") ? 'class="active"' : ''?>><?php echo Html::anchor(__('by number', 'news'), 'index.php?id=news&page='.$opt['page'].'&sort=id&order='.$opt['order']);?></a></li>
                    <li <?php echo ($opt['sort'] == "views") ? 'class="active"' : ''?>><?php echo Html::anchor(__('by views', 'news'), 'index.php?id=news&page='.$opt['page'].'&sort=views&order='.$opt['order']);?></a></li>
                    <li <?php echo ($opt['sort'] == "status") ? 'class="active"' : ''?>><?php echo Html::anchor(__('by status', 'news'), 'index.php?id=news&page='.$opt['page'].'&sort=status&order='.$opt['order']);?></a></li>
                    <li class="divider"></li>
                    <li <?php echo ($opt['order'] == "ASC") ? 'class="active"' : ''?>><?php echo Html::anchor(__('ASC', 'news'), 'index.php?id=news&page='.$opt['page'].'&order=ASC&sort='.$opt['sort']);?></li>
                    <li <?php echo ($opt['order'] == "DESC") ? 'class="active"' : ''?>><?php echo Html::anchor(__('DESC', 'news'), 'index.php?id=news&page='.$opt['page'].'&order=DESC&sort='.$opt['sort']);?></a></li>
                </ul>
            </div>
        </h2>
        <br />

        <?php
        if (Notification::get('success')) Alert::success(Notification::get('success'));
        if (Notification::get('error')) Alert::success(Notification::get('error'));
        ?>

        <table class="table table-bordered">
            <thead>
            <tr>
                <td width="3%"></td>
                <td><?php echo __('Name', 'news'); ?></td>
                <td><?php echo __('Author', 'news'); ?></td>
                <td><?php echo __('Status', 'news'); ?></td>
                <td><?php echo __('Access', 'news'); ?></td>
                <td><?php echo __('Date', 'news'); ?></td>
                <td width="20%">
                    <div class="btn-group pull-right">
                        <span class="btn btn-small"><input type="checkbox" data-action="checked"></span>
                        <a href="#" data-toggle="dropdown" class="btn dropdown-toggle btn-small"><span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li>
                                <a data-confirm="<?php echo __('Are you sure you want to delete all the news?', 'news'); ?>" data-action="deleteNews" href="#">
                                    <?php echo __('Delete all checked', 'news'); ?></a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            </thead>
            <tbody>
            <?php
            if (count($items) != 0) {
                foreach ($items as $item) {
                    $dash = "";
                    ?>
                    <tr <?php if(trim($item['parent']) !== '') {?> rel="children_<?php echo $item['parent']; ?>" <?php } ?>>
                        <td>
                            <?php
                                if (isset($item['expand'])) {
                                    echo '<a href="javascript:;" class="btn-expand parent" token="'.$token.'" rel="'.$item['slug'].'">-</a>';
                                } else {
                                    $dash = Html::arrow('right').'&nbsp;&nbsp;';
                                }
                            ?>
                        </td>
                        <td>
                            <?php
                            $parent  = (trim($item['parent']) == '') ? '' : $item['parent'].'/';
                            echo $dash.Html::anchor(Html::toText($item['name']), $opt['site_url'].'news/'.$parent.$item['slug'], array('target' => '_blank', 'rel' => 'children_'.$item['parent']));
                            ?>
                        </td>
                        <td>
                            <?php echo $item['author']; ?>
                        </td>
                        <td>
                            <?php echo $item['_status']; ?>
                        </td>
                        <td>
                            <?php echo $item['_access']; ?>
                        </td>
                        <td>
                            <?php echo Date::format($item['date'], "d.m.Y"); ?>
                        </td>
                        <td>
                            <div class="btn-group pull-right">
                                <?php echo Html::anchor(__('Edit', 'news'), 'index.php?id=news&action=edit_news&uid='.$item['id'], array('class' => 'btn btn-small')); ?>
                                <span class="btn btn-small"><input type="checkbox" name="key" value="<?php echo $item['id'] ?>"></span>
                                <a class="btn dropdown-toggle btn-small" data-toggle="dropdown" href="#"><span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <?php if ($item['parent'] == '') { ?>
                                        <li><a href="index.php?id=news&action=add_news&parent=<?php echo $item['slug']; ?>" title="<?php echo __('Create New news', 'news'); ?>"><?php echo __('Create', 'news'); ?></a></li>
                                    <?php } ?>
                                    <li><?php echo Html::anchor(__('Clone', 'news'), 'index.php?id=news&action=clone_news&uid='.$item['id'].'&token='.$token, array('title' => __('Clone', 'news'))); ?></li>
                                    <li><?php echo Html::anchor(__('Delete', 'news'),
                                            'index.php?id=news&action=delete_news&uid='.$item['id'].'&token='.$token,
                                            array('onclick' => "return confirmDelete('".__("Delete news: :news", 'news', array(':news' => Html::toText($item['name'])))."')"));
                                        ?></li>
                                    <li class="divider"></li>
                                    <li>
                                        <a href=""><?php echo __('Status', 'news'); ?></a>
                                    </li>
                                    <?php foreach($opt["status"] as $key => $value):
                                        $active = $item['status'] == $key ? ' class="active"' :  '';
                                        echo '<li'.$active.'><a href="index.php?id=news&action=update_status&uid='.$item['id'].'&status='.$key.'&token='.$token.'">'.$value.'</a></li>';
                                    endforeach; ?>
                                    <li class="divider"></li>
                                    <li>
                                        <a href=""><?php echo __('Access', 'news'); ?></a>
                                    </li>
                                    <?php foreach($opt["access"] as $key => $value):
                                        $active = $item['access'] == $key ? ' class="active"' :  '';
                                        echo '<li'.$active.'><a href="index.php?id=news&action=update_access&uid='.$item['id'].'&access='.$key.'&token='.$token.'">'.$value.'</a></li>';
                                    endforeach; ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php
                }
            }
            ?>
            </tbody>
        </table>
        <?php echo News::paginator($opt['page'], $opt['pages'], 'index.php?id=news&sort='.$opt['sort'].'&order='.$opt['order'].'&page=');?>
    </div>
</div>
<?php echo View::factory('news/views/backend/modal')->render();?>