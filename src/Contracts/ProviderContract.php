<?php

namespace gleisonnanet\CepGratis\Contracts;

interface ProviderContract
{
    /**
     * @return Address
     */
    public function getAddress($cep, HttpClientContract $client);
}
