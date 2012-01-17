<?php

$file=file_get_contents($_SERVER['argv'][1]);
$queue=array();
$pairs=array('{'=>'}','['=>']');
$line=1;
$linechar=0;
$quote=false;

print "Checking file ".$_SERVER["argv"][1]."\n";

json_decode($file);
if (json_last_error()==JSON_ERROR_NONE) {
	print "No errors found\n";
}

$json_errors=array( // Taken from http://www.php.net/manual/en/function.json-last-error.php
	JSON_ERROR_NONE=>'No error has occurred',
	JSON_ERROR_DEPTH=>'The maximum stack depth has been exceeded',
	JSON_ERROR_STATE_MISMATCH=>'Invalid or malformed JSON',
	JSON_ERROR_CTRL_CHAR=>'Control character error, possibly incorrectly encoded',
	JSON_ERROR_SYNTAX=>'Syntax error',
	# This is php 5.3.3 or better. May have value of 5.
	#	JSON_ERROR_UTF8=>'Malformed UTF-8 characters, possibly incorrectly encoded'
);


for ($char=0; $char<strlen($file); $char++) {
	$linechar++;
	if ($file[$char]=="\n") {
		$line++;
		$linechar=0;
	}
	if ($file[$char]=='\\') {
		$char++;
		$linechar++;
		continue;
	}
	if ($file[$char]=='"') {
		$quote=!$quote;
		continue;
	}
	if ($quote) {
		continue;
	}
	if (in_array($file[$char],array_keys($pairs))) {
		array_push($queue,array($char,$file[$char],$line,$linechar));
		continue;
	}
	if (in_array($file[$char],array_values($pairs))) {
		if (count($queue)==0) {
			print "symbol mismatch - $file[$char] on line $line char ".($char-1)." does not have balancing".$pairs[$file[$char]]."\n";
			die;
		}
		$partner=array_pop($queue);
		if ($pairs[$partner[1]]!=$file[$char]) {
			print "symbol mismatch - $partner[1] on line $partner[2] character $partner[3] does not balance $file[$char] on line $line char $linechar\n";
			die;
		};
		$test=substr($file,$partner[0],$char-$partner[0]+1);
		json_decode($test);
		$err=json_last_error();
		if ($err!=JSON_ERROR_NONE) {
			if (array_key_exists($err,$json_errors)) {
				$err=$json_errors[$err];
			} else {
				$err="Unknown error $err";
			}
			print "JSON Error $err\n";
			print "From line $partner[2] character $partner[3]:\n";
			print "$test\n";
			die;
		}
		continue;
	}
}

