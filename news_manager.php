<?php

/*
Plugin Name: News Manager
Description: A blog/news plugin for GetSimple
Version: 2.5 beta 15
Author: Rogier Koppejan
Updated by: Carlos Navarro

*/


# get correct id for plugin
$thisfile = basename(__FILE__, '.php');

# register plugin
register_plugin(
  $thisfile,
  'News Manager',
  '2.5 beta 15',
  'Rogier Koppejan, Carlos Navarro',
  'http://www.cyberiada.org/cnb/news-manager/',
  'A blog/news plugin for GetSimple',
  'pages',
  'nm_admin'
);

# includes
require_once('news_manager/inc/common.php');

# language
i18n_merge('news_manager') || i18n_merge('news_manager', 'en_US');

if(isAjax()) {
    if (nm_env_check()) {
        if (isset($_GET['tgglread']) && isset($_GET['slug'])) {
            nm_toggle_read_comment($_GET['slug'], $_GET['tgglread']);
        }
        if (isset($_GET['tgglappr']) && isset($_GET['slug'])) {
            nm_toggle_approved_comment($_GET['slug'], $_GET['tgglappr']);
        }
    }
    exit;
}
else {
# hooks
add_action('pages-sidebar', 'createSideMenu', array($thisfile, i18n_r('news_manager/PLUGIN_NAME')));
add_action('header', 'nm_header_include');
add_action('index-pretemplate', 'nm_frontend_init');
//add_filter('content', 'nm_site'); // deprecated
if (!function_exists('generate_sitemap')) { // exclude GetSimple 3.1+
  add_action('sitemap-additem', 'nm_sitemap_include');
}
add_action('plugin-hook', 'nm_patch_plugin_management');

# scripts (GetSimple 3.1+)
if (function_exists('register_script')) {
  if (isset($_GET['id']) && $_GET['id'] == 'news_manager' && (isset($_GET['edit']) || isset($_GET['settings']))) {
    if (!defined('GSNOCDN') || !GSNOCDN) {
      register_script('jquery-validate','//ajax.aspnetcdn.com/ajax/jquery.validate/1.10.0/jquery.validate.min.js', '1.10.0', false);
    } else {
      register_script('jquery-validate',$SITEURL.'plugins/news_manager/js/jquery.validate.min.js', '1.10.0', false);
    }
    queue_script('jquery-validate', GSBACK);
  }
}
}


/*******************************************************
 * @function nm_admin
 * @action back-end main function
 */
function nm_admin() {
  if (nm_env_check()) {
    # post management
    if (isset($_GET['edit'])) {
        nm_edit_post($_GET['edit']);
	} elseif (isset($_GET['comment'])) {
		nm_edit_comments($_GET['comment']);
    } elseif (isset($_POST['post'])) {
        nm_save_post();
        nm_admin_panel();
    } elseif (isset($_GET['delete'])) {
        nm_delete_post($_GET['delete']);
        nm_admin_panel();
    } elseif (isset($_GET['cmdelete']) && isset($_GET['slug'])) {
        nm_delete_comment($_GET['slug'], $_GET['cmdelete']);
        nm_edit_post($_GET['slug']);
    /*} elseif (isset($_GET['tgglread']) && isset($_GET['slug'])) {
        nm_toggle_read_comment($_GET['slug'], $_GET['tgglread']);
        //nm_edit_post($_GET['slug']);
    } elseif (isset($_GET['tgglappr']) && isset($_GET['slug'])) {
        nm_toggle_approved_comment($_GET['slug'], $_GET['tgglappr']);
        //nm_edit_post($_GET['slug']);*/
    } elseif (isset($_GET['restore'])) {
        nm_restore_post($_GET['restore']);
        nm_admin_panel();
    # settings management
    } elseif (isset($_GET['settings'])) {
        nm_edit_settings();
    } elseif (isset($_POST['settings'])) {
        nm_save_settings();
        nm_admin_panel();
    } elseif (isset($_GET['htaccess'])) {
        nm_generate_htaccess();
    } else {
        nm_admin_panel();
    }
  }
}

/*******************************************************
 * @function nm_frontend_init
 * @action front-end main function
 * @since 2.4
 */
function nm_frontend_init() {
  global $NMPAGEURL;
  nm_i18n_merge();
  $url = strval(get_page_slug(false));
  if ($url == $NMPAGEURL) {
    global $content, $metad;
    $metad_orig = ($metad == '' ? ' ' : $metad);
    $metad = ' ';
    ob_start();
    echo PHP_EOL;
    if (isset($_POST['search'])) {
        nm_show_search_results();
    } elseif (isset($_GET['archive'])) {
        $archive = $_GET['archive'];
        nm_show_archive($archive);
    } elseif (isset($_GET['tag'])) {
        $tag = rawurldecode($_GET['tag']);
        nm_show_tag($tag);
    } elseif (isset($_GET['post'])) {
        $slug = $_GET['post'];
        nm_show_single($slug);
    } elseif (isset($_GET['page'])) {
        $index = $_GET['page'];
        nm_show_page($index);
    } else {
        $metad = $metad_orig;
        nm_show_page();
    }
    $content = ob_get_contents();
    ob_end_clean();
    $content = addslashes(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
  }
}

function isAjax() {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
}

/*******************************************************
 * @deprecated as of 2.4+
 */
function nm_site($content) {
  return '[deprecated]';
}
