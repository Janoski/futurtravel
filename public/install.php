<?php
header( 'content-type: text/html; charset=utf-8' );
error_reporting(E_ALL);
ini_set('display_errors', 1);
$app = require_once __DIR__ . '/../app/bootstrap.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Model\User;
use App\Model\Photo;

$user = new User();
$photo = new Photo();

try {
    Capsule::schema()->create($user->getTable(), function($table)
    {
        $table->increments('id');
        $table->string('email');
        $table->string('first_name');
        $table->string('last_name');
        $table->string('gender');
        $table->bigInteger('facebook_id');
        $table->timestamps();
    });

    Capsule::schema()->create($photo->getTable(), function($table)
    {
        $table->increments('id');
        $table->integer('user_id')->unsigned()->index();
        $table->string('link');
        $table->string('title');
        $table->string('description');
        $table->string('photo_id');
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
    });

    echo 'Installation terminée !!';
} catch (PDOException $e) {
    echo "Installation déjà effectué !!";
}