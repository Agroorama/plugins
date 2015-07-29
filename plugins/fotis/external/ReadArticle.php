<?php namespace Fotis\External\Components;

use Cms\Classes\ComponentBase;
use Fotis\External\Models\Article;
use Rainlab\User\Facades\Auth;
use DB;
include('simple_html_dom.php');
require_once('url_to_absolute.php');

// ini_set("user_agent","Mozilla/4.0 (compatible; MSIE 6.0;)"); 
// ini_set("max_execution_time", 0);
// ini_set("memory_limit", "10000M");

class ReadArticle extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Aricles',
            'description' => 'Aricles Shared'
        ];
    }

    public function defineProperties()
    {
        return ['take' => [
             'title'             => 'Number of Articles',
             'description'       => 'Number of Articles to Show',
             'default'           => '10',
             'type'              => 'string',
             'validationPattern' => '^[0-9]+$',
             'validationMessage' => 'The Default Limit property can contain only numeric symbols'
            ]
        ];
    }

    public function init(){

        $articles = Article::take($this->property('take'))->orderBy('id', 'desc')->get();
        $this->articles = $articles;
    }

    public function onLoadMoreData(){

        // sleep(5);
        $items = post('items');
        $articles = Article::skip($items)->take($this->property('take'))->orderBy('id', 'desc')->get();
        $this->articles = $articles;
        if (sizeof($articles) == 0) {
            return json_encode('DONE');
        }
    }

    public function onAddView(){

        $id = post('id');
        $temp = DB::table('fotis_external_articles')->where('id', '=', $id)->increment('total_views');
        $data['result'] = 'ok';
        $temp2 = Article::where('id', '=', $id)->first();
        $data['views'] = $temp2->total_views;
        $data['url'] = $temp2->article_url;
        return $data;
    }

    public function onGetData(){

        if (post('url') == null || trim(post('url')) == '') {
            return 'Error';
        }
        $data['url'] = $link = trim(post('url'));
        $data['image'] = $this->GetImageUrl($link);
        $data['title'] = $this->get_title($link);
        // trigger_error($this->get_title($link));
        if ($data['image'] == null || $data['title'] == null || $data['url'] == null
            || $data['image'] == 'No Image' || $data['title'] == 'No Title' || $data['url'] == 'No Url') {
            return 'Error';
        }
        return $data;
    }

    public function onSubmitData(){

        $bool = $this->checkifexists();
        if ($bool!=0) {

            if (post('image') == null || post('title') == null || post('articleurl') == null
                || post('image') == 'No Image' || post('title') == 'No Title' || post('articleurl') == 'No Url') {
                return 'Error';
            }
            // if ( $this->isImage(post('image')) && $this->isValidUrl(post('articleurl')) ) {
                $data['image']  = $image = trim(post('image'));
                $data['title'] = $title = trim(post('title'));
                $data['url'] = $url = trim(post('articleurl'));
            // }else{
            //     return 'Error';
            // }


            $db = new Article;
            $db->user_email = $data['email'] = Auth::getUser()->email;
            $db->user_name = $data['username'] = Auth::getUser()->name;
            $db->article_title = $title;
            $db->article_image = $image;
            $db->article_url = $url;
            $db->save();

            $articles = Article::take($this->property('take'))->orderBy('id', 'desc')->get();
            $this->articles = $articles;
            return 'OK';
        }else{

            return 'Already';
        }
    }

    public function checkifexists(){

        if (isset(Auth::getUser()->email)) {
                
        }else{
            trigger_error('login');
        }
        $email = Auth::getUser()->email;
        $articles = Article::where('user_email', '=', $email)->get();
        $bool = 1;
        $image = trim(post('image'));
        $title = trim(post('title'));
        $url = trim(post('articleurl'));
        $url2 = trim($url,'/');
        // $url2 = strtok($url2, '?');
        // $url2 = strtok($url2, '#');
        $url2 = preg_replace('/([^:])(\/{2,})/', '$1/', $url2);
        $url2 = trim($url2,'/');
        // $url = preg_replace('/&?return=[^&]*/', '', $url);
        // $url = parse_url($url, PHP_URL_PATH);
        // echo $url;
        // $url = filter_var(post('articleurl'), FILTER_SANITIZE_URL);
        // trigger_error($url2);
        
        foreach ($articles as $article) {
            if ( $url2 == $article->article_url) {
                $bool = 0;
            }
            if ( ($title == $article->article_title) && ($image == $article->article_image) ) {
                $bool = 0;
                // trigger_error($title . $article->article_title . $image . $article->article_image);
            }
        }
        // trigger_error($bool);
        return $bool;
    }

    public function GetImageUrl($url){

        $maxSize = -1;
//         $curl = curl_init(); 
// curl_setopt($curl, CURLOPT_URL, $url);  
// curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  
// curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);  
// $str = curl_exec($curl);  
// curl_close($curl);  
 
// $html= str_get_html($str); 
        $html = file_get_html($url);
        if ($html === FALSE) {
            return 'No Image';
        }
        $temp = array();
        $visited = array();
        foreach($html->find('img') as $e){
            $src = $e->src;
            if($src=='')continue;// it happens on your test url
            $imageurl = url_to_absolute($url, $src);//get image absolute url
            // ignore already seen images, add new images
            if(in_array($imageurl, $visited))continue;
            $visited[]=$imageurl;
            // get image
            $image=@getimagesize($imageurl);// get the rest images width and height
            if (($image[0] * $image[1]) > $maxSize) {   
                $maxSize = $image[0] * $image[1];  //compare sizes
                $biggest_img = $imageurl;
            }
        }
        return $biggest_img ? $biggest_img : 'No Image';
    }

    function get_title($url){

        $meta_title = 'No Title';
//         $curl = curl_init(); 
// curl_setopt($curl, CURLOPT_URL, $url);  
// curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  
// curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);  
// $str = curl_exec($curl);  
// curl_close($curl);  
 
// $html= str_get_html($str); 
        $html = file_get_html($url);
        // $html = mb_convert_encoding($html,'utf8','windows-1251');

        //To get Meta Title
        $temp = $html->find("title", 0);
        if ($temp) {
            $meta_title = $temp->innertext;
        }
        // $meta_title = str_replace('"', '', $meta_title);
        if (is_string($meta_title)) {
            // $meta_title = mb_detect_encoding($meta_title, "iso-8859-7");
            if (mb_detect_encoding($meta_title, 'UTF-8', true) == 'UTF-8') {
                
            }else{
                $meta_title = mb_convert_encoding($meta_title,'utf8','iso-8859-7');
            }
            // $meta_title = mb_convert_encoding($meta_title,'utf8','iso-8859-7');
            // trigger_error($meta_title);
            // $meta_title = utf8_encode($meta_title);
            // $meta_title = mb_detect_encoding($meta_title);
        }else{
            $meta_title = 'No title';
        }
        // $webpage = file_get_contents($url);
        // $title = explode("title>", $webpage);
        // $title = str_replace("</", "", $title[1]);

        //To get Meta Description
        // $meta_description = $html->find("meta[name='description']", 0)->content;

        //To get Meta Keywords
        // $meta_keywords = $html->find("meta[name='keywords']", 0)->content;

        return $meta_title;
        // $str = @file_get_contents($url);
        // if ($str === FALSE) {
        //     return 'No Title';
        // }
        // if(strlen($str)>0){
        //     $str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
        //     preg_match("/\<title\>(.*)\<\/title\>/i",$str,$title); // ignore case
        //     return $title[1] ? $title[1] : 'No Title';
        // }else{
        //     return 'No Title';
        // }

            // $meta = get_meta_tags($url);
            // return $meta['description'];
    }

    function isImage($url){

        // $params = array('http' => array(
        //           'method' => 'HEAD'
        //        ));
        // $ctx = stream_context_create($params);
        // $fp = @fopen($url, 'rb', false, $ctx);
        // if (!$fp) 
        // return false;  // Problem with url

        // $meta = stream_get_meta_data($fp);
        // if ($meta === false)
        // {
        // fclose($fp);
        // return false;  // Problem reading data from url
        // }

        // $wrapper_data = $meta["wrapper_data"];
        // if(is_array($wrapper_data)){
        // foreach(array_keys($wrapper_data) as $hh){
        //   if (substr($wrapper_data[$hh], 0, 19) == "Content-Type: image") // strlen("Content-Type: image") == 19 
        //   {
        //     fclose($fp);
        //     return true;
        //   }
        // }
        // }

        // fclose($fp);
        // return false;
        if (@getimagesize($external_link)) {
            return true;
        } else {
            return false;
        }
    }

    function isValidUrl($url){
        // first do some quick sanity checks:
        if(!$url || !is_string($url)){
            return false;
        }
        // quick check url is roughly a valid http request: ( http://blah/... ) 
        if( ! preg_match('/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $url) ){
            return false;
        }
        // the next bit could be slow:
        if($this->getHttpResponseCode_using_curl($url) != 200){
        //if($this->getHttpResponseCode_using_getheaders($url) != 200){  // use this one if you cant use curl
            return false;
        }
        // all good!
        return true;
    }

    function getHttpResponseCode_using_curl($url, $followredirects = true){
        // returns int responsecode, or false (if url does not exist or connection timeout occurs)
        // NOTE: could potentially take up to 0-30 seconds , blocking further code execution (more or less depending on connection, target site, and local timeout settings))
        // if $followredirects == false: return the FIRST known httpcode (ignore redirects)
        // if $followredirects == true : return the LAST  known httpcode (when redirected)
        if(! $url || ! is_string($url)){
            return false;
        }
        $ch = @curl_init($url);
        if($ch === false){
            return false;
        }
        @curl_setopt($ch, CURLOPT_HEADER         ,true);    // we want headers
        @curl_setopt($ch, CURLOPT_NOBODY         ,true);    // dont need body
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER ,true);    // catch output (do NOT print!)
        if($followredirects){
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,true);
            @curl_setopt($ch, CURLOPT_MAXREDIRS      ,10);  // fairly random number, but could prevent unwanted endless redirects with followlocation=true
        }else{
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,false);
        }
//      @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);   // fairly random number (seconds)... but could prevent waiting forever to get a result
//      @curl_setopt($ch, CURLOPT_TIMEOUT        ,6);   // fairly random number (seconds)... but could prevent waiting forever to get a result
//      @curl_setopt($ch, CURLOPT_USERAGENT      ,"Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1");   // pretend we're a regular browser
        @curl_exec($ch);
        if(@curl_errno($ch)){   // should be 0
            @curl_close($ch);
            return false;
        }
        $code = @curl_getinfo($ch, CURLINFO_HTTP_CODE); // note: php.net documentation shows this returns a string, but really it returns an int
        @curl_close($ch);
        return $code;
    }

    function getHttpResponseCode_using_getheaders($url, $followredirects = true){
        // returns string responsecode, or false if no responsecode found in headers (or url does not exist)
        // NOTE: could potentially take up to 0-30 seconds , blocking further code execution (more or less depending on connection, target site, and local timeout settings))
        // if $followredirects == false: return the FIRST known httpcode (ignore redirects)
        // if $followredirects == true : return the LAST  known httpcode (when redirected)
        if(! $url || ! is_string($url)){
            return false;
        }
        $headers = @get_headers($url);
        if($headers && is_array($headers)){
            if($followredirects){
                // we want the the last errorcode, reverse array so we start at the end:
                $headers = array_reverse($headers);
            }
            foreach($headers as $hline){
                // search for things like "HTTP/1.1 200 OK" , "HTTP/1.0 200 OK" , "HTTP/1.1 301 PERMANENTLY MOVED" , "HTTP/1.1 400 Not Found" , etc.
                // note that the exact syntax/version/output differs, so there is some string magic involved here
                if(preg_match('/^HTTP\/\S+\s+([1-9][0-9][0-9])\s+.*/', $hline, $matches) ){// "HTTP/*** ### ***"
                    $code = $matches[1];
                    return $code;
                }
            }
            // no HTTP/xxx found in headers:
            return false;
        }
        // no headers :
        return false;
    }

}