<?php
namespace Metabase;

/**
 * Convenience class to embed Metabase dashboards and questions
 */
class Embed
{
    private $url;
    private $key;

    public $border = true;
    public $title = false;
    public $theme;

    public $width = '100%';
    public $height = '800';

    /**
     * Default constructor
     *
     * @param $url The base url for the Metabase installation
     * @param $key The secret Metabase key
     */
    public function __construct($url, $key)
    {
        $this->url = $url;
        $this->key = $key;
    }

    /**
     * Get the embed URL for a Metabase question
     *
     * @param $questionId   The id of the question to embed
     * @param $params       An associate array with variables to be passed to the question
     *
     * @return Embed URL
     */
    public function questionUrl($questionId, $params = [])
    {
        return $this->url('question', $questionId, $params);
    }

    /**
     * Get the embed URL for a Metabase dashboard
     *
     * @param $dashboardId  The id of the dashboard to embed
     * @param $params       An associate array with variables to be passed to the dashboard
     *
     * @return Embed URL
     */
    public function dashboardUrl($dashboardId, $params = [])
    {
        return $this->url('dashboard', $dashboardId, $params);
    }
    
    /**
     * Use JWT to encode tokens
     *
     * @param $resouce  Resource to encode (question or dashboard)
     * @param $params   An associate array with variables to be passed to the dashboard
     *
     * @return Token
     */
    private function encode($resource, $params)
    {
        $jwt = new \Lcobucci\JWT\Builder();
        $jwt->set('resource', $resource);
        $jwt->set('params', $params);
        $jwt->sign(new \Lcobucci\JWT\Signer\Hmac\Sha256(), $this->key);

        return $jwt->getToken();
    }
    
    protected function url($resource, $id, $params)
    {
        // Generate auth token, using JWT
        $token = $this->encode([$resource => $id], $params);
        
        // Generate embed URL
        $url = $this->url . '/embed/' . $resource . '/' . $token . '#';

        // Should border be included
        if ($this->border) {
            $url .= 'bordered=true&';
        } else {
            $url .= 'bordered=false&';
        }
        
        // Should title be included
        if ($this->title) {
            $url .= 'titled=true&';
        } else {
            $url .= 'titled=false&';
        }
        
        // Set selected theme (if any)
        if (!empty($this->theme)) {
            $url .= 'theme=' . $this->theme . '&';
        }
        
        // Remove trailing &
        $url = rtrim($url, '&');
        
        return $url;
    }
    
    /**
     * Generate the HTML to embed a question iframe with a given question id.
     * It assumes no iframe border. Size can be manipulated via
     * class $width/$height
     *
     * @param $questionId   The id of the question to embed
     * @param $params       An associate array with variables to be passed to the question
     *
     * @return HTML Code to embed
     */
    public function questionIFrame($questionId, $params = [])
    {
        $url = $this->questionUrl($questionId, $params);
        return $this->iframe($url);
    }
    
    /**
     * Generate the HTML to embed a dashboard iframe with a given dashboard id.
     * It assumes no iframe border. Size can be manipulated via
     * class $width/$height
     *
     * @param $dashboardId  The id of the dashboard to embed
     * @param $params       An associate array with variables to be passed to the dashboard
     *
     * @return HTML Code to embed
     */
    public function dashboardIFrame($dashboardId, $params = [])
    {
        $url = $this->dashboardUrl($dashboardId, $params);
        return $this->iframe($url);
    }

    /**
     * Generate the HTML to embed an iframe with a given URL.
     * It assumes no iframe border. Size can be manipulated via
     * class $width/$height
     *
     * @param $iframeUrl    The URL to create an iframe for
     *
     * @return HTML Code to embed
     */
    protected function iframe($iframeUrl)
    {
        return '<iframe
            src="' . $iframeUrl . '"
            frameborder="0"
            width="' . $this->width . '"
            height="' . $this->height . '"
            allowtransparency></iframe>';
    }
}
