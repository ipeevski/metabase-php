<?php

namespace Metabase;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Hmac\Sha256;
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

    private $jwtConfig;

    /**
     * Default constructor
     *
     * @param string $url    Base url for the Metabase installation
     * @param string $key    Secret Metabase key
     * @param bool   $title  Show dashboard/question title (default = false)
     * @param string $width  Set css width of dashboard/question (default = 100%)
     * @param string $height Set css height of dashboard/question (default = 800)
     * @param bool   $border Show dashboard/question border (default = true)
     */
    public function __construct($url, $key, $title = false, $width = '100%', $height = '800', $border = true)
    {
        $this->url = $url;
        $this->key = $key;
        $this->border = $border;
        $this->title = $title;
        $this->width = $width;
        $this->height = $height;

        $this->jwtConfig = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($this->key));
    }

    /**
     * Get the embed URL for a Metabase question
     *
     * @param int   $questionId The id of the question to embed
     * @param array $params     An associate array with variables to be passed to the question
     *
     * @return string Embed URL
     */
    public function questionUrl($questionId, $params = [])
    {
        return $this->url('question', $questionId, $params);
    }

    /**
     * Get the embed URL for a Metabase dashboard
     *
     * @param int   $dashboardId The id of the dashboard to embed
     * @param array $params      An associate array with variables to be passed to the dashboard
     *
     * @return string Embed URL
     */
    public function dashboardUrl($dashboardId, $params = [])
    {
        return $this->url('dashboard', $dashboardId, $params);
    }

    /**
     * Use JWT to encode tokens
     *
     * @param array $resource Resource to encode (question or dashboard)
     * @param array $params   An associate array with variables to be passed to the dashboard
     *
     * @return string Token
     */
    private function encode($resource, $params)
    {
        $jwt = $this->jwtConfig->builder();
        $jwt->withClaim('resource', $resource);
        if (empty($params)) {
            $jwt->withClaim('params', (object)[]);
        } else {
            $jwt->withClaim('params', $params);
        }

        return $jwt->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey());
    }

    protected function url($resource, $id, $params)
    {
        // Generate auth token, using JWT
        $token = $this->encode([$resource => $id], $params);

        // Generate embed URL
        $url = $this->url . '/embed/' . $resource . '/' . $token->toString() . '#';

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
     * @param int   $questionId The id of the question to embed
     * @param array $params     An associate array with variables to be passed to the question
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
     * @param int   $dashboardId The id of the dashboard to embed
     * @param array $params      An associate array with variables to be passed to the dashboard
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
     * @param string $iframeUrl The URL to create an iframe for
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
