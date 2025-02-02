<?php

/***************************************************************************
 *
 * Copyright (c) 2013 xiaomi.com, Inc. All Rights Reserved
 *
 **************************************************************************/

namespace xiaomi\httpclient;

use think\Exception;
use xiaomi\utils\XMUtil;

/**
 * @version 1.0
 * @author passport.xiaom.com
 */
class XMApiClient extends XMHttpClient
{
    protected $apiUrl;
    protected $clientId;
    protected $accessToken;
    protected $defaultEncode = 'UTF-8';

    public function XMApiClient($clientId, $accessToken)
    {
        global $API_URL;
        $this->clientId = $clientId;
        $this->accessToken = $accessToken;
        $this->apiUrl = trim($API_URL, ' /');
    }

    public function getApiHost()
    {
        $list = parse_url($this->apiUrl);
        return $list['host'];
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getDefaultEncode()
    {
        return $this->defaultEncode;
    }

    public function setDefaultEncode($defaultEncode)
    {
        $this->defaultEncode = strtoupper($defaultEncode);
    }

    /**
     * 访问api
     * @param string $path api url的path部分，例如：/user/profile(path 必须以/开头)
     * @param array $params 参数数组
     * @param bool $cookies
     * @param bool $header
     * @param string $method GET/POSt
     * @return array|bool|mixed
     * @throws Exception
     */
    public function callApi($path, $params = array(), $cookies = false, $header = false, $method = 'GET')
    {
        if (!isset($this->apiUrl)) {
            throw  new Exception(" API HOST  IS NULL.");
        }
        $url = $this->apiUrl . $path;
        $result = false;
        if (strtoupper($method) === 'GET') {
            $result = $this->get($url, $params, $cookies, $header);
        } else if (strtoupper($method) === 'POST') {
            $result = $this->post($url, $params, $cookies, $header);
        }
        if ($result && $result['succ']) {
            $result = json_decode($result['result'], true);
            return $result;
        }
        return $result;
    }

    /**
     * 访问api
     * @param string $path api url的path部分，例如：/user/profile(path 必须以/开头)
     * @param array $params 参数数组
     * @param string $macKey 下发的mac key
     * @param string $method GET/POSt
     * @return array|bool|mixed
     * @throws Exception
     */
    public function callApiSelfSign($path, $params = array(), $macKey, $method = 'GET')
    {
        // 获取nonce  随机数:分钟
        $nonce = XMUtil::getNonce();
        if (!array_search('clientId', $params)) {
            $params['clientId'] = $this->clientId;
        }
        if (!array_search('token', $params)) {
            $params['token'] = $this->accessToken;
        }
        $sign = XMUtil::buildSignature($nonce, $method, $this->getApiHost(), $path, $params, $macKey);
        $header = XMUtil::buildMacRequestHead($this->accessToken, $nonce, $sign);
        return $this->callApi($path, $params, false, $header, $method);
    }
}

?>