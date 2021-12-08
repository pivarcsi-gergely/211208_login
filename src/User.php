<?php
namespace Petrik\loginapp;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
    //Ha a created_at / updated_at nélkül hoztuk létre:
    //protected $timestamps = false;

    protected $visible = ['id', 'email'];
}