<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mailjet 
{
    private $api_key = 'e7e709a810333fe6a51838f99f7192fc';
    private $api_key_secret = '96bcb35f30556c3a216293f91edc9e11';

    public function send($to_email, $to_name, $subject, $content)
    {
        $mj = new Client($this->api_key, $this->api_key_secret,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "jrmprod@gmail.com",
                        'Name' => "La Boutique Française"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 2105006,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        "content" => $content
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();

    }





}
