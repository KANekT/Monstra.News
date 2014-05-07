<?php
Navigation::add(__('News', 'news'), 'content', 'news', 10);

Action::add('admin_themes_extra_index_template_actions','NewsAdmin::formComponent');
Action::add('admin_themes_extra_actions','NewsAdmin::formComponentSave');

// Add action on admin_pre_render hook
Action::add('admin_pre_render','NewsAdmin::_requestAjax');

class NewsAdmin extends Backend {

    /**
     * News tables
     *
     * @var object
     */
    public static $news = null;

    /**
     * News admin function
     */
    public static function main() {
        $templates_path = THEMES_SITE;
        $opt['site_url'] = Option::get('siteurl');

        // Get all templates
        $templates_list = File::scan($templates_path, '.template.php');
        foreach ($templates_list as $file) {
            $opt['templates'][basename($file, '.template.php')] = basename($file, '.template.php');
        }
        $errors = array();

        $news = new Table('news');
        NewsAdmin::$news = $news;

        $users = new Table('users');
        $user = $users->select('[id='.Session::get('user_id').']', null);

        $user['firstname'] = Html::toText($user['firstname']);
        $user['lastname']  = Html::toText($user['lastname']);

        // Page author
        if ( ! empty($user['firstname'])) {
            $opt['author'] = (empty($user['lastname'])) ? $user['firstname'] : $user['firstname'].' '.$user['lastname'];
        } else {
            $opt['author'] = Session::get('user_login');
        }

        // Status array
        $opt['status'] = array('published' => __('Published', 'news'), 'draft' => __('Draft', 'news'));

        // Access array
        $opt['access'] = array('public'   => __('Public', 'news'), 'registered'  => __('Registered', 'news'));

        $opt['url'] = $opt['site_url'] . '/public/uploads/news/';
        $opt['dir'] = ROOT . DS . 'public' . DS . 'uploads' . DS . 'news' . DS;


        // Check for get actions
        // ---------------------------------------------
        if (Request::get('action')) {

            // Switch actions
            // -----------------------------------------
            switch (Request::get('action')) {

                // Settings
                // -------------------------------------
                case "settings":

                    if (Request::post('news_submit_settings_cancel')) {
                        Request::redirect('index.php?id=news');
                    }

                    if (Request::post('news_submit_settings')) {
                        if (Security::check(Request::post('csrf'))) {
                            Option::update(array(
                                'news_limit'  => (int)Request::post('limit'),
                                'news_limit_admin' => (int)Request::post('limit_admin'),
                                'news_w' => (int)Request::post('width_thumb'),
                                'news_h' => (int)Request::post('height_thumb'),
                                'news_wmax'   => (int)Request::post('width_orig'),
                                'news_hmax'   => (int)Request::post('height_orig'),
                                'news_resize' => (string)Request::post('resize')
                            ));

                            Notification::set('success', __('Your changes have been saved', 'news'));

                            Action::run('admin_news_settings');

                            Request::redirect('index.php?id=news&action=settings');
                        } else { die('csrf detected!'); }
                    }

                    View::factory('news/views/backend/settings')->display();

                    break;

                // Clone news
                // -------------------------------------
                case "clone_news":

                    if (Security::check(Request::get('token'))) {

                        // Generate rand news name
                        $rand_news_name = Request::get('uid').'_clone_'.date("Ymd_His");

                        // Get original news
                        $orig_news = $news->select('[id="'.Request::get('uid').'"]', null);

                        // Generate rand news title
                        $news_name = $orig_news['name'].' [copy]';
                        $safeName = Security::safeName($rand_news_name, '-', true);

                        // Clone news
                        if ($news->insert(array(
                            'slug'         => $safeName,
                            'name'         => $news_name,
                            'parent'       => $orig_news['parent'],
                            'robots_index' => $orig_news['robots_index'],
                            'robots_follow'=> $orig_news['robots_follow'],
                            'status'       => $orig_news['status'],
                            'template'     => $orig_news['template'],
                            'access'       => (isset($orig_news['access'])) ? $orig_news['access'] : 'public',
                            'title'        => $orig_news['title'],
                            'description'  => $orig_news['description'],
                            'keywords'     => $orig_news['keywords'],
                            'tags'         => $orig_news['tags'],
                            'date'         => time(),
                            'author'       => $orig_news['author']
                        ))) {

                            // Get cloned news ID
                            $last_id = $news->lastId();

                            // Save cloned news content
                            File::setContent(STORAGE . DS . 'news' . DS . $last_id . '.news.txt',
                            File::getContent(STORAGE . DS . 'news' . DS . $orig_news['id'] . '.news.txt'));

                            // Save cloned news content
                            File::setContent(STORAGE . DS . 'news' . DS . $last_id . '.short.news.txt',
                            File::getContent(STORAGE . DS . 'news' . DS . $orig_news['id'] . '.short.news.txt'));

                            Dir::create($opt['dir']. $orig_news['parent'] . DS . $safeName . DS);
                            Dir::create($opt['dir']. $orig_news['parent'] . DS . $safeName . DS . 'thumbnail' . DS);
                            // Send notification
                            Notification::set('success', __('The news <i>:news</i> cloned.', 'news', array(':news' => Security::safeName(Request::get('slug'), '-', true))));
                        }

                        // Run add extra actions
                        Action::run('admin_news_action_clone');

                        // Redirect
                        Request::redirect('index.php?id=news');

                    } else { die('csrf detected!'); }

                    break;

                // Add news
                // -------------------------------------
                case "add_news":

                    // Add news
                    if (Request::post('add_news') || Request::post('add_news_and_exit')) {

                        if (Security::check(Request::post('csrf'))) {

                            // Get parent news
                            if (Request::post('news_parent') == '0') {
                                $parent = '';
                            } else {
                                $parent = Request::post('news_parent');
                            }

                            // Prepare date
                            if (Valid::date(Request::post('news_date'))) {
                                $date = strtotime(Request::post('news_date'));
                            } else {
                                $date = time();
                            }

                            if (Request::post('news_robots_index'))  $robots_index = 'noindex';   else $robots_index = 'index';
                            if (Request::post('news_robots_follow')) $robots_follow = 'nofollow'; else $robots_follow = 'follow';
                            $slug = (Request::post('news_slug') == "") ? Request::post('news_name') : Request::post('news_slug');

                            $slugs = $news->select("[slug='".$slug."']");

                            if ($slugs !== null && count($slugs) > 0) {
                                $errors[] = __('Slug is exits', 'news');
                                Notification::set('error', __('Slug is exits', 'news'));
                            }

                            // If no errors then try to save
                            if (count($errors) == 0) {

                                $last_id =  0;
                                $safeName = Security::safeName($slug, '-', true);
                                // Insert new news
                                if ($news->insert(array(
                                        'slug'         => $safeName,
                                        'name'         => Request::post('news_name'),
                                        'parent'       => $parent,
                                        'status'       => Request::post('news_status'),
                                        'template'     => Request::post('news_template'),
                                        'access'       => Request::post('news_access'),
                                        'robots_index' => $robots_index,
                                        'robots_follow'=> $robots_follow,
                                        'title'        => Request::post('news_title'),
                                        'description'  => Request::post('news_description'),
                                        'tags'         => Request::post('news_tags'),
                                        'keywords'     => Request::post('news_keywords'),
                                        'date'         => $date,
                                        'author'       => Request::post('news_author'),
                                        'hits'         => '0')
                                )) {
                                    // Get inserted news ID
                                    $last_id = $news->lastId();

                                    // Save content
                                    File::setContent(STORAGE . DS . 'news' . DS . $last_id . '.news.txt', XML::safe(Request::post('editor')));
                                    File::setContent(STORAGE . DS . 'news' . DS . $last_id . '.short.news.txt', XML::safe(Request::post('news_short')));
                                    if ($parent != "")
                                        $parent = $parent . DS;
                                    Dir::create($opt['dir']. $parent . $safeName . DS);
                                    Dir::create($opt['dir']. $parent . $safeName . DS . 'thumbnail' . DS);
                                    $imgName = Security::safeName(Text::translitIt(trim(Request::post('news_name')), '-', true));
                                    NewsAdmin::UploadImage($imgName, $opt['dir']. $parent . $safeName . DS, $_FILES);

                                    // Send notification
                                    Notification::set('success', __('Your news <i>:news</i> have been added.', 'news', array(':news' => Security::safeName(Request::post('news_title'), '-', true))));
                                }

                                // Run add extra actions
                                Action::run('admin_news_action_add');

                                // Redirect
                                if (Request::post('add_news_and_exit')) {
                                    Request::redirect('index.php?id=news');
                                } else {
                                    Request::redirect('index.php?id=news&action=edit_news&uid='.$last_id);
                                }
                            }

                        } else { die('csrf detected!'); }

                    }

                    // Get all news
                    $news_list = $news->select('[parent=""]');
                    $opt['list'][""] = '-none-';
                    if (is_array($news_list))
                    {
                        foreach ($news_list as $item) {
                            $opt['list'][$item['slug']] = $item['name'];
                        }
                    }

                    // Save fields
                    if (Request::post('news_slug'))             $news_item['slug']          = Request::post('news_slug');         else $news_item['slug'] = '';
                    if (Request::post('news_name'))             $news_item['name']          = Request::post('news_name');         else $news_item['name'] = '';
                    if (Request::post('news_title'))            $news_item['title']         = Request::post('news_title');        else $news_item['title'] = '';
                    if (Request::post('news_keywords'))         $news_item['keywords']      = Request::post('news_keywords');     else $news_item['keywords'] = '';
                    if (Request::post('news_tags'))             $news_item['tags']          = Request::post('news_tags');         else $news_item['tags'] = '';
                    if (Request::post('news_description'))      $news_item['description']   = Request::post('news_description');  else $news_item['description'] = '';
                    if (Request::post('editor'))                $news_item['content']       = Request::post('editor');            else $news_item['content'] = '';
                    if (Request::post('news_templates'))        $news_item['template']      = Request::post('news_templates');    else $news_item['template'] = Option::get('news_template');
                    if (Request::post('news_short'))            $news_item['short']         = Request::post('news_short');        else $news_item['short'] = '';
                    if (Request::post('news_status'))           $news_item['status']        = Request::post('news_status');       else $news_item['status'] = 'published';
                    if (Request::post('news_access'))           $news_item['access']        = Request::post('news_access');       else $news_item['access'] = 'public';
                    if (Request::post('news_parent'))           $news_item['parent']        = Request::post('news_parent');       else if(Request::get('parent')) $news_item['parent'] = Request::get('parent'); else $news_item['parent'] = '';
                    if (Request::post('news_robots_index'))     $news_item['robots_index']  = true;                               else $news_item['robots_index'] = false;
                    if (Request::post('news_robots_follow'))    $news_item['robots_follow'] = true;                               else $news_item['robots_follow'] = false;
                    //--------------

                    // Generate date
                    $news_item['date'] = Date::format(time(), 'Y-m-d H:i:s');

                    // Set Tabs State - news
                    Notification::setNow('news', 'news');

                    // Display view
                    View::factory('news/views/backend/add')
                        ->assign('item', $news_item)
                        ->assign('opt', $opt)
                        ->assign('errors', $errors)
                        ->display();

                    break;

                // Edit news
                // -------------------------------------
                case "edit_news":

                    if (Request::post('edit_news') || Request::post('edit_news_and_exit')) {

                        if (Security::check(Request::post('csrf'))) {

                            // Get news parent
                            if (Request::post('news_parent') == '0') {
                                $parent = '';
                            } else {
                                $parent = Request::post('news_parent');
                            }

                            $id = (int)Request::post('news_id');

                            // Prepare date
                            if (Valid::date(Request::post('news_date'))) {
                                $date = strtotime(Request::post('news_date'));
                            } else {
                                $date = time();
                            }

                            if (Request::post('news_robots_index'))  $robots_index = 'noindex';   else $robots_index = 'index';
                            if (Request::post('news_robots_follow')) $robots_follow = 'nofollow'; else $robots_follow = 'follow';
                            $slug = (Request::post('news_slug') == "") ? Request::post('news_title') : Request::post('news_slug');

                            if ($slug != Request::post('old_slug'))
                            {
                                $slugs = $news->select("[slug='".$slug."']");

                                if ($slugs !== null && count($slugs) > 0) {
                                    $errors[] = __('Slug is exits', 'news');
                                    Notification::set('error', __('Slug is exits', 'news'));
                                }
                            }

                            if (count($errors) == 0) {
                                $safeName = Security::safeName($slug, '-', true);
                                $safeOldName = Security::safeName(Request::post('old_slug'), '-', true);
                                $parent_old = Request::post('old_parent');

                                $data = array(
                                    'slug'         => $safeName,
                                    'parent'       => $parent,
                                    'name'         => Request::post('news_name'),
                                    'title'        => Request::post('news_title'),
                                    'description'  => Request::post('news_description'),
                                    'tags'         => Request::post('news_tags'),
                                    'keywords'     => Request::post('news_keywords'),
                                    'robots_index' => $robots_index,
                                    'robots_follow'=> $robots_follow,
                                    'status'       => Request::post('news_status'),
                                    'template'     => Request::post('news_template'),
                                    'access'       => Request::post('news_access'),
                                    'date'         => $date,
                                    'author'       => Request::post('news_author')
                                );

                                if ($parent != "")
                                    $parent = $parent . DS;
                                if ($parent_old != "")
                                    $parent_old = $parent_old . DS;

                                $no_rename = true;
                                if ($parent != $parent_old)
                                {
                                    rename($opt['dir']. $parent_old . $safeOldName, $opt['dir']. $parent . $safeName);
                                    $no_rename = false;
                                }

                                // Update parents in all childrens
                                if ($safeName !== $safeOldName) {

                                    if (Request::post('old_parent') == '')
                                    {
                                        $_news = $news->select('[parent="'.$safeOldName.'"]');
                                        if ( ! empty($_news)) {
                                            foreach($_news as $news_item) {
                                                $news->updateWhere('[slug="'.$news_item['slug'].'"]', array('parent' => $safeName));
                                            }
                                        }
                                    }

                                    if ($no_rename)
                                        rename($opt['dir'] . $parent_old . $safeOldName, $opt['dir'] . $parent . $safeName);
                                }

                                if ($news->updateWhere('[id="'.$id.'"]', $data)) {
                                    $imgName = Security::safeName(Text::translitIt(trim(Request::post('news_name')), '-', true));
                                    NewsAdmin::UploadImage($imgName, $opt['dir']. $parent . $safeName . DS, $_FILES);
                                    File::setContent(STORAGE . DS . 'news' . DS . $id . '.news.txt', XML::safe(Request::post('editor')));
                                    File::setContent(STORAGE . DS . 'news' . DS . $id . '.short.news.txt', XML::safe(Request::post('news_short')));
                                    Notification::set('success', __('Your changes to the news <i>:news</i> have been saved.', 'news', array(':news' => Security::safeName(Request::post('news_title'), '-', true))));
                                }

                                // Run edit extra actions
                                Action::run('admin_news_action_edit');

                                // Redirect
                                if (Request::post('edit_news_and_exit')) {
                                    Request::redirect('index.php?id=news');
                                } else {
                                    Request::redirect('index.php?id=news&action=edit_news&uid='.$id);
                                }
                            }

                        } else { die('csrf detected!'); }
                    }

                    $item = $news->select('[id="'.Request::get('uid').'"]', null);

                    // Get all news
                    $news_list = $news->select();
                    $opt['list'][""] = '-none-';
                    // Foreach news find news whithout parent
                    foreach ($news_list as $item_list) {
                        if (isset($item_list['parent'])) {
                            $c_p = $item_list['parent'];
                        } else {
                            $c_p = '';
                        }
                        if ($c_p == '') {
                            if ($item_list['slug'] !== $item['slug']) {
                                $opt['list'][$item_list['slug']] = $item_list['name'];
                            }
                        }
                    }

                    if ($item) {

                        $item['content'] = Text::toHtml(File::getContent(STORAGE . DS . 'news' . DS . $item['id'] . '.news.txt'));
                        $item['short'] = Text::toHtml(File::getContent(STORAGE . DS . 'news' . DS . $item['id'] . '.short.news.txt'));
                        $item['robots_index'] = ($item['robots_index'] == 'noindex') ? true : false;
                        $item['robots_follow'] = ($item['robots_follow'] == 'nofollow') ? true : false;

                        if (Request::post('parent')) {
                            // Get news parent
                            if (Request::post('parent') == '-none-') {
                                $item['parent'] = '';
                            } else {
                                $item['parent'] = Request::post('parent');
                            }
                        }

                        // date
                        $item['date'] = Date::format($item['date'], 'Y-m-d H:i:s');
                        // Set Tabs State - news
                        Notification::setNow('news', 'news');
                        // Display view
                        View::factory('news/views/backend/edit')
                            ->assign('item', $item)
                            ->assign('opt', $opt)
                            ->assign('errors', $errors)
                            ->display();
                    }

                    break;

                // Delete news
                // -------------------------------------
                case "delete_news":

                    if (Security::check(Request::get('token'))) {

                        NewsAdmin::deleteNews(Request::get('uid'));

                        // Run delete extra actions
                        Action::run('admin_news_action_delete');

                        // Redirect
                        Request::redirect('index.php?id=news');

                    } else { die('csrf detected!'); }

                    break;

                // Update page access
                // -------------------------------------
                case "update_access":

                        if (Security::check(Request::get('token'))) {

                            $news->updateWhere('[id="'.Request::get('uid').'"]', array('access' => Request::get('access')));

                            // Run delete extra actions
                            Action::run('admin_news_action_update_access');

                            // Send notification
                            Notification::set('success', __('Your changes to the news <i>:page</i> have been saved.', 'pages', array(':page' => Request::get('slug'))));

                            // Redirect
                            Request::redirect('index.php?id=news');

                        } else { die('csrf detected!'); }

                    break;

                // Update page status
                // -------------------------------------
                case "update_status":

                        if (Security::check(Request::get('token'))) {

                            $news->updateWhere('[id="'.Request::get('uid').'"]', array('status' => Request::get('status')));

                            // Run delete extra actions
                            Action::run('admin_news_action_update_status');

                            // Send notification
                            Notification::set('success', __('Your changes to the news <i>:page</i> have been saved.', 'pages', array(':page' => Request::get('slug'))));

                            // Redirect
                            Request::redirect('index.php?id=news');

                        } else { die('csrf detected!'); }

                    break;
            }

            // Its mean that you can add your own actions for this plugin
            Action::run('admin_news_extra_actions');

        } else {

            // Index action
            // -------------------------------------

            // Init vars
            $count = 0;
            $items = array();
            $limit = Option::get('news_limit_admin');
            $records_all = $news->select('[parent=""]', array('slug', 'title', 'status', 'date', 'author', 'expand', 'access', 'parent'));
            $count_news = count($records_all);
            $opt['pages'] = ceil($count_news/$limit);

            $opt['page'] = (Request::get('page')) ? (int)Request::get('page') : 1;
            $opt['sort'] = (Request::get('sort')) ? (string)Request::get('sort') : 'date';
            $opt['order'] = (Request::get('order') and Request::get('order')=='ASC') ? 'ASC' : 'DESC';

            if ($opt['page'] < 1) { $opt['page'] = 1; }
            elseif ($opt['page'] > $opt['pages']) { $opt['page'] = $opt['pages']; }

            $start = ($opt['page']-1)*$limit;

            $records_sort = Arr::subvalSort($records_all, $opt['sort'], $opt['order']);
            if($count_news>0) $records = array_slice($records_sort, $start, $limit);
            else $records = array();

            // Loop
            foreach ($records as $item) {

                $items[$count]['name']    = $item['name'];
                $items[$count]['parent']  = $item['parent'];
                $items[$count]['_status'] = $opt['status'][$item['status']];
                $items[$count]['_access'] = $opt['access'][$item['access']];
                $items[$count]['status']  = $item['status'];
                $items[$count]['access']  = $item['access'];
                $items[$count]['date']    = $item['date'];
                $items[$count]['author']  = $item['author'];
                $items[$count]['expand']  = 0;
                $items[$count]['slug']    = $item['slug'];
                $items[$count]['id']      = $item['id'];

                $_news = Arr::subvalSort($news->select('[parent="'.$item['slug'].'"]', 'all'), 'date', 'DESC');

                if ( ! empty($_news)) {
                    foreach($_news as $news_item) {
                        $items = NewsAdmin::childrenIndex($news_item, $items, $opt, $count);
                        $count++;
                    }
                }
                $count++;
            }

            // Display view
            View::factory('news/views/backend/index')
                ->assign('items', $items)
                ->assign('opt', $opt)
                ->display();
        }

    }

    /**
     * Add child to parent
     */
    public static function childrenIndex($item, $items, $opt, $count)
    {
        $count++;
        $items[$count]['name']    = $item['name'];
        $items[$count]['parent']  = $item['parent'];
        $items[$count]['_status'] = $opt['status'][$item['status']];
        $items[$count]['_access'] = $opt['access'][$item['access']];
        $items[$count]['status']  = $item['status'];
        $items[$count]['access']  = $item['access'];
        $items[$count]['date']    = $item['date'];
        $items[$count]['author']  = $item['author'];
        $items[$count]['slug']    = $item['slug'];
        $items[$count]['id']      = $item['id'];

        return $items;
    }

    /**
     * Form Component Save
     */
    public static function formComponentSave() {
        if (Request::post('news_component_save')) {
            if (Security::check(Request::post('csrf'))) {
                Option::update('news_template', Request::post('news_form_template'));
                Request::redirect('index.php?id=themes');
            }
        }
    }


    /**
     * Form Component
     */
    public static function formComponent() {

        $_templates = Themes::getTemplates();
        foreach($_templates as $template) {
            $templates[basename($template, '.template.php')] = basename($template, '.template.php');
        }

        echo (
            Form::open().
                Form::hidden('csrf', Security::token()).
                Form::label('news_form_template', __('News template', 'news')).
                Form::select('news_form_template', $templates, Option::get('news_template')).
                Html::br().
                Form::submit('news_component_save', __('Save', 'news'), array('class' => 'btn')).
                Form::close()
        );
    }

    private static function UploadImage($uid, $folder, $_files)
    {
        foreach($_files as $item)
        {
            if ($item) {
                if($item['type'] == 'image/jpeg' ||
                    $item['type'] == 'image/png' ||
                    $item['type'] == 'image/gif') {

                    switch($item['type'])
                    {
                        case "image/jpeg" :
                            $ext = ".jpg";
                            break;
                        case "image/png" :
                            $ext = ".png";
                            break;
                        case "image/gif" :
                            $ext = ".gif";
                            break;
                        default:
                            $ext = ".jpg";
                            break;
                    }

                    $img  = Image::factory($item['tmp_name']);
                    $file['wmax']   = (int)Option::get('news_wmax');
                    $file['hmax']   = (int)Option::get('news_hmax');
                    $file['w']      = (int)Option::get('news_w');
                    $file['h']      = (int)Option::get('news_h');
                    $file['resize'] = Option::get('news_resize');

                    NewsAdmin::ReSize($img, $folder, $uid.'_'.time().$ext, $file);
               }
            }
        }

    }

    private static function deleteNews($uid)
    {
        $news = NewsAdmin::$news;
        // Get specific news
        $item = $news->select('[id="'.$uid.'"]', null);

        $opt['dir'] = ROOT . DS . 'public' . DS . 'uploads' . DS . 'news' . DS;
        //  Delete news and update <parent> fields
        if ($news->deleteWhere('[slug="'.$item['slug'].'" ]')) {

            $_news = $news->select('[parent="'.$item['slug'].'"]');

            if ( ! empty($_news)) {
                foreach($_news as $news_item) {
                    $news->updateWhere('[slug="'.$news_item['slug'].'"]', array('parent' => ''));
                    rename($opt['dir'] . $item['slug'] . DS . $news_item['slug'], $opt['dir']. $news_item['slug']);
                }
            }

            if ($item['parent'] != "")
                $item['parent'] = $item['parent'] . DS;

            Dir::delete($opt['dir'] . $item['parent'] . $item['slug']);
            File::delete(STORAGE . DS . 'news' . DS . $item['id'] . '.news.txt');
            File::delete(STORAGE . DS . 'news' . DS . $item['id'] . '.short.news.txt');
            Notification::set('success', __('News <i>:news</i> deleted', 'news', array(':news' => Html::toText($item['title']))));
        }
    }

    /**
     * _requestAjax
     */
    public static function _requestAjax()
    {
        if (Security::check(Request::get('csrf')) && Request::get('id') == 'news') {
            if (Request::get('image') == true) {
                View::factory('news/views/backend/addImg')->display();
                die();
            }
        }
        if (Security::check(Request::post('csrf')) && Request::get('id') == 'news') {
            if (Request::get('delete') == "image") {
                File::delete(Request::post('dir') . Request::post('id'));
                File::delete(Request::post('dir') . 'thumbnail' . DS . Request::post('id'));
                die();
            }
            if (Request::get('delete') == "item")
            {
                if (is_array(Request::post('items'))) {
                    NewsAdmin::$news = new Table('news');
                    foreach(Request::post('items') as $row)
                    {
                        NewsAdmin::deleteNews($row);
                    }
                    die ('deleted');
                }
                die('delete item');
            }
        }
        //exit('no action');
    }

    private  static function ReSize($img, $folder, $name, $opt)
    {
        $wmax   = (int)$opt['wmax'];
        $hmax   = (int)$opt['hmax'];
        $width  = (int)$opt['w'];
        $height = (int)$opt['h'];
        $resize = $opt['resize'];
        $ratio  = $width/$height;

        if ($img->width > $wmax or $img->height > $hmax) {
            if ($img->height > $img->width) {
                $img->resize($wmax, $hmax, Image::HEIGHT);
            } else {
                $img->resize($wmax, $hmax, Image::WIDTH);
            }
        }
        $img->save($folder.$name);

        switch ($resize) {
            case 'width' :   $img->resize($width, $height, Image::WIDTH);  break;
            case 'height' :  $img->resize($width, $height, Image::HEIGHT); break;
            case 'stretch' : $img->resize($width, $height); break;
            default :
                // crop
                if (($img->width/$img->height) > $ratio) {
                    //$img->resize($width, $height, Image::HEIGHT)->crop($width, $height, round(($img->width-$width)/2),0);
                    $img->resize($width, $height, Image::HEIGHT);
                } else {
                    //$img->resize($width, $height, Image::WIDTH)->crop($width, $height, 0, 0);
                    $img->resize($width, $height, Image::WIDTH);
                }
                break;
        }
        $img->save($folder.'thumbnail'.DS.$name);
    }
}