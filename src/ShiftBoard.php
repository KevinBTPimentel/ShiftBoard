<?php

namespace Library\ShiftBoard;

use GuzzleHttp\Client as GuzzleClient;

class ShiftBoard
{
    /** @var string */
    private $accessKey;

    /** @var string */
    private $signatureKey;

    /** @var GuzzleHttp\Client as GuzzleClient */
    protected $client;

    /**
     *
     * Requires accessKey and signatureKey to
     * initialize ShiftBoard API Wrapper
     *
     * @param string accessKey ShiftBoard provided access key
     * @param string signatureKey ShiftBoard provided signature key
     * @param string url optional base URI
     */
    public function __construct(string $accessKey, string $signatureKey, string $url = null)
    {
        $this->accessKey = $accessKey;
        $this->signatureKey = $signatureKey;

        $this->client = new GuzzleClient([
            'base_uri' => $url ?? 'https://api.shiftdata.com/servola/api/'
        ]);
    }

    /**
     *
     * Using shiftdata https://www.shiftdata.com/#account-object
     * Select a method under Objects and then you may pass any parameters
     * specified in the documentation
     *
     * @param string method ShiftBoard object
     * @param array params accepts valid parameters based on method choosen
     */
    public function call(string $method, array $params = [])
    {
        $json_params = $this->getJsonEncodedParams($params);
        $uri64_params = $this->getBase64UrlEncodedParams($json_params);

        $signature = $this->getSignature(
            "method" . $method . "params" . $json_params
        );

        try {
            $response = $this->client->get("api.cgi?&access_key_id={$this->accessKey}&jsonrpc=2.0&id=1&method={$method}&params={$uri64_params}&signature={$signature}");

            return json_decode(
                $response->getBody()
            );
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function getJsonEncodedParams($params = []) : string
    {
        return empty($params) ? '{}' : json_encode($params);
    }

    public function getBase64UrlEncodedParams($params = []) : string
    {
        return urlencode(base64_encode($params));
    }

    public function getSignature($data) : string
    {
        return base64_encode(
            hash_hmac('sha1', $data, $this->signatureKey, true)
        );
    }
}
