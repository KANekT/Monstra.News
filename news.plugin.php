<?php

/**
 *  News plugin
 *
 *  @package Monstra
 *  @subpackage Plugins
 *  @copyright Copyright (C) KANekT @ http://kanekt.ru
 *  @license http://creativecommons.org/licenses/by-nc/4.0/
 *  Creative Commons Attribution-NonCommercial 4.0
 *  Donate Web Money Z104136428007 R346491122688
 *  Yandex Money 410011782214621
 *
 */

// Register plugin
Plugin::register( __FILE__,
    __('News', 'news'),
    __('News plugin for Monstra', 'news'),
    '3.0.0',
    'KANekT',
    'http://kanekt.ru/',
    'news');

if (Option::get('news_is_main') > 0)
{
    Uri::$default_component = 'news';
}

// Load News Admin for Editor and Admin
if (Session::exists('user_role') && in_array(Session::get('user_role'), array('admin', 'editor'))) {
    Plugin::admin('news');
}

/*
 * Register for Developer Helper
 */

if (!Registry::exists('dev_valid_backend'))
    Javascript::add('plugins/news/dev/js/validate.js', 'backend', 11);
if (!Registry::exists('dev_bootstrap_file_upload')) {
    Javascript::add('plugins/news/dev/js/bootstrap-fileupload-setting.js', 'backend', 19);
}
if (!Registry::exists('dev_fancy_frontend'))
{
    Javascript::add('plugins/news/dev/js/jquery.fancybox.pack.js', 'frontend', 15);
    Javascript::add('plugins/news/dev/js/jquery.fancybox-media.js', 'frontend', 16);
    Stylesheet::add('plugins/news/dev/css/jquery.fancybox.css', 'frontend',15);

    Javascript::add('plugins/news/dev/js/script.js', 'frontend', 17);
}

Registry::set('dev_valid_backend', 1);
Registry::set('dev_fancy_frontend', 1);
Registry::set('dev_bootstrap_file_upload', 1);

Shortcode::add('news', 'News::_shortcode');
Javascript::add('plugins/news/js/admin.js', 'backend', 15);
Stylesheet::add('plugins/news/css/frontend.css', 'frontend',15);

class News extends Frontend {

    public static $news = null; // news table @object
    public static $_news = null; // news item @object
    public static $meta = array(); // meta tags news @array
    public static $template = ''; // news template content @string
    public static $slug;
    public static $path = '/';

    public static function main(){

        News::$news = new Table('news');

        News::$meta['title'] = __('News', 'news');
        News::$meta['keywords'] = '';
        News::$meta['description'] = '';
        $uri = Uri::segments();
        $segment = 0;
        if($uri[$segment] == 'news' || Option::get('news_is_main') > 0) {
            if (Option::get('news_is_main') == 0)
            {
                News::$path = '/news/';
                $segment++;
            }

            if (isset($uri[$segment]) && $uri[$segment] != '')
            {
                switch($uri[$segment])
                {
                    case 'page':
                        News::getNews($uri, $segment);
                        break;
                    default:
                        News::getNewsBySlug($uri, $segment);
                        break;
                }
            }
            else{
                News::getNews($uri, $segment);
            }
        }
    }


    /**
     * get News
     */
    private static function getNews($uri, $segment, $parent = ""){

        $opt['site_url'] = Option::get('siteurl');
        $opt['url'] = $opt['site_url'] . '/public/uploads/news/';
        $opt['dir'] = ROOT . DS . 'public' . DS . 'uploads' . DS . 'news' . DS;
        $limit    = Option::get('news_limit');

        if (Request::get('tag')) {
            $query = '[status="published" and contains(tags, "'.Request::get('tag').'")]';
            Notification::set('tag', Request::get('tag'));
        } else {
            $query = '[status="published" and parent="'.$parent.'"]';
            Notification::clean();
        }

        $records_all = News::$news->select($query, 'all', null, array('id', 'slug', 'name', 'hits', 'date', 'parent'));

        $count_news = count($records_all);

        $opt['pages'] = ceil($count_news/$limit);
        $segment_1 = $segment+1;
        $opt['page'] = (isset($uri[$segment]) and isset($uri[$segment_1]) and $uri[$segment_1] != '' and $uri[$segment] != 'page') ? (int)$uri[$segment_1] : 1;
        if($opt['page'] < 1 or $opt['page'] > $opt['pages']) {
            News::error404();
        } else {

            $start = ($opt['page']-1)*$limit;

            $records_sort = Arr::subvalSort($records_all, 'date', 'DESC');

            if($count_news > 0) $records = array_slice($records_sort, $start, $limit);
            else $records = array();

            News::$template = View::factory('news/views/frontend/index')
                ->assign('records', $records)
                ->assign('opt', $opt)
                ->render();
        }
    }

    /**
     * Last news
     * <ul><?php News::Last(3);?></ul>
     */
    public static function Last($count=3, $parent = '') {
        return News::getNewsList($count, 'last', $parent);
    }

    /**
     * Best views
     * <ul><?php News::TopViews(5);?></ul>
     */
    public static function TopViews($count=3, $parent = '') {
        return News::getNewsList($count, 'views', $parent);
    }

    /**
     * News views
     * <ul><?php News::Block(5);?></ul>
     */
    public static function Block($count=3, $parent = '') {
        return News::getNewsList($count, 'block', $parent);
    }

    /**
     * Shortcode news
     * <ul>{news list="last" count=3}</ul>
     * <ul>{news list="views" count=3}</ul>
     * <ul>{news list="block" count=3}</ul>
     */
    public static function _shortcode($attributes) {
        extract($attributes);

        $count = (isset($count)) ? (int)$count : 3;
        $parent = (isset($parent)) ? (string)$parent : '';
        if (isset($list)) {
            return News::getNewsList($count, $list, $parent, false);
        }
    }

    /**
     * List news
     */
    private static function getNewsList($count, $action, $parent='', $display=true){
        if (Option::get('news_is_main') == 0)
        {
            News::$path = '/news/';
        }

        $opt['site_url'] = Option::get('siteurl');
        $opt['url'] = $opt['site_url'] . '/public/uploads/news/';
        $opt['dir'] = ROOT . DS . 'public' . DS . 'uploads' . DS . 'news' . DS;
        News::$news = new Table('news');

        $sort = ($action == 'views') ? 'hits' : 'date';

        $records_all = News::$news->select('[status="published" and parent="'.$parent.'"]', 'all', null, array('id', 'slug', 'name', 'hits', 'date', 'parent'));
        $records_sort = Arr::subvalSort($records_all, $sort, 'DESC');

        if(count($records_sort)>0) {
            $records = array_slice($records_sort, 0, $count);

            switch($action)
            {
                case 'block':
                    $output = View::factory('news/views/frontend/block')
                        ->assign('records', $records)
                        ->assign('opt', $opt)
                        ->render();
                    break;
                case 'views':
                    $output = View::factory('news/views/frontend/views')
                        ->assign('records', $records)
                        ->assign('opt', $opt)
                        ->render();
                    break;
                default:
                    $output = View::factory('news/views/frontend/last')
                        ->assign('records', $records)
                        ->assign('opt', $opt)
                        ->render();
                    break;

            }

            if($display) echo $output; else return $output;
        }
    }

    /**
     * get Current news
     */
    private static function getNewsBySlug($uri, $segment){
        $segment_1 = $segment + 1;
        if (isset($uri[$segment_1]))
        {
            $slug = $uri[$segment_1];
            $parent_name = News::$news->select('[slug="'.$uri[$segment].'"]', null);
        }
        else
            $slug = $uri[$segment];
        $opt['site_url'] = Option::get('siteurl');
        $opt['url'] = $opt['site_url'] . '/public/uploads/news/';
        $opt['dir'] = ROOT . DS . 'public' . DS . 'uploads' . DS . 'news' . DS;
        $record = News::$news->select('[slug="'.$slug.'"]', null);
        News::$slug = $slug;

        if($record) {
            News::$_news = $record;

            if(empty($record['title'])) $record['title'] = $record['name'];

            News::$meta['title'] = $record['title'];
            News::$meta['keywords'] = $record['keywords'];
            News::$meta['description'] = $record['description'];

            $record['hits'] = News::hits($record['id'], $record['hits']);

            News::$template = View::factory('news/views/frontend/item')
                ->assign('item', $record)
                ->assign('opt', $opt)
                ->render();
        } else {
            News::error404();
        }
    }

    /**
     * Get news breadcrumbs
     * @author Pronin Andrey / KANekT
     *
     *  <code>
     *      echo News::Breadcrumbs();
     *  </code>
     *
     */
    public static function Breadcrumbs($item, $opt)
    {
        if ($item['parent'] != "")
        {
            $news = News::$news->select('[slug="'.$item['parent'].'"]', null);
            $item['parent_name'] = $news['name'];
        }

        // Display view
        return View::factory('news/views/frontend/breadcrumbs')
            ->assign('item', $item)
            ->assign('opt', $opt)
            ->render();
    }

    /**
     * Get News content by id
     * @author Pronin Andrey / KANekT
     *
     *  <code>
     *      echo News::ContentById(1, true);
     *  </code>
     *
     * @param $id
     * @param bool $short
     * @return string
     */
    public static function ContentById($id, $short=false) {
        return News::getContentById($id, $short);
    }

    private static function getContentById($id, $short=false) {
        if($short) {
            $content = Text::toHtml(File::getContent(STORAGE . DS . 'news' . DS . $id . '.short.news.txt'));
        } else {
            $content = Text::toHtml(File::getContent(STORAGE . DS . 'news' . DS . $id . '.news.txt'));
        }

        return $content;
    }

    /**
     * Get tags
     * @author Romanenko Sergey / Awilum
     *
     *  <code>
     *      echo News::Tags();
     *  </code>
     *
     * @param null $slug
     * @return string
     */
    public static function Tags($slug = null) {

        // Display view
        return View::factory('news/views/frontend/tags')
            ->assign('tags', News::getTagsArray($slug))
            ->render();

    }

    /**
     * Get tags array
     * @author Romanenko Sergey / Awilum
     *
     * @param null $slug
     * @return array
     */
    private static function getTagsArray($slug = null) {

        // Init vars
        $tags = array();
        $tags_string = '';

        if ($slug == null) {
            $posts = News::$news->select('[status="published"]', 'all');
        } else {
            $posts = News::$news->select('[status="published" and slug="'.$slug.'"]', 'all');
        }

        foreach($posts as $post) {
            $tags_string .= $post['tags'].',';
        }

        $tags_string = substr($tags_string, 0, strlen($tags_string)-1);

        // Explode tags in tags array
        $tags = explode(',', $tags_string);

        // Remove empty array elementss
        foreach ($tags as $key => $value) {
            if ($tags[$key] == '') {
                unset($tags[$key]);
            }
        }

        // Trim tags
        array_walk($tags, create_function('&$val', '$val = trim($val);'));

        // Get unique tags
        $tags = array_unique($tags);

        // Return tags
        return $tags;
    }

    /**
     * Get related news
     *
     *  <code>
     *      echo News::Related();
     *  </code>
     *
     * @param null $limit
     * @return string
     */
    public static function Related($limit = null) {
        News::$news = new Table('news');

        $uri = Uri::segments();
        if($uri[0] == 'news') {
            if (isset($uri[2]))
            {
                News::$slug = $uri[2];
            }
            if (isset($uri[1]))
            {
                News::$slug = $uri[1];
            }
        }
        return News::getRelated($limit);
    }

    /**
     * Get related posts
     * @author Romanenko Sergey / Awilum
     *
     * @param null $limit
     * @return string
     */
    private static function getRelated($limit = null) {

        $related_posts = array();
        $tags = News::getTagsArray(News::$slug);

        foreach($tags as $tag) {

            $query = '[status="published" and contains(keywords, "'.$tag.'") and slug!="'.News::$slug.'"]';

            if ($result = Arr::subvalSort(News::$news->select($query, ($limit == null) ? 'all' : (int)$limit), 'date', 'DESC')) {
                $related_posts = $result;
            }
        }

        // Display view
        return View::factory('news/views/frontend/related')
            ->assign('related_posts', $related_posts)
            ->render();

    }


    /**
     * Get Children News
     *
     * @param $slug
     * @return string
     */
    public static function Children($slug)
    {
        $child = Arr::subvalSort(News::$news->select('[parent="'.$slug.'"]', 'all'), 'date', 'DESC');

        if ($child != null)
        {
            return View::factory('news/views/frontend/children')
                ->assign('items', $child)
                ->render();
        }

        return '';
    }

    public static function title(){
        return News::$meta['title'];
    }

    public static function keywords(){
        return News::$meta['keywords'];
    }

    public static function description(){
        return News::$meta['description'];
    }

    public static function content(){
        $content = Filter::apply('content', News::$template);
        return $content;
    }

    public static function template() {
        if (News::$_news['template'] == '') return Option::get('news_template'); else return News::$_news['template'];
    }

    private static function error404() {
        if (BACKEND == false) {
            News::$template = Text::toHtml(File::getContent(STORAGE . DS . 'pages' . DS . '1.page.txt'));
            News::$meta['title'] = 'error404';
            Response::status(404);
        }
    }

    private static function hits($id, $hits) {
        if (Session::exists('hits'.$id) == false) {
            $hits++;
            if(News::$news->updateWhere('[id='.$id.']', array('hits' => $hits))) {
                Session::set('hits'.$id, 1);
            }
        }

        return $hits;
    }

    /**
     * current page
     * pages all
     * site_url
     * limit pages
     */
    public static function paginator($current, $pages, $urls, $sections = 1, $limit_pages=10) {

        $content = '';
        if (is_array($urls))
        {
            $url = $urls[0];
            $req = $urls[1];
        }
        else{
            $url = $urls;
            $req = '';
        }
        if ($pages > 1) {

            // pages count > limit pages
            if ($pages > $limit_pages) {
                $start = ($current <= 6) ? 1 : $current-3;
                $finish = (($pages-$limit_pages) > $current) ? ($start + $limit_pages - 1) : $pages;
            } else {
                $start = 1;
                $finish = $pages;
            }

            // pages list
            $content .= '<div class="pagination"><ul>';

            // next
            if($current!=$pages && $sections > 0)
            {
                $content .= '<li><a href="'.$url.($current+1).$req.'">'.__('Next', 'dev').'</a></li>';
            }

            if (($pages > $limit_pages) and ($current > 6)) {
                $content .= '<li><a href="'.$url.'1'.$req.'">1</a></li>';
            }

            for ($i = $start; $i <= $finish; $i++) {
                $class = ($i == $current) ? ' class="active"' : '';
                $content .= '<li '.$class.'><a href="'.$url.$i.$req.'">'.$i.'</a></li>';
            }

            if (($pages > $limit_pages) && ($current < ($pages - $limit_pages))) {
                $content .= '<li><a href="'.$url.$pages.$req.'">'.$pages.'</a></li>';
            }

            // prev
            if($current!=1 && $sections > 0)
            {
                $content .= '<li><a href="'.$url.($current-1).$req.'">'.__('Prev', 'dev').'</a></li>';
            }
            $content .= '</ul></div>';
        }
        return $content;
    }

}