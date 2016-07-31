<?php

namespace paybook;

class Transaction extends Paybook
{
    public function __construct($transaction_array)
    {
        $this->id_transaction = array_key_exists('id_transaction', $transaction_array) ? $transaction_array['id_transaction'] : '';
        $this->id_user = array_key_exists('id_user', $transaction_array) ? $transaction_array['id_user'] : '';
        $this->id_external = array_key_exists('id_external', $transaction_array) ? $transaction_array['id_external'] : '';
        $this->id_site = array_key_exists('id_site', $transaction_array) ? $transaction_array['id_site'] : '';
        $this->id_site_organization = array_key_exists('id_site_organization', $transaction_array) ? $transaction_array['id_site_organization'] : '';
        $this->id_site_organization_type = array_key_exists('id_site_organization_type', $transaction_array) ? $transaction_array['id_site_organization_type'] : '';
        $this->id_account = array_key_exists('id_account', $transaction_array) ? $transaction_array['id_account'] : '';
        $this->id_account_type = array_key_exists('id_account_type', $transaction_array) ? $transaction_array['id_account_type'] : 0;
        $this->is_disable = array_key_exists('is_disable', $transaction_array) ? $transaction_array['is_disable'] : '';
        $this->description = array_key_exists('description', $transaction_array) ? $transaction_array['description'] : '';
        $this->amount = array_key_exists('amount', $transaction_array) ? $transaction_array['amount'] : '';
        $this->dt_transaction = array_key_exists('dt_transaction', $transaction_array) ? $transaction_array['dt_transaction'] : '';
        $this->dt_refresh = array_key_exists('dt_refresh', $transaction_array) ? $transaction_array['dt_refresh'] : '';
    }//End of __construct

    public static function get($session = null, $id_user = null, $options = [])
    {
        self::log('');
        self::log('Transaction->get');
        $params = $options;
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        $transaction_arrays = self::call($endpoint = 'transactions', $method = 'get', $params = $params);
        $transactions = [];
        foreach ($transaction_arrays as $index => $transaction_array) {
            $transaction = new self($transaction_array);
            array_push($transactions, $transaction);
        }//End of foreach
        return $transactions;
    }//End of get

    public static function get_count($session = null, $id_user = null, $options = [])
    {
        self::log('');
        self::log('Transaction->get_count');
        $params = $options;
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        $transactions_count = self::call($endpoint = 'transactions/count', $method = 'get', $params = $params);

        return $transactions_count['count'];
    }//End of get_count

    public function get_array()
    {
        return [
            'id_transaction' => $this->id_transaction,
            'id_user' => $this->id_user,
            'id_external' => $this->id_external,
            'id_site' => $this->id_site,
            'id_site_organization' => $this->id_site_organization,
            'id_site_organization_type' => $this->id_site_organization_type,
            'id_account' => $this->id_account,
            'id_account_type' => $this->id_account_type,
            'is_disable' => $this->is_disable,
            'description' => $this->description,
            'amount' => $this->amount,
            'dt_transaction' => $this->dt_transaction,
            'dt_refresh' => $this->dt_refresh,
        ];//End of return 
    }//End of get_array
}//End of Transaction class
