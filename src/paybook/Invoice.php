<?php

namespace paybook;

class Invoice extends Paybook
{
    public function __construct($session = null, $id_user = null, $taxpayer = null, $invoice_data = null, $invoice_xml = null, $id_provider)
    {
        if ($taxpayer && ($invoice_data || $invoice_xml) && $id_provider) {
            if ($id_user != null) {
                $data = [
                    'api_key' => self::$api_key,
                    'id_user' => $id_user,
                ];//End of $data
            } else {
                $data = [
                    'token' => $session->token,
                ];//End of $data
            }//End of if
            $data['taxpayer'] = $taxpayer;
            $data['id_provider'] = $id_provider;
            if ($invoice_data) {
                $data['invoice_data'] = $invoice_data;
            } elseif ($invoice_xml) {
                $data['invoice_xml'] = $invoice_xml;
            }//End of if
            $invoice_array = self::call($endpoint = '/invoicing/mx/invoices', $method = 'post', $data = $data);
        }
        $this->taxpayer = array_key_exists('taxpayer', $invoice_array) ? $invoice_array['taxpayer'] : null;
        $this->errors = array_key_exists('errors', $invoice_array) ? $invoice_array['errors'] : [];
        $this->xml = array_key_exists('xml', $invoice_array) ? $invoice_array['xml'] : null;
        $this->uuid = array_key_exists('uuid', $invoice_array) ? $invoice_array['uuid'] : null;
        $this->warnings = array_key_exists('warnings', $invoice_array) ? $invoice_array['warnings'] : [];
        $this->cadena_original = array_key_exists('cadenaOriginal', $invoice_array) ? $invoice_array['cadenaOriginal'] : null;
        $this->qr_string = array_key_exists('QRString', $invoice_array) ? $invoice_array['QRString'] : null;
    }//End of __construct
}//End of Credentials class
