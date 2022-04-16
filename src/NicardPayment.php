<?php

namespace HopeOfIran\NicardPayment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;

class NicardPayment
{
    /**
     * @var object $config
     */
    public $config = [];

    /**
     * @var int
     */
    private $cashAmount = 0;

    /**
     * @var int
     */
    private $creditAmount = 0;

    /**
     * @var string $token
     */
    private $token = null;

    /**
     * @var string $token
     */
    private $url = null;

    /**
     * NicardPayment constructor.
     *
     * @param  array  $config
     *
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
        $this->config = empty($config) ? $this->loadDefaultConfig() : $config;
        $this->getToken();
    }

    /**
     * Set custom configs
     * we can use this method when we want to use dynamic configs
     *
     * @param $key
     * @param $value  |null
     *
     * @return $this
     */
    public function config($key, $value = null)
    {
        $configs = [];
        $key     = is_array($key) ? $key : [$key => $value];
        foreach ($key as $k => $v) {
            $configs[$k] = $v;
        }
        $this->config = array_merge((array) $this->config, $configs);
        return $this;
    }

    /**
     * @param  string  $url
     *
     * @return $this
     */
    public function callbackUrl(string $url)
    {
        $this->config('callbackUrl', $url);
        return $this;
    }

    /**
     * @param  string  $url
     *
     * @return $this
     */
    public function backUrl(string $url)
    {
        $this->config('backUrl', $url);
        return $this;
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function getTotalAmount()
    {
        if (($this->cashAmount + $this->creditAmount) == 0) {
            throw new \Exception("amount payment couldn't be zero");
        }
        return $this->cashAmount + $this->creditAmount;
    }

    /**
     * @param  int  $amount
     *
     * @return \HopeOfIran\NicardPayment\NicardPayment
     */
    public function cashAmount(int $amount)
    {
        $this->cashAmount = $amount;
        return $this;
    }

    /**
     * @param  int  $amount
     *
     * @return \HopeOfIran\NicardPayment\NicardPayment
     */
    public function creditAmount(int $amount)
    {
        $this->creditAmount = $amount;
        return $this;
    }

    /**
     * Retrieve default config.
     *
     * @return array
     */
    protected function loadDefaultConfig() : array
    {
        return require(static::getDefaultConfigPath());
    }

    /**
     * Retrieve Default config's path.
     *
     * @return string
     */
    public static function getDefaultConfigPath() : string
    {
        return __DIR__.'/config/nicardPayment.php';
    }

    /**
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function httpRequest()
    {
        return Http::baseUrl($this->config['baseUrl'])->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ]);
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function getToken()
    {
        $response    = $this->httpRequest()->post('cpg/login/get_token', [
            'username' => $this->config['username'],
            'password' => $this->config['password'],
        ]);
        $responseJson = $response->json();
        if (!isset($responseJson['merchant_token'])) {
            throw new \Exception($response->body());
        }
        $this->token = $response->json('merchant_token');
        return $this;
    }

    /**
     * @return int
     */
    private function getInstallmentsCountList()
    {
        return $this->config['installmentsCountList'];
    }

    /**
     * @param  array  $installmentsCountList
     *
     * @return \HopeOfIran\NicardPayment\NicardPayment
     */
    public function installmentsCountList(array $installmentsCountList = [])
    {
        $this->config('installmentsCountList', $installmentsCountList);
        return $this;
    }

    /**
     * @param  null  $finalizeCallback
     *
     * @return \Illuminate\Http\Client\Response
     * @throws \Exception
     */
    public function purchase($finalizeCallback = null)
    {
        $response = $this->httpRequest()->post('cpg/link_generator/cpg_link_generator', [
            "total_amount"            => $this->getTotalAmount(),
            "callback_url"            => $this->config['callbackUrl'],
            "back_url"                => $this->config['backUrl'],
            "installments_count_list" => $this->getInstallmentsCountList(),
            "cash_amount"             => $this->cashAmount,
            "credit_amount"           => $this->creditAmount,
            "smart_ipg_username"      => $this->config['username'],
            "smart_ipg_password"      => $this->config['password'],
            "smart_ipg_service_id"    => $this->config['serviceId'],
        ]);
        if ($response->failed()) {
            throw new \Exception($response->collect()->toJson());
            return false;
        }
        $this->url = $response->collect('data')['open_cpg_url'];
        if ($finalizeCallback) {
            return call_user_func($finalizeCallback, $this, $response, $response->json('data'));
        }
        return $response;
    }

    /**
     * @param  string|null  $url
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pay(string $url = null)
    {
        if ($url == null) {
            $url = $this->url;
        }
        return Redirect::to($url);
    }

    /**
     * @param  string  $uuidTransaction
     *
     * @return \Illuminate\Http\Client\Response
     */
    public function verify(string $uuidTransaction)
    {
        $response = $this->httpRequest()->post('transaction/after_transaction/transaction_inquiry', [
            'transaction_uuid' => $uuidTransaction,
        ]);
        return $response;
    }
}
