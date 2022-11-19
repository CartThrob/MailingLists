<?php

namespace CartThrob\MailingLists\Hooks;

use CartThrob\Hooks\Hook;

class CartthrobOnAuthorizeHook extends Hook
{
    /**
     * @param $valid_addons
     * @return void
     */
    public function process($valid_addons)
    {
        $custom_data = ee()->cartthrob->cart->order('custom_data');
        $last_name = ee()->cartthrob->cart->order('last_name');
        $first_name = ee()->cartthrob->cart->order('first_name');
        $email_address = ee()->cartthrob->cart->order('email_address');

        if (isset($custom_data['campaign_monitor'])) {
            if (!is_array($custom_data['campaign_monitor'])) {
                $this->contact_campaign_monitor($email_address, $first_name . ' ' . $last_name, $custom_data['campaign_monitor'], (string)$this->settings['api_key']);
            } else {
                foreach ($custom_data['campaign_monitor'] as $list) {
                    $this->contact_campaign_monitor($email_address, $first_name . ' ' . $last_name, $list, (string)$this->settings['api_key']);
                }
            }
        }

        if (isset($custom_data['mailchimp'])) {
            if (!is_array($custom_data['mailchimp'])) {
                $this->mailchimp_subscribe($email_address, $first_name . ' ' . $last_name, $custom_data['mailchimp'], (string)$this->settings['mailchimp_api_key']);
            } else {
                foreach ($custom_data['mailchimp'] as $list) {
                    $this->mailchimp_subscribe($email_address, $first_name . ' ' . $last_name, $list, (string)$this->settings['mailchimp_api_key']);
                }
            }
        }
    }

    /**
     * @param $email
     * @param $name
     * @param $list_id
     * @param $api_key
     * @return void
     */
    public function mailchimp_subscribe($email, $name, $list_id, $api_key)
    {
        require_once PATH_THIRD . $this->module_name . '/libraries/MCAPI.class.php';
        $mailchimp_api = new MCAPI($api_key);

        if ($this->settings['mailchimp_require_double_opt_in'] == 'no') {
            $double_opt_in = false;
        } else {
            $double_opt_in = true;
        }

        $result = $mailchimp_api->listSubscribe($list_id, $email, ['FNAME' => ee()->cartthrob->cart->order('first_name'), 'LNAME' => ee()->cartthrob->cart->order('last_name')], $email_type = 'html', $double_opt_in);
    }

    /**
     * @param $email
     * @param $name
     * @param $list_id
     * @param $api_key
     * @return bool
     */
    public function contact_campaign_monitor($email, $name, $list_id, $api_key)
    {
        $params = [
            'ListID' => $list_id,
            'Email' => $email,
            'Name' => $name,
        ];
        $url = 'http://api.createsend.com/api/api.asmx';

        $data = "ApiKey={$api_key}";
        $url .= '/Subscriber.Add';

        foreach ($params  as $key => $value) {
            $data .= '&' . $key . '=' . rawurlencode(utf8_encode($value));
        }

        $url .= '?' . $data;
        $headers['header'][] = 'User-Agent: CT1';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers['header']);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            return true;
        } else {
            return false;
        }
    }
}
