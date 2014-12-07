foreach( $opts as $method=>$apis ) {
//	var_dump($method); //FIXME: propper debuging
	foreach( $apis as $api ) {
//		var_dump($api); //FIXME: propper debuging
		list($host, $api) = explode("?",$api,2);
		foreach( $obj as $k=>$v ) {
			$api = str_replace("%".$k,$method == "get" ? urlencode($v) : $v, $api);
		}
//		var_dump($api); //FIXME: propper debuging
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, ($method == "get" ? $host."?".$api : $host) );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt($curl, CURLOPT_POSTFIELDS, $api);
		$ret = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if( $code != 200 ) {
			var_dump("API '$host' returnd Error"); //FIXME: propper debuging
			var_dump("Params: ".$api); //FIXME: propper debuging
			var_dump("Return: ".$ret); //FIXME: propper debuging
			return false;
		}
	}
}
return true;
