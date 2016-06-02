<?php

require('../vendor/autoload.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;

$app = new Silex\Application();

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));

$app->before(function (Request $request) use($bot) {
    // TODO validation
});

// Webhookを設定するときに必要
$app->get('/callback', function (Request $request) use ($app) {
    $response = "";
    // if ($request->query->get('hub_verify_token') === getenv('kawamurakazushi')) {
    // Facebookで設定するToken
    if ($request->query->get('hub_verify_token') === 'kawamurakazushi') {
        $response = $request->query->get('hub_challenge');
    }

    return $response;
});

$app->post('/callback', function (Request $request) use ($app) {
    // ここで色々編集する
    $body = json_decode($request->getContent(), true);
    $client = new Client(['base_uri' => 'https://graph.facebook.com/v2.6/']);

    foreach ($body['entry'] as $obj) {
        $app['monolog']->addInfo(sprintf('obj: %s', json_encode($obj)));

        foreach ($obj['messaging'] as $m) {

            $app['monolog']->addInfo(sprintf('messaging: %s', json_encode($m)));
            $from = $m['sender']['id'];
            $text = $m['message']['text'];


            if ($text) {
                $path = sprintf('me/messages?access_token=%s', 'EAAG9bUdzn2IBANNOL7Oy1bpnZCVbTRffsAONfplAlfzcK2iLZCVvopgX9oGyI5aZCERC8XBUsz8FDZBvfPUOEN0bDd0DNxwKYM8xus494feQcqLq5IOs5DrQZArQF4b0kfrZBgOTgZBMp2KzMRFr7k2wqF050usamy64zccTu0qbAZDZD');

                if ($text == 'weather') {
                    $url = 'http://weather.livedoor.com/forecast/webservice/json/v1?city=400040';
                    $weather = json_decode(file_get_contents($url), true);
                     
                    $json = [
                        'recipient' => [
                            'id' => $from, 
                        ],
                        'message' => [
                            'text' => $weather['pinpointLocations']['link'],
                        ],
                    ];
                    $client->request('post', $path, ['json' => $json]);
                } else {
                    $json = [
                        'recipient' => [
                            'id' => $from, 
                        ],
                        'message' => [
                            'text' => sprintf('%sふぁああ', $text), 
                        ],
                    ];
                  $client->request('post', $path, ['json' => $json]);
                }

            }
        }

    }

    return 0;
});

// Method: POST, PUT, GET etc
// Data: array("param" => "value") ==> index.php?param=value

function callAPI($method, $url, $data = false) {
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}

$app->run();
