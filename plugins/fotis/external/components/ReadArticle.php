<?php namespace Fotis\External\Components;

use Cms\Classes\ComponentBase;
use Fotis\External\Models\Article;
use Rainlab\User\Models\User;
use Rainlab\User\Facades\Auth;
use DB;
use DOMDocument;
use DOMXPath;
require_once('url_to_absolute.php');
error_reporting(0);

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

    public function boot(){

        User::extend(function($model) 
        {
            $model->hasOne['user'] = ['Fotis\External\Models\Article'];
        });
    }

    public function init(){

        $articles = Article::with('user')->take($this->property('take'))->orderBy('id', 'desc')/*->remember(1)*/->get();
        $this->articles = $articles;
        $this->page['items'] = $this->property('take');
        $this->page['hasArticles'] = false; 
        if (isset(Auth::getUser()->id)) {
            $this->page['current_user_id'] = Auth::getUser()->id;
            if ( Article::where('user_id', '=', Auth::getUser()->id)->first() ) {
                $this->page['hasArticles'] = true;
            }
        }else{
            $this->page['current_user_id'] = -1;
        }
    }

    function addScheme($url, $scheme = 'http://'){

        return parse_url($url, PHP_URL_SCHEME) === null ? $scheme . $url : $url;
    }

    public function onGetData(){

        $articles = Article::with('user')->take($this->property('take'))->orderBy('id', 'desc')->get();
        $url = $this->addScheme( preg_replace( "/^https:/i", "http:", trim(post('url')) ) );
        if ( $this->isValidUrl($url) ) {

        }else{
            return 'Error';
        }

        $document = $this->GetDocument($url);
        $data['images'] = $this->GetImage($document, $url);
        $data['title'] = $this->GetTitle($document, $url);
        $data['url'] = $url;
        return $data;
    }

    public function onSubmitData(){

        $data['result'] = 'Error';
        if (isset(Auth::getUser()->email)) {

        }else{
            $data['message'] = 'Σύνδεση';
            return $data;
        }
        if (post('image') && post('title') && post('articleurl')) {
            $data['image']  = $image = trim(post('image'));
            $data['title'] = $title = trim(post('title'));
            $data['url'] = $url = preg_replace( '/([^:])(\/{2,})/', '$1/', trim( trim( post('articleurl'), '/' ) ) );
        }else{
            $data['message'] = 'Πρόβλημα στο σύνδεσμο';
            return $data;
        }
    
        $check1 = $this->checkifexists($url, $image, $title);
        if ($check1 == 0 ) {

            $data['message'] = 'Ήδη στο χρονολόγιο';
            return $data;
        }
        $check2 = $this->isImage($image);
        if ($check2 == 0 ) {

            $data['message'] = 'Πρόβλημα με εικόνα';
            return $data;
        }
        $check3 = $this->isTitle($title);
        if ($check3 == 0 ) {

            $data['message'] = 'Πρόβλημα με τίτλο';
            return $data;
        }
        $db = new Article;
        $db->user_id = Auth::getUser()->id;
        $db->user_email = $data['email'] = Auth::getUser()->email;
        $db->user_name = $data['username'] = Auth::getUser()->name;
        $db->article_title = $title;
        $db->article_image = $image;
        $db->article_url = $url;
        $db->save();

        $articles = Article::with('user')->take($this->property('take'))->orderBy('id', 'desc')->get();
        $this->articles = $articles;
        // return ['#wallmessages' => $this->renderPartial('ReadArticle::ArticleList')];
        $data['result'] = 'Success';
        $data['message'] = 'OK';
        return $data;
    }

    function checkifexists($url, $image, $title){

        $bool = 1;
        $articles = Article::where('user_id', '=', Auth::getUser()->id)->get();

        foreach ($articles as $article) {
            if ( $url == $article->article_url) {
                $bool = 0;
            }
            if ( ($title == $article->article_title) && ($image == $article->article_image) ) {
                $bool = 0;
            }
            $url = strtok($url, '?');
            $url = strtok($url, '#');
            if ( ( preg_replace( '/([^:])(\/{2,})/', '$1/', trim( trim( $url, '/' ) ) ) == $article->article_url) && ($title == $article->article_title) ) {
                $bool = 0;
            }
        }
        return $bool;
    }

    function isImage($image){

        $raw = $this->ranger($image);
        // if 1 at least true
        if ( @getimagesize($image) || @imagecreatefromstring($raw) ) {
            return 1;
        }else{
            return 0;
        }
    }

    function ranger($url){
        $headers = array(
            "Range: bytes=0-32768"
            );

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    function isTitle($title){

        if (is_string($title)) {
            return 1;
        }else{
            return 0;
        }
    }

    public function onDeletePost(){

        $id = post('postid');
        $userid = post('userid');
        if ( $this->checkdeleterequest($id, $userid) ) {

            try {
                Article::where('id', '=', $id)->delete();
                return 'OK';
            } catch (Exception $e) {
                return 'Error';
            }
        }else{

            return 'Denied';
        }
    }

    function checkdeleterequest($id, $userid){

        if (isset(Auth::getUser()->id)) {
            if ( ($userid == Auth::getUser()->id) && (Article::where('id', '=', $id)->where('user_id', '=', $userid)->first()) ){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }

    function GetDocument($url){

        $request = curl_init();
        curl_setopt_array($request, array
        (
            CURLOPT_URL => $url,

            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HEADER => FALSE,

            CURLOPT_SSL_VERIFYPEER => TRUE,
            CURLOPT_CAINFO => 'cacert.pem',

            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_MAXREDIRS => 2,
        ));

        $response = curl_exec($request);
        curl_close($request);

        $document = new DOMDocument();
        if($response)
        {
            libxml_use_internal_errors(true);
            if ( preg_match('!!u', file_get_contents($url)) ){
                $document->loadHTML(mb_convert_encoding($response, 'HTML-ENTITIES', 'UTF-8'));
            }else{
                $document->loadHTML(mb_convert_encoding($response, 'HTML-ENTITIES', 'iso-8859-7'));
            }
            libxml_clear_errors();
        }
        return $document;
    }

    function GetImage($document, $url){

        foreach($document->getElementsByTagName('img') as $img){
            $image = array
            (
                'src' => @url_to_absolute($url, $img->getAttribute('src')),
            );
            if( ! $image['src'])
                continue;
            if ( !$this->endsWith($image['src'], "gif") )
                $images[$image['src']] = $image;
        }
        if (isset($images)) {
            return $images;
        }else{
            return 0;
        }
    }

    public function GetTitle($document, $url){        

        $list = $document->getElementsByTagName("title");
        if ($list->length > 0) {
            $title = $list->item(0)->textContent;
            return trim($title);
        }else{
            return $url;
        }
    }

    function startsWith($haystack, $needle) {
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    function endsWith($haystack, $needle) {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    public function onLoadMoreData(){

        $items = post('items');
        $articles = Article::with('user')->skip($items)->take($this->property('take'))->orderBy('id', 'desc')->get();
        $this->articles = $articles;
        if (sizeof($articles) == 0) {
            return 'DONE';
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

    function isValidUrl($url){
        if(!$url || !is_string($url)){
            return 0;
        }
        if( ! preg_match('/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $url) ){
            return 0;
        }
        if($this->getHttpResponseCode_using_curl($url) != 200){
            return 0;
        }
        return 1;
    }

    function getHttpResponseCode_using_curl($url, $followredirects = true){
        if(! $url || ! is_string($url)){
            return false;
        }
        $ch = @curl_init($url);
        if($ch === false){
            return false;
        }
        @curl_setopt($ch, CURLOPT_HEADER         ,true);
        @curl_setopt($ch, CURLOPT_NOBODY         ,true);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER ,true);
        if($followredirects){
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,true);
            @curl_setopt($ch, CURLOPT_MAXREDIRS      ,10);
        }else{
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,false);
        }
        @curl_exec($ch);
        if(@curl_errno($ch)){
            @curl_close($ch);
            return false;
        }
        $code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        @curl_close($ch);
        return $code;
    }
}
