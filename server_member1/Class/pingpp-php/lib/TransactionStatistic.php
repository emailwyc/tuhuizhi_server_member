<?php

namespace Pingpp;

class TransactionStatistic extends AppBase
{
    /**
     * This is a special case because the card info endpoint has an
     *    underscore in it. The parent `className` function strips underscores.
     *
     * @return string The name of the class.
     */
    public static function className()
    {
        return 'transaction_statistic';
    }


    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return array An array of TransactionStatistic.
     */
    public static function all($params = null, $options = null)
    {
        return self::_all($params, $options);
    }
}
