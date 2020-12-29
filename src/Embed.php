<?php
namespace Metabase;
use Lcobucci\JWT\Token;

/**
 * Convenience class to embed Metabase dashboards and questions
 */
class Embed
{
    private $url;
    private $key;

    public $border;
    public $title;
    public $theme;

    public $width;
    public $height;

    /**
     * Default constructor
     *
     * @param $url    string base url for the Metabase installation
     * @param $key    int secret Metabase key
     * @param $title  bool show dashboard/question title (default = false)
     * @param $width  string set css width of dashboard/question
     * @param $height string set css height of dashboard/question
     * @param $border bool show dashboard/question border (default = false)
     */
    public function __construct($url, $key, $title = false, $width = '100%', $height = '800', $border = true)
    {
        $this->url = $url;
        $this->key = $key;
        $this->border = $border;
        $this->title = $title;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Get the embed URL for a Metabase question
     *
     * @param $questionId int id of the question to embed
     * @param $params array an associate array with variables to be passed to the question
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
     * @param $dashboardId int the id of the dashboard to embed
     * @param $params array an associate array with variables to be passed to the dashboard
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
     * @param $resource array resource to encode (question or dashboard)
     * @param $params array an associate array with variables to be passed to the dashboard
     *
     * @return Token
     */
    private function encode($resource, $params)
    {
        $jwt = new \Lcobucci\JWT\Builder();
        $jwt->set('resource', $resource);
        if (empty($params)) {
            $jwt->set('params', (object)[]);
        } else {
            $jwt->set('params', $params);
        }
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
     * @param $questionId int the id of the question to embed
     * @param $params array an associate array with variables to be passed to the question
     *
     * @return string Code to embed
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
     * @param $dashboardId int the id of the dashboard to embed
     * @param $params array an associate array with variables to be passed to the dashboard
     *
     * @return string Code to embed
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
     * @param $iframeUrl string the URL to create an iframe for
     *
     * @return string Code to embed
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
