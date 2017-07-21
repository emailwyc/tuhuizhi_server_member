<?php

namespace Pingpp;

class Balance extends AppBase
{
    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return array An array of transfer.
     */
    public static function transfer($params = null, $options = null)
    {
        $url = static::appBaseUrl().'/transfers';
        return static::_directRequest('post', $url, $params, $options);
    }

    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return array An array of receipts.
     */
    public static function createReceipts($params = null, $options = null)
    {
        $url = static::appBaseUrl().'/receipts';
        return static::_directRequest('post', $url, $params, $options);
    }

}
