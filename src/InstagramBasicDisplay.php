<?php
namespace Zolisz\InstagramBasicDisplay;

class InstagramBasicDisplay{
    private $_appId = INSTAGRAM_APP_ID;
    private $_appSecret = INSTAGRAM_APP_SECRET;
    private $_redirectUrl = INSTAGRAM_APP_REDIRECT_URI;
    private $_getCode;
    private $_apiBaseUrl = 'https://api.instagram.com/';
    private $_graphBaseUrl = 'https://graph.instagram.com/';
    private $_userAccessToken;
    private $_userAccessTokenExpires;
    
    public $authorizationUrl;
    public $hasUserAccessToken = false;
    public $userId;
    
    public function firstSetup($params){
        $this->_getCode = $params['get_code'];
        
        $this->_setUserInstagramAccessToken($params);
            
        $this->_setAuthorizationUrl();
        $_SESSION['userAccessToken'] = $this->getUserAccessToken();
        $_SESSION['igUserId'] = $this->userId;
    }

    public function setup($params){        
        $this->_setUserInstagramAccessToken($params);
        
    }
    
    private function _setAuthorizationUrl(){
        $getVars = array(
            'app_id' => $this->_appId,
            'redirect_uri' => $this->_redirectUrl,
            'scope' => 'user_profile,user_media',
            'response_type' => 'code'
        );
        
        $this->authorizationUrl = $this->_apiBaseUrl . 'oauth/authorize?' . http_build_query($getVars);
    }

    public function setUserAccessToken($token){
        $this->_userAccessToken = $token;
    }

    public function setIgUserId($id){
        $this->userId = $id;
    }
    
    public function getUserAccessToken(){
        return $this->_userAccessToken;    
    }
    
    public function getUserAccessTokenExpires(){
        return $this->_userAccessTokenExpires;    
    }
    
    
    private function _setUserInstagramAccessToken($params){
        if($params['access_token']){
            $this->_userAccessToken = $params['access_token'];
            $this->hasUserAccessToken = true;
            $this->userId = $params['user_id'];
        }elseif($params['get_code']){
            $userAccessTokenResponse = $this->_getUserAccessToken();    
            $this->_userAccessToken = $userAccessTokenResponse['access_token'];
            $this->hasUserAccessToken = true;
            $this->userId = $userAccessTokenResponse['user_id'];
            
            $longLivedAccessTokenResponse = $this->_getLongLivedUserAccessToken();
            $this->_userAccessToken = $longLivedAccessTokenResponse['access_token'];
            $this->_userAccessTokenExpires = $longLivedAccessTokenResponse['expires_in'];
        }
    }
    
    private function _getUserAccessToken(){
        $params = array(
            'endpoint_url' => $this->_apiBaseUrl . 'oauth/access_token',
            'type' => 'POST',
            'url_params' => array(
                'app_id' => $this->_appId,
                'app_secret' => $this->_appSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->_redirectUrl,
                'code' => $this->_getCode
            )
        );
        $response = $this->_makeApiCall($params);
        return $response;
    }
    
    private function _getLongLivedUserAccessToken(){
        $params = array(
            'endpoint_url' => $this->_graphBaseUrl . 'access_token',
            'type' => 'GET',
            'url_params' => array(
                'client_secret' => $this->_appSecret,
                'grant_type' => 'ig_exchange_token'
            )
        );
        
        $response = $this->_makeApiCall($params);
        return $response;
    }
    
    
    public function getUser(){
        $params = array(
            'endpoint_url' => $this->_graphBaseUrl . 'me',
            'type' => 'GET',
            'url_params' => array(
                'fields' => 'id,username,media_count,account_type'
            )
        );
        
        $response = $this->_makeApiCall($params);
        return $response;
    }
    
    public function getUserMedia(){
        $params = array(
            'endpoint_url' => $this->_graphBaseUrl . $this->userId . '/media',
            'type' => 'GET',
            'url_params' => array(
                'fields' => 'id,caption,media_type,media_url'
            )
        );
        
        $response = $this->_makeApiCall($params);
        return $response;
    }
    
     public function getPaging($pagingEndpoint){
        $params = array(
            'endpoint_url' => $pagingEndpoint,
            'type' => 'GET',
            'url_params' => array(
                'paging' => true
            )
        );
        
        $response = $this->_makeApiCall($params);
        return $response;
    }
    
    public function getMedia($mediaId){
        $params = array(
            'endpoint_url' => $this->_graphBaseUrl . $mediaId,
            'type' => 'GET',
            'url_params' => array(
                'fields' => 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,username'
            )
        );
        
        $response = $this->_makeApiCall($params);
        return $response;
    }
    
    public function getMediaChildren($mediaId){
        $params = array(
            'endpoint_url' => $this->_graphBaseUrl . $mediaId . '/children',
            'type' => 'GET',
            'url_params' => array(
                'fields' => 'id,media_type,media_url,permalink,thumbnail_url,timestamp,username'
            )
        );
        
        $response = $this->_makeApiCall($params);
        return $response;
    }
    
    private function _makeApiCall($params){
        $ch = curl_init();
        
        $endpoint = $params['endpoint_url'];
        
        if('POST' == $params['type']){
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params['url_params']));
            curl_setopt($ch, CURLOPT_POST, 1);
        }elseif('GET' == $params['type'] && !isset($params['url_params']['paging']) || !$params['url_params']['paging'] ){
            $params['url_params']['access_token'] = $this->_userAccessToken;
            
            $endpoint .= '?' . http_build_query($params['url_params']);
        }
        
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        
        $response = curl_exec($ch);
        
        curl_close($ch);
        
        $responseArray = json_decode($response, true);
        
        if(isset($responseArray['error_type'])){
            var_dump($responseArray);
            die();
        }else{
            return $responseArray;
        }
    }
}

?>