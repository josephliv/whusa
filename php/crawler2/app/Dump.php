<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dump extends Model
{
	protected $table 		= 'PC_dump';
	protected $primaryKey	= 'id';
	public    $timestamps 	= false;
}
