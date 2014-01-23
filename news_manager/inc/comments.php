<?php if (!defined('IN_GS')) {die('you cannot load this page directly.');}

/**
 * News Manager comments management functions.
 */


function nm_save_comment($slug) {
    $comments_dir = NMPOSTPATH.$slug.'/';

    if(!is_dir($comments_dir))
        nm_create_dir($comments_dir);

    $comments = getFiles($comments_dir);
    #out from array non xml files
    if(!empty($comments)) foreach($comments as $key=>$comfile) if(!preg_match('/.*\.xml$/', $comfile)) unset($comments[$key]);
    //$comment_num = (!empty($comments))?(count($comments)+1):1;
    $comment_num = uniqid();

    $file = $comments_dir . 'comment_' . $comment_num . '.xml';

    $xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><comment></comment>');

    $xml->addAttribute('id', $comment_num);
    $xml->addAttribute('readed', 'false');
    $xml->addAttribute('approved', 'false');

    $obj = $xml->addChild('date');
    $obj->addCData(date('Y-m-d H:i'));
    $obj = $xml->addChild('username');
    $obj->addCData($_POST['username']);
    $obj = $xml->addChild('email');
    $obj->addCData($_POST['email']);
    $obj = $xml->addChild('link');
    $obj->addCData($_POST['link']);
    $obj = $xml->addChild('message');
    $obj->addCData($_POST['comment']);

    # write data to file
    if (@XMLsave($xml, $file) && nm_update_comments_cache($slug))
        echo "OK";
    //(i18n_r('news_manager/SUCCESS_SAVE'), false, @$backup);
    //else
    //nm_display_message(i18n_r('news_manager/ERROR_SAVE'), true);
}


function nm_delete_comment($slug, $comment_id) {
    $file = 'comment_' . $comment_id . '.xml';
    # path traversal?
    if (dirname(realpath(NMPOSTPATH.$slug.'/'.$file)) != realpath(NMPOSTPATH.$slug.'/')) {
        nm_display_message('<b>Error:</b> incorrect path', true); // not translated
    } else {
        # delete post
        if (file_exists(NMPOSTPATH.$slug.'/' . $file)) {
            if(!file_exists(NMBACKUPPATH.$slug.'/')) nm_create_dir(NMBACKUPPATH.$slug.'/');
            if (nm_rename_file(NMPOSTPATH.$slug.'/' . $file, NMBACKUPPATH.$slug.'/'. $file) && nm_update_comments_cache($slug))
                nm_display_message(i18n_r('news_manager/SUCCESS_DELETE'), false, $slug);
            else
                nm_display_message(i18n_r('news_manager/ERROR_DELETE'), true);
        }
    }
}


function nm_toggle_read_comment($slug, $comment_id) {
    $file = 'comment_' . $comment_id . '.xml';
    $dir_path = NMPOSTPATH . $slug . '/';
    $file_path = $dir_path . $file;

    if (dirname(realpath($file_path)) != realpath($dir_path)) {
        nm_display_message('<b>Error:</b> incorrect path', true); // not translated
    }
    else {
        $data = @getXML($file_path);
        $attr = $data->attributes();
        $attrname = 'readed';
        $readed = strval($attr[$attrname]);
        if($readed == 'false') $data->attributes()->$attrname = 'true';
        else $data->attributes()->$attrname = 'false';
        /*if (@XMLsave($data, $file_path) && nm_update_comments_cache($slug))
            nm_display_message(i18n_r('news_manager/SUCCESS_DELETE'), false, $slug);
        else
            nm_display_message(i18n_r('news_manager/ERROR_DELETE'), true);*/
        if (@XMLsave($data, $file_path) && nm_update_comments_cache($slug))
            echo "Success";
        else
            echo "Error";
    }
}

function nm_toggle_approved_comment($slug, $comment_id) {
    $file = 'comment_' . $comment_id . '.xml';
    $dir_path = NMPOSTPATH . $slug . '/';
    $file_path = $dir_path . $file;

    if (dirname(realpath($file_path)) != realpath($dir_path)) {
        nm_display_message('<b>Error:</b> incorrect path', true); // not translated
    }
    else {
        $data = @getXML($file_path);
        $attr = $data->attributes();
        $attrname = 'approved';
        $attrreadname = 'readed';
        $approved = strval($attr[$attrname]);
        if($approved == 'false') {
            $data->attributes()->$attrname = 'true';
            $readed = strval($attr[$attrreadname]);
            if($readed == 'false') $data->attributes()->$attrreadname = 'true';
        }
        else $data->attributes()->$attrname = 'false';
        /*if (@XMLsave($data, $file_path) && nm_update_comments_cache($slug))
            nm_display_message(i18n_r('news_manager/SUCCESS_DELETE'), false, $slug);
        else
            nm_display_message(i18n_r('news_manager/ERROR_DELETE'), true);*/

        if (@XMLsave($data, $file_path) && nm_update_comments_cache($slug))
            echo "Success";
        else
            echo "Error";
    }
}