<?php

get('webartisan', ['as' => 'webartisan', 'uses' => 'Emir\Webartisan\WebartisanController@index']);
post('webartisan/run', ['as' => 'webartisan.run', 'uses' => 'Emir\Webartisan\WebartisanController@actionRpc']);
