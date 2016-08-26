<?php
/**
 * Description of iDokladMini
 * Only auth and cURL layer for iDoklad api
 * @author honza
 */
class iDokladMini {
    private static $token;
    private static $url = 'https://app.idoklad.cz/developer/api/';
    private static $XApp;
    private static $XAppVersion;
    
    /**
     * 
     * @param string $XApp
     * @param string $XAppVersion
     * @param string $token
     */
    public static function setEnviroment($XApp, $XAppVersion, $token = null){
        self::$XApp = $XApp;
        self::$XAppVersion = $XAppVersion;
        if(!empty($token)){
            self::$token = $token;
        }
    }

    /**
     * 
     * @param string $email
     * @param string $password
     * @throws Exception
     */
    public static function getToken($email, $password){
        if(empty(self::$XApp) || empty(self::$XAppVersion)){
            throw new Exception('Zadejte nazev aplikace a jeji verzi');
        }
        
        $token = self::curlData('Agendas/GetSecureToken', array('username' => $email, 'password' => $password));
        $ret = json_decode($token, true);
        if(!empty($ret['Message'])){
            throw new Exception($ret['Message']);
        } else {
            self::$token = $token;
        }
    }
    
    /**
     * 
     * @param string $action
     * @param array $params
     * @param string $header
     * @return mixed
     */
    public static function curlData($action, $params = array(), $header = 'GET', $params_post = array()){
        $curl = curl_init();
        $curl_opt = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => self::$url.$action.'?'.  http_build_query($params),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-App: '.self::$XApp,
                'X-App-Version: '.self::$XAppVersion,
                (empty(self::$token) ? '' : 'SecureToken: '.self::$token)
            ),
            CURLOPT_SSL_VERIFYPEER => false
        );
        switch($header){
            case 'PUT':
                $curl_opt[CURLOPT_CUSTOMREQUEST] = "PUT";
                if(!empty($params_post)){
                    $curl_opt[CURLOPT_POSTFIELDS] = json_encode($params_post);
                } else {
                    $curl_opt[CURLOPT_POSTFIELDS] = http_build_query($params_post);
                }
                break;
            case 'POST':
                $curl_opt[CURLOPT_POSTFIELDS] = json_encode($params_post);
                break;
            case 'DELETE':
                $curl_opt[CURLOPT_CUSTOMREQUEST] = "DELETE";
                $curl_opt[CURLOPT_POSTFIELDS] = http_build_query($params_post);
                break;
        }
        curl_setopt_array($curl, $curl_opt);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }
    
    public static function getInvoiceIdByVs($vs){
        $ret = self::curlData('IssuedInvoices', array('query' => $vs));
        return $ret['Data'][0]['Id'];
    }
}
