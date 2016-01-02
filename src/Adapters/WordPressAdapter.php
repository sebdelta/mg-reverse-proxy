<?php

namespace Mindgruve\ReverseProxy\Adapters;

use Mindgruve\ReverseProxy\AbstractCacheAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;
use Symfony\Component\HttpKernel\HttpCache\SurrogateInterface;

class WordPressAdapter extends AbstractCacheAdapter
{
    /**
     * @var StoreInterface
     */
    protected $store;

    /**
     * @var SurrogateInterface
     */
    protected $surrogate;

    /**
     * @var array
     */
    protected $httpCacheOptions;

    /**
     * @var string
     */
    protected $bootstrapFile;

    /**
     * @var int
     */
    protected $defaultMaxAge;

    public function __construct($bootstrapFile, $defaultMaxAge, StoreInterface $store, array $httpCacheOptions = array(), SurrogateInterface $surrogate = null)
    {
        $this->bootstrapFile = $bootstrapFile;
        $this->defaultMaxAge = $defaultMaxAge;
        $this->store = $store;
        $this->surrogate = $surrogate;
        $this->httpCacheOptions = $httpCacheOptions;
    }

    /**
     * @return boolean
     */
    public function isCachingEnabled()
    {
        return $this->isLoggedIn() == false;
    }

    /**
     * @return boolean
     */
    public function isShutdownFunctionEnabled()
    {
        return true;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function setCacheHeaders(Request $request, Response $response)
    {
        if (!$this->isLoggedIn()) {
            $response->setPublic();
        }
        return $response;
    }

    /**
     * @return bool
     */
    protected function isLoggedIn()
    {
        foreach ($_COOKIE as $key => $value) {
            if (preg_match('/^wordpress_logged_in_(.*)/', $key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return StoreInterface
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @return null | SurrogateInterface
     */
    public function getSurrogate()
    {
        return $this->surrogate;
    }

    /**
     * @return array
     */
    public function getHttpCacheOptions()
    {
        return $this->httpCacheOptions;
    }

    public function bootstrap()
    {
        ob_start();
        include_once($this->bootstrapFile);
    }

    public function getRawContent()
    {
        $rawContent = ob_get_contents();
        ob_end_clean();

        return $rawContent;
    }

    /**
     * @return int
     */
    public function getDefaultMaxAge()
    {
        return $this->defaultMaxAge;
    }

    /**
     * @return string
     */
    public function getDefaultResponseType()
    {
        return self::RESPONSE_TYPE_PRIVATE;
    }

}