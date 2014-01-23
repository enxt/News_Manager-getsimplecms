<?php if (!defined('IN_GS')) {die('you cannot load this page directly.');}

/**
 * News Manager cache functions.
 */


/*******************************************************
 * @function nm_update_cache
 * @action store frequently accessed post data in cache files
 */
function nm_update_cache() {
  $posts = nm_get_cache_data();
  return nm_cache_to_xml($posts);
}

function nm_update_comments_cache($slug) {
    $comments = nm_get_cache_comment_data($slug);
    return nm_comments_cache_to_xml($slug, $comments);
}

/*******************************************************
 * @function nm_get_cache_data
 * @return arrays with relevant post data
 */
function nm_get_cache_data() {
  $posts = array();
  $files = getFiles(NMPOSTPATH);
  # collect all post data
  foreach ($files as $file) {
    if (isFile($file, NMPOSTPATH, 'xml')) {
      $data = getXML(NMPOSTPATH . $file);
      $time = strtotime($data->date);
      while (array_key_exists($time, $posts)) $time++;
      $posts[$time]['slug'] = basename($file, '.xml');
      $posts[$time]['title'] = strval($data->title);
      $posts[$time]['date'] = strval($data->date);
      $posts[$time]['tags'] = strval($data->tags);
      $posts[$time]['private'] = strval($data->private);
      $posts[$time]['image'] = strval($data->image);
    }
  }
  krsort($posts);
  return $posts;
}

function nm_get_cache_comment_data($slug) {
    $comments = array();
    $NMCOMMENTSPATH = NMPOSTPATH . $slug . '/';
    if(!is_dir($NMCOMMENTSPATH)) return $comments;
    $files = getFiles($NMCOMMENTSPATH);


    $comments_summary = array(
        'total' => 0,
        'readed' => 0,
        'approved' => 0
    );
    #collect all comments data
    foreach($files as $file) {
        if (isFile($file, $NMCOMMENTSPATH, 'xml')) {
            $data = getXML($NMCOMMENTSPATH . $file);

            $time = strtotime($data->date);
            while (array_key_exists($time, $comments)) $time++;

            $attr = $data->attributes();
            $id = strval($attr['id']);
            $readed = (strval($attr['readed']) == 'true')?1:0;
            $approved = (strval($attr['approved']) == 'true')?1:0;

            $comments_summary['total'] = $comments_summary['total']+1;
            $comments_summary['readed'] = $comments_summary['readed']+$readed;
            $comments_summary['approved'] = $comments_summary['approved']+$approved;

            $comments[$time]['id'] = $id;
            $comments[$time]['readed'] = strval($attr['readed']);
            $comments[$time]['approved'] = strval($attr['approved']);
            $comments[$time]['username'] = strval($data->username);
            $comments[$time]['date'] = strval($data->date);
            $comments[$time]['email'] = strval($data->email);
            $comments[$time]['link'] = strval($data->link);
            $comments[$time]['message'] = strval($data->message);
        }
    }

    krsort($comments);
    $comments['summary'] = $comments_summary;
    return $comments;
}


/*******************************************************
 * @function nm_cache_to_xml
 * @action write post data to xml file
 */
function nm_cache_to_xml($posts) {
  $xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');
  foreach ($posts as $post) {
    $item = $xml->addChild('item');
    $elem = $item->addChild('slug');
    $elem->addCData($post['slug']);
    $elem = $item->addChild('title');
    $elem->addCData($post['title']);
    $elem = $item->addChild('date');
    $elem->addCData($post['date']);
    $elem = $item->addChild('tags');
    $elem->addCData($post['tags']);
    $elem = $item->addChild('private');
    $elem->addCData($post['private']);
    $elem = $item->addChild('image');
    $elem->addCData($post['image']);
  }
  return @XMLsave($xml, NMPOSTCACHE);
}

function nm_comments_cache_to_xml($slug, $comments) {  
    $xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');
    $xmlsum = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');
    foreach ($comments as $key=>$comment) {
        if($key != 'summary') {
            $item = $xml->addChild('item');

            $elem = $item->addChild('id');
            $elem->addCData($comment['id']);

            $elem = $item->addChild('readed');
            $elem->addCData($comment['readed']);

            $elem = $item->addChild('approved');
            $elem->addCData($comment['approved']);

            $elem = $item->addChild('username');
            $elem->addCData($comment['username']);

            $elem = $item->addChild('date');
            $elem->addCData($comment['date']);

            $elem = $item->addChild('email');
            $elem->addCData($comment['email']);

            $elem = $item->addChild('link');
            $elem->addCData($comment['link']);

            $elem = $item->addChild('message');
            $elem->addCData($comment['message']);
	    }
        else {
            $ditem = $xmlsum->addChild('item');

            $elem = $ditem->addChild('total');
            $elem->addCData($comment['total']);

            $elem = $ditem->addChild('readed');
            $elem->addCData($comment['readed']);

            $elem = $ditem->addChild('approved');
            $elem->addCData($comment['approved']);

            @XMLsave($xmlsum, NMPOSTCOMMENTSCACHE.$slug.'_summary.xml');
        }
  }
  return @XMLsave($xml, NMPOSTCOMMENTSCACHE.$slug.'.xml');
}






?>
