<?php

namespace paybook;

class Attachment extends Paybook
{
    public function __construct($attachment_array)
    {
        $this->id_attachment = array_key_exists('id_attachment', $attachment_array) ? $attachment_array['id_attachment'] : '';
        $this->id_account = array_key_exists('id_account', $attachment_array) ? $attachment_array['id_account'] : '';
        $this->id_user = array_key_exists('id_user', $attachment_array) ? $attachment_array['id_user'] : '';
        $this->id_external = array_key_exists('id_external', $attachment_array) ? $attachment_array['id_external'] : '';
        $this->id_attachment_type = array_key_exists('id_attachment_type', $attachment_array) ? $attachment_array['id_attachment_type'] : '';
        $this->id_transaction = array_key_exists('id_transaction', $attachment_array) ? $attachment_array['id_transaction'] : '';
        $this->is_valid = array_key_exists('is_valid', $attachment_array) ? $attachment_array['is_valid'] : null;
        $this->mime = array_key_exists('mime', $attachment_array) ? $attachment_array['mime'] : '';
        $this->file = array_key_exists('file', $attachment_array) ? $attachment_array['file'] : '';
        $this->extra = array_key_exists('extra', $attachment_array) ? $attachment_array['extra'] : '';
        $this->url = array_key_exists('url', $attachment_array) ? $attachment_array['url'] : '';
        $this->dt_refresh = array_key_exists('dt_refresh', $attachment_array) ? $attachment_array['dt_refresh'] : 0;
    }//End of __construct

    public static function get($session = null, $id_user = null, $id_attachment = null, $extra = null, $options = [])
    {
        self::log('');
        self::log('Attachment->get');
        $params = $options;
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        if ($id_attachment != null) {
            if ($extra != null) {
                $id_attachment = 'attachments/'.$id_attachment.'/extra';
            } else {
                $id_attachment = 'attachments/'.$id_attachment;
            }//End of if
            $attachment_data = self::call($endpoint = $id_attachment, $method = 'get', $params = $params);

            return $attachment_data;
        } else {
            $attachment_arrays = self::call($endpoint = 'attachments', $method = 'get', $params = $params);
        }//End of if
        $attachments = [];
        foreach ($attachment_arrays as $index => $attachment_array) {
            $attachment = new self($attachment_array);
            array_push($attachments, $attachment);
        }//End of foreach
        return $attachments;
    }//End of get

    public static function get_count($session = null, $id_user = null, $options = [])
    {
        self::log('');
        self::log('Attachment->get_count');
        $params = $options;
        if ($id_user != null) {
            $params['api_key'] = self::$api_key;
            $params['id_user'] = $id_user;
        } else {
            $params['token'] = $session->token;
        }//End of if
        $attachment_count = self::call($endpoint = 'attachments/count', $method = 'get', $params = $params);

        return $attachment_count['count'];
    }//End of get_count
}//End of Attachment class
