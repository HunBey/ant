<?php
/*
 * Download remote file with range
 * 
 * @author lazypeople<hfutming@gmail.com>
 */
function Usage($argv)
{
	die(sprintf("Usage: /usr/bin/php %s url [out_filename] [chunk_size]\n/usr/bin/php %s 'http://www.baidu.com' 1\n", $argv[0], $argv[0]));
}

function echo_hr()
{
	echo "\n";
}

function parse_filename($url)
{
	$file_info = get_headers($url, true);
	if (array_key_exists('Content-Disposition', $file_info)) {
		$b = function() use($file_info) {
			$exp = explode(';', $file_info['Content-Disposition']);
			foreach ($exp as $strstr) {
				if (strstr($strstr, 'filename')) {
					$exp = explode('=', $strstr);
					return $exp[1];
				}
			}
			return false;
		};
		if ($ret = $b()) {
			return $ret;
		}
	}

	$parse_result = parse_url($url);
	$path = $parse_result['path'];
	$path_info = pathinfo($path);
	return $path_info['basename'];
}

function get_file_size($url)
{
	$info = get_headers($url, true);
	$length = $info['Content-Length'];
	return $length;
}

if (count($argv) < 2) {
	Usage($argv);
}

$file = $argv[1];

if (count($argv) == 3) {
	$out_filename = $argv[2];
} else {
	$out_filename = parse_filename($file);
}

$length = get_file_size($file);

echo "Total length:".$length." byte\n";

if (count($argv) == 4) {
	$chunk_size = intval($argv[3]);
} else {
	$chunk_size = 100;
}
$one = $chunk_size*1024*1024;

if ($length < $one) {
	// download directly
	$cmd = sprintf("curl -r 0-%s -o %s '%s'", $length, $out_filename, $file);
	echo "Downloading now...\n";
	exec($cmd);
	die("download successfully..\n");
}

$part = ceil($length/$one);

for ($i=1; $i <= $part; $i++) { 
	if ($i == 1) {
		$start_size = 0;
	} else {
		$start_size = 1+($i-1)*$one;
	}
	if ($i == $part) {
		$end_size = $length;
	} else {
		$end_size = $one*$i;
	}
	echo "Now downing part ".$i;
	$tmp_out_filename = $out_filename.$i;
	$cmd = sprintf("curl -r %s-%s -o %s '%s'", $start_size, $end_size, $tmp_out_filename, $file);
	exec($cmd);
}

echo "Now combine file together\n";
$cmd = 'cat ';
for ($i=1; $i <= $part ; $i++) { 
	$cmd .= $out_filename.$i." ";
}
$cmd .= '> '.$out_filename;
exec($cmd);

echo "Now make some clean work\n";
$cmd = 'rm -rf ';
for ($i=1; $i <= $part ; $i++) { 
	$cmd .= $out_filename.$i." ";
}
exec($cmd);
echo "Clean successfully\n";

echo "Downlod successfully\n";


