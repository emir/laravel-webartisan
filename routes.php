<?php

get('artisan', ['as' => 'webartisan', 'uses' => 'Emir\Webartisan\WebartisanController@index']);
post('artisan/run', ['as' => 'webartisan.run', 'uses' => 'Emir\Webartisan\WebartisanController@actionRpc']);
