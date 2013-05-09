<?php
if (!function_exists('apache_request_headers')) {
    function apache_request_headers() {
        foreach($_SERVER as $key=>$value) {
            if (substr($key,0,5)=="HTTP_") {
                $key=str_replace(" ","-",ucwords(str_replace("_"," ",substr($key,5))));
                $out[$key]=$value;
            }
        }
        return $out;
    }
}
$headers = apache_request_headers();

error_log('ENDPOINT HIT');

if(isset($headers['X-DataSift-ID'])){

	$filename = 'ds_' . $headers['X-DataSift-ID'] . '_' . uniqid() . '.json';
	$ok = file_put_contents('/data/'. $filename, file_get_contents('php://input')."\n");
	if (false === $ok) {
		//whooops
		error_log('cannot write to file');
		header('HTTP/1.1 500 NO SPACE LEFT ON DEVICE');
		exit(-1);
	}
	
	$pipe="/tmp/queueserver-input";
	$fh = fopen($pipe, 'a+') or error_log("can't open file $pipe");
	$ok = fwrite($fh, $filename."\n");
	if (false === $ok) {
		//whooops
		error_log('cannot write to pipe');
		header('HTTP/1.1 500 FAILED WRITING TO QUEUE');
		exit(-1);
	}
	fclose($fh);
	error_log('WRITTEN TO PIPE');
}

echo json_encode(array('success' => true));