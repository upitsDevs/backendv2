<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\device;
use Hash;
use App\detection;
use Carbon\Carbon;
use Auth;

class DeviceController extends Controller
{
	/* DEVICE MAIN LOG FUNC
	*	TYPE : POST
	*   ACCEPTS : DEVICE_ID , LOCAL_IP , Software VERSION  
	*	RETURNS : IF NEW DEVICE ( RETURN NEW DEVICE FULL DATA WITH API KEY ) , IF ALLREADY REGISTERED (RETURNS CHANNEL AFTER AUTH CHECK )
	*/
	public function create(Request $request) {
		$device_mac = $request->input('device');
		$device_id = getIdFromMac($device_mac);
		$request->validate([
			'device' => 'required',
			'local_ip' => 'required',
			'swv' => 'required'
		]);
		$timeStamp = Carbon::now()->timestamp;
		$sn = "upv1" . $timeStamp;
		$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
		$plainKey = substr(str_shuffle($permitted_chars), 0, 32);
		$data = array(
			"device" => $device_mac,
			"global_ip" => $request->ip(),
			"local_ip" => $request->input('local_ip'),
			"sn" => $sn,
			"key" => Hash::make($plainKey),
			"status" => 0,
			"type" => $request->input('type'),
			"user_id" => "0",
			"binded" => 0,
		);
		$device = device::firstOrCreate([ "deviceID" => $device_id ], $data);
		if($device->wasRecentlyCreated) {
			$new_device = array(
				"type"=> "init",
				"key" => $plainKey,
				"sn" => $sn,
				"device_id" => $device_id
			);
			return response($new_device);
			}else{
				return authCheck($device,$request);
			}
		}
	// CHECK FOR UPDATE ( GET , CONNECTION FROM DEVICE ONLY ) {RETURNS CURRENT VERSION}
	public function checkUpdate() {
		return response()->json('{"current_version":"1.01"}',200);
	}	
	// LIST ALL DEVICES 
	public function list() {
		$devices = device::all();
		$response = [];
		foreach( $devices as $device) {
			$device['user'] = $device->user;
			$response = $device;
		}
		return response($devices);
	}
	// ADD DEVICE TO ACCOUNT { POST , (ACCEPTS DEVICE_ID , DEVICE_PASSWORD ) }
	public function add_device(Request $request) {
        $request->validate([
			'device_id' => 'required',
			'password' => 'required'			
        ]);
    try{
        $device = device::where("deviceID","=",$request->input("device_id"))->firstOrFail();                
		if (! Hash::check($request->input('password'), $device->password)) {
			return response()->json('{"Message":"Device Password Is Wrong"}',401);
		}
		if($device->user()->where('user_id','=',Auth::guard()->user()->id)->exists()) {
            return response()->json('{"Message":"device already registered"}',406);
        }
        Auth::guard()->user()->device()->attach($device);
        return response()->json('{"Message":"Device successfully added to your account"}',202);
        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json('{"Message":"No Device Id or Not Found"}',404);
    }
	}
	// REMOVE DESIGN FROM ACCOUNT ( POST , ACCEPTS DEVICE_ID )
    public function remove_device(Request $request) {
        $request->validate([
			'device_id' => 'required',			
        ]);
        try{
            $device = device::where("deviceID","=",$request->input("device_id"))->firstOrFail();
            $status = Auth::guard()->user()->device()->detach($device);
            if($status == 1){
                return response()->json('{"Message":"Device successfully removed from your account"}',202);
            }else{
                return response()->json('{"Message":"no device attached to your account with this id"}',400);
            }
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json('{"Message":"No Device Id or Not Found"}',404);
        }

	}
	// GET Account Devices ( GET return JSON )
    public function get_devices() {
        return response(Auth::guard()->user()->device,200);
	}
	// get device Data { POST , RETURN TYPE JSON , ACCEPTS DEVICE_ID }
	public function getDevice(Request $request){
		$id = $request->input('device_id');
		try{
		$device = device::where('deviceID','=',$id)->firstOrFail();
		return response()->json($device,200);
		}catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
						return response()->json('No Device Id or Not Found',404);
			}
	}
	// Set Device Password ( POST , Accepts {String device (mac_address), String password} ) 
	public function set_passsword(Request $request){
		$request->validate([
			'device' => 'required',
			'password' => 'required'
		]);
		$device_id = getIdFromMac($request->input('device'));
		$password = $request->input('password');
		$device = device::where('deviceID','=',$device_id)->firstOrFail();
		if(Hash::check($request->header("Authorization"),$device->key)){
			$hashed = Hash::make($password);
			$device->password = $hashed;
			$device->save();
			return response()->json('{"status":"success"}',200);
		}else{
			return response()->json('{"status": "not authorized"}', 401);
		}			
	}
	// ping server ( GET , Return type : Json with 200 code)		
	public function ping() {
		$status = array(
						'status' => 'connected',
						);
		return response()->json($status,200);
	}
	// Change device state between active or in active accepts ( String deviceID , bool state)
	public function changeState(Request $request) {
		$deviceID = $request->input('deviceID');
		$state = $request->input("state");

		$device = device::where('deviceID','=',$deviceID)->firstOrFail();
		$device->status = $state;
		$device->save();
		return $device;
	}

}
