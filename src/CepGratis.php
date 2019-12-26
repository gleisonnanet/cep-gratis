<?php

namespace gleisonnanet\CepGratis;

use gleisonnanet\CepGratis\Clients\CurlHttpClient;
use gleisonnanet\CepGratis\Contracts\HttpClientContract;
use gleisonnanet\CepGratis\Contracts\ProviderContract;
use gleisonnanet\CepGratis\Exceptions\CepGratisInvalidParameterException;
use gleisonnanet\CepGratis\Exceptions\CepGratisTimeoutException;
use gleisonnanet\CepGratis\Providers\CorreiosProvider;
use gleisonnanet\CepGratis\Providers\ViaCepProvider;

/**
 * Class to query CEP.
 */
class CepGratis
{
    /**
     * @var HttpClientContract
     */
    private $client;

    /**
     * @var ProviderContract[]
     */
    private $providers = [];

    /**
     * @var int
     */
    private $timeout = 5;

    /**
     * CepGratis constructor.
     */
    public function __construct()
    {
        $this->client = new CurlHttpClient();
    }

    /**
     * Search CEP on all providers.
     *
     * @param string $cep CEP
     *
     * @return Address
     */
    public static function search($cep)
    {
        $cepGratis = new self();
        $cepGratis->addProvider(new ViaCepProvider());
        $cepGratis->addProvider(new CorreiosProvider());

        $address = $cepGratis->resolve($cep);

        return $address;
    }

    /**
     * Performs provider CEP search.
     *
     * @param string $cep CEP
     *
     * @return Address
     */
    public function resolve($cep)
    {
        if (strlen($cep) != 8 && filter_var($cep, FILTER_VALIDATE_INT) === false) {
            throw new CepGratisInvalidParameterException('CEP is invalid');
        }

        if (count($this->providers) == 0) {
            throw new CepGratisInvalidParameterException('No providers were informed');
        }

        /*
         * Execute
         */
        $time = time();

        do {
            foreach ($this->providers as $provider) {
                $address = $provider->getAddress($cep, $this->client);
            }

            if ((time() - $time) >= $this->timeout) {
                throw new CepGratisTimeoutException("Maximum execution time of $this->timeout seconds exceeded in PHP");
            }
        } while (is_null($address));

        /*
         * Return
         */
        return $address;
    }

    /**
     * Set client http.
     *
     * @param HttpClientContract $client
     */
    public function setClient(HttpClientContract $client)
    {
        $this->client = $client;
    }

    /**
     * Set array providers.
     *
     * @param HttpClientContract $client
     */
    public function addProvider(ProviderContract $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * Set timeout.
     *
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }
}
