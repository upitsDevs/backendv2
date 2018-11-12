<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\device;
class operationsController extends Controller
{
    //
	public function bindUserDevice(Request $request) {
		$user_id = $request->user()->id;
		$device_id = $request->input('device_id');
		$device_sn = $request->input('device_sn');
		$device = device::where('deviceID','=',$device_id)->where('sn','=',$device_sn)->firstOrFail();
		$device->user_id = $user_id;
		$device->binded = 1;
		$device->save();
		return response($device,200);
	}
}
