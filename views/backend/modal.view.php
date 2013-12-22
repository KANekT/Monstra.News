<div id="exampleCode" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="exampleCodeLabel" aria-hidden="true" style="width: 600px;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="exampleCodeLabel"><?php echo __('Example Code', 'news');?></h3>
    </div>
    <div class="modal-body">
        <div class="well well-small">
            <?php echo __('PHP Code', 'news');?><br/>
            <dl class="dl-horizontal">

                <dt><?php echo __('Code Last News', 'news');?></dt>
                <dd><code>&lt;?php echo News::Last(3, 'parent');?&gt;</code></dd>

                <dt><?php echo __('Code Top View', 'news');?></dt>
                <dd><code>&lt;?php echo News::TopViews(5, 'parent');?&gt;</code></dd>

                <dt><?php echo __('Code Block', 'news');?></dt>
                <dd><code>&lt;?php echo News::Block(5, 'parent');?&gt;</code></dd>

                <dt><?php echo __('Code Content By Id', 'news');?></dt>
                <dd><code>&lt;?php echo News::ContentById($id, $short=false);?&gt;</code></dd>

                <dt><?php echo __('Code Children', 'news');?></dt>
                <dd><code>&lt;?php echo News::Children($slug);?&gt;</code></dd>

                <dt><?php echo __('Code Tags', 'news');?></dt>
                <dd><code>&lt;?php echo News::Tags($slug = null);?&gt;</code></dd>

                <dt><?php echo __('Code Related', 'news');?></dt>
                <dd><code>&lt;?php echo News::Related();?&gt;</code></dd>

            </dl>
        </div>
        <div class="well well-small">
            <?php echo __('Short Code', 'news');?><br/>

            <dl class="dl-horizontal">
                <dt><?php echo __('Code Last News', 'news');?></dt>
                <dd><code>{news list="last" count=3 parent=""}</code></dd>

                <dt><?php echo __('Code Top View', 'news');?></dt>
                <dd><code>{news list="views" count=3 parent=""}</code></dd>

                <dt><?php echo __('Code Block', 'news');?></dt>
                <dd><code>{news list="block" count=3 parent=""}</code></dd>
            </dl></div>
    </div>
</div>