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
});

// Webhookを設定するときに必要
$app->get('/callback', function (Request $request) use ($app) {
    $response = "";
    // Facebookで設定するToken
    if ($request->query->get('hub_verify_token') === getenv('FACEBOOK_PAGE_VERIFY_TOKEN')) {
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
                $path = sprintf('me/messages?access_token=%s', getenv('FACEBOOK_PAGE_ACCESS_TOKEN'));

                $message = '';
                // TODO: 場所を指定したい
                if ($text == '天気') {
                    // 現状は東京都の天気
                    $url = 'http://weather.livedoor.com/forecast/webservice/json/v1?city=130010';
                    $weather = json_decode(file_get_contents($url), true);

                    $message = "今日の天気は" . $weather['forecasts'][0]['telop'] ."!\n";
                    $message .= "明日の天気は" .$weather['forecasts'][1]['telop'];
                } else {
                    $message = sprintf('%s?', $text); 
                }

                $json = [
                    'recipient' => [
                        'id' => $from, 
                    ],
                    'message' => [
                        'text' => $message,
                    ],
                ];
                $client->request('post', $path, ['json' => $json]);
        }
        }
    }
    return 0;
});

$app->run();
