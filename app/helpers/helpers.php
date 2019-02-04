<?php
if (! function_exists('authCheck')) {
    function authCheck($device,$request) {        
			if(Hash::check($request->header("Authorization"),$device->key))
			{
				if($device->status) {
					$device->global_ip = $request->ip();
					$device->local_ip  = $request->input('local_ip');
					$device->swv = $request->input('swv');
					$device->touch();
					$device->save();
					//return response()->json('{"status": "authorized"}',202);
					$response_array = array(
						"channel" => $device->sn
					);
					return response()->json($response_array,202);
				}else{
					return response()->json('{"status": "authorized but not active"}',451);
				}				
				}else{
					return response()->json('{"status": "not authorized"}', 401);
				}		
    }
}
if(! function_exists('getIdFromMac')){
	function getIdFromMac($macAddress) {
		$device_id = str_replace(':','',$macAddress);
		return $device_id;
	}
}

if(! function_exists('verifyAuth')){
	function verifyAuth($device,$key) {
		if(Hash::check($key,$device->key)){
			return response()->json('{"status": "not authorized"}', 401);
		}else{
			return response()->json('{"status": "not authorized"}', 401);
		}
	}
}
?>