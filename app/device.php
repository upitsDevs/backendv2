<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\device;
class device extends Model
{
    //
	protected $fillable = ['device','local_ip','global_ip','deviceID','sn','user_id','type','binded','status'];
	protected $hidden = ['password'];
	public $timestamps = 'true';
	
	public function user() {
		return $this->belongsTo('App\User');
	}
}
