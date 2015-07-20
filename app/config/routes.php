<?php

$app->get('/', controller($app, "app:index"));
$app->get('/galeries', controller($app, "app:galleries"))->bind('galeries');
$app->get('/photo/{id}/ft/likes', controller($app, "app:singlePhoto"))->bind('single_photo');


$app->get('/api/albums', controller($app, "app:albums"))->bind('api_albums');
$app->post('/api/upload/photo', controller($app, "app:uploadPhoto"))->bind('api_upload_photo');
$app->get('/api/album/{id}/photos', controller($app, "app:photosByAlbum"))->bind('api_photos_by_album');

$app->get('/api/login/upload', controller($app, "app:getLoginUpload"));

$app->get('/les-lots-a-gagner', controller($app, "app:prizeList"));