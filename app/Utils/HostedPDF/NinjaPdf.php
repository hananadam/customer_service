<?php
/**
 * NinjaPMS (https://ninjapms.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2021. NinjaPMS LLC (https://ninjapms.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Utils\HostedPDF;

use GuzzleHttp\RequestOptions;

class NinjaPdf
{

    private $url = 'https://pdf.ninjapms.com/api/';

    public function build($html)
    {

        $client =  new \GuzzleHttp\Client(['headers' =>
            [
            'X-Ninja-Token' => 'test_token_for_now',
            ]
        ]);

        $response = $client->post($this->url,[
            RequestOptions::JSON => ['html' => $html]
        ]);

        return $response->getBody();
    }

}
