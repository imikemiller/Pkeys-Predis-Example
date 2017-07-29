<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 29/07/17
 * Time: 19:38
 */

return [
    /*
     * Real world Redis schema examples.
     */
    'schema'=>[
        'redis'=>[
            'user'=>[
                /*
                 * Must have the param `id` passed in and must be numeric
                 */
                'messages'=>'user:{id|numeric}:messages'
            ],
            'users'=>[
                /*
                 * Must have params `status` and `day` passed in.
                 * `status` must be either "active","new" or "returning"
                 * `day` must be a valid date
                 */
                'count'=>'users:{status|in:active,new,returning}:{day|date}:count'
            ],
            'events'=>[
                /*
                 * Must have the param `type` and must be either "new" or "read"
                 */
                'messages'=>'message-event-{type|in:new,read}'
            ]
        ]
    ]
];