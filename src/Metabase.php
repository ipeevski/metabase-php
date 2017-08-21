<?php
namespace Metabase;

class Metabase
{
    private $url;
    private $key;

    public function __construct($url, $key)
    {
        $this->url = $url;
        $this->key = $key;
    }

    public function questionUrl($questionId, $params = [], $options = ['bordered'])
    {
        return $this->url('question', $questionId, $params, $options);
    }

    public function dashboardUrl($dashboardId, $params = [], $options = ['bordered'])
    {
        return $this->url('dashboard', $dashboardId, $params, $options);
    }
    
    private function encode($resource, $params)
    {
        $signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();
        $jwt = new \Lcobucci\JWT\Builder();
        $jwt->set('resource', $resource);
        $jwt->set('params', $params);
        $jwt->sign($signer, $this->key);
        return $jwt->getToken();
    }
    
    protected function url($resource, $id, $params, $options = ['bordered'])
    {
        $token = $this->encode([$resource => $id], $params);
        
        $url = $this->url . '/embed/' . $resource . '/' . $token . '#';
        if (!empty($options['bordered'])) {
            $url .= 'bordered=true&';
        }
        
        if (!empty($options['titled'])) {
            $url .= 'titled=true&';
        }
        
        if (!empty($options['theme'])) {
            $url .= 'theme=' . $options['theme'] . '&';
        }
        
        // Remove trailing &
        $url = rtrim($url, '&');
        
        return $url;
    }
    
    public function questionIFrame($questionId, $params = [], $height = '800', $width = '100%')
    {
        $url = $this->questionUrl($questionId, $params);
        return $this->iframe($url, $height, $width);
    }
    
    public function dashboardIFrame($dashboardId, $params = [], $height = '800', $width = '100%')
    {
        $url = $this->dashboardUrl($dashboardId, $params);
        return $this->iframe($url, $height, $width);
    }

    protected function iframe($iframeUrl, $height = '800', $width = '100%')
    {
        return '<iframe
            src="' . $iframeUrl . '"
            frameborder="0"
            width="' . $width . '"
            height="' . $height . '"
            allowtransparency
            ></iframe>';
    }
}
