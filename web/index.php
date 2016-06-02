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
    if ($request->query->get('hub_verify_token') === 'kawamurakazushi') {
        $response = $request->query->get('hub_challenge');
    }

    return $response;
});

$app->post('/callback', function (Request $request) use ($app) {
    // Let's hack from here!
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
                $json = [
                    'recipient' => [
                        'id' => $from, 
                    ],
                    'message' => [
                        'text' => sprintf('%sふぁああ', $text), 
                    ],
                ];
                $client->request('POST', $path, ['json' => $json]);
            }
        }

    }

    return 0;
});

$app->run();
