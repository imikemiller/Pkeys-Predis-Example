<?php

/**
 * Created by PhpStorm.
 * User: mike
 * Date: 29/07/17
 * Time: 19:32
 */
class PredisExample extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Predis\Client
     */
    public $redisClient;

    public $pkey;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        /*
         * Change this in phpunit.xml
         */
        $this->redisClient = new Predis\Client([
            'host'     =>       env('REDIS_HOST'),
            'port'     => (int) env('REDIS_PORT'),
            'database' => (int) env('REDIS_DATABASE'),
            'password' =>       env('REDIS_PASSWORD')
        ]);

        /*
         * Instantiate the Pkey client and load in the schema
         */
        $this->pkey = new \Pkeys\Pkey('schema.php');
    }

    /**
     *
     */
    public function test_using_predis_client()
    {

        /*
         * Request key from the schema
         */
        $countKey = $this->pkey->make('redis.users.count',[
            'status'=>'active',
            'day'=>\Carbon\Carbon::now()->toDateString()
        ]);

        /*
         * Do an incr operation on that key for examples sake
         */
        $this->redisClient->incr($countKey);
        $this->assertEquals(1,$this->redisClient->get($countKey));


        /*
         * Do an del operation on that key for examples sake.
         */
        $this->redisClient->del($countKey);
        $this->assertEquals(null,$this->redisClient->get($countKey));

        /*
         * Do some pub/sub for examples sake
         */
        /*
         * Request channel key from the schema
         */
        $pubsubChannel = $this->pkey->make('redis.user.messages',[
            'id'=>21
        ]);

        /*
         * The generated pubsub channel should be `user:21:messages`
         *
         * Use redis-cli in the terminal with following commands
         *
         * `publish user:21:messages "testing a message"`
         * `publish user:21:messages "quit_loop"`
         */
        fwrite(STDERR, 'Open redis-cli in your terminal and run:'.PHP_EOL);
        fwrite(STDERR, '`publish '.$pubsubChannel.' "testing this message"`'.PHP_EOL.PHP_EOL);
        fwrite(STDERR, 'Or to kill the loop: '.PHP_EOL);
        fwrite(STDERR, '`publish '.$pubsubChannel.' "quit_loop"`'.PHP_EOL
            .'---------------------------------------'.PHP_EOL.PHP_EOL);

        $pubsub = $this->redisClient->pubSubLoop();
        $pubsub->subscribe($pubsubChannel);
        foreach ($pubsub as $message) {
            switch ($message->kind) {
                case 'subscribe':
                    fwrite(STDERR,  "> Subscribed to {$message->channel}".PHP_EOL);
                    break;

                case 'message':
                    if ($message->payload == 'quit_loop') {
                        fwrite(STDERR, '> Aborting pubsub loop...'. PHP_EOL);
                        $pubsub->unsubscribe();
                    } else {
                        fwrite(STDERR,  "> Received the following message from {$message->channel}:".PHP_EOL);
                        fwrite(STDERR,  ">> \"{$message->payload}\"".PHP_EOL);
                    }
                    break;
            }
        }
    }
}