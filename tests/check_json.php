<?php

$file=file_get_contents($_SERVER['argv'][1]);
$queue=array();
$pairs=array('{'=>'}','['=>']');
$line=1; // which line we are currently examening.
$linechar=0; // which character on line $line we are examening.
$quote=false; // are we currently in quote marks? double-quote only.
$last_significant="comma"; // what did we last see? defaults to a comma, because the next thing should be an open-bracket.
	// possible values include: null, boolean (=true or false), string, digit, decimal-point, open-bracket; close-bracket.

## Not yet checked for: {"a" : "b" : "c"}; 12.3.4.5; {"a", "b", "c"}; 1. 2;
## Anything not explicitly checked for will be checked once we have balancing brackets, using json_decode.

print "Checking file ".$_SERVER["argv"][1]."\n";

json_decode($file);
if (json_last_error()==JSON_ERROR_NONE) {
	print "No errors found\n";
	exit;
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
		if (!$quote) {
			if (($last_significant!='comma') AND ($last_significant!='open-bracket') AND ($last_significant!='colon')) {
				print "Found invalid $last_significant followed by a string at line $line char $linechar\n";
				exit;
			}
		}
		$last_significant="string";
		$quote=!$quote;
		continue;
	}
	if ($quote) {
		continue;
	}
	if ($file[$char]==':') {
		if (count($queue)==0) {
			print "Found colon outside all brackets at line $line char $linechar\n";
			exit;
		}
		if ($queue[count($queue)-1][1]=='[') {
			print "Found colon in a list at line $line char $linechar\n";
			exit;
		}
		if (($last_significant!='string') AND ($last_significant!='null') AND ($last_significant!='boolean') AND ($last_significant!='digit')) {
			print "Found invalid $last_significant followed by a string at line $line char $linechar\n";
			exit;
		}
		$last_significant='colon';
	}
	if (($file[$char]>='0') AND ($file[$char]<='9')) {
		if (($last_significant=='close-bracket') OR ($last_significant=='null') OR ($last_significant=='boolean') OR ($last_significant=='string')) {
			print "Found invalid $last_significant followed by a digit at line $line char $linechar\n";
			exit;
		} else {
			$last_significant="digit";
		}
	}
	if ($file[$char]=='.') {
		if ($last_significant=='decimal-point') {
			print "Found double-decimal point in number at line $line char $linechar\n";
			exit;
		}
		if ($last_significant!='digit') {
			print "Found invalid $last_significant followed by a decimal point at line $line char $linechar\n";
			exit;
		}
		$last_significant='decimal-point';
	}
	if (substr($file,$char,4)=='null') {
		if (($last_significant!='comma') AND ($last_significant!='open-bracket') AND ($last_significant!='colon')) {
			print "Found invalid $last_significant followed by a null at line $line char $linechar\n";
			exit;
		}
		$last_significant='null';
	}
	if ((substr($file,$char,5)=='false') OR (substr($file,$char,4)=='true')) {
		if (($last_significant!='comma') AND ($last_significant!='open-bracket') AND ($last_significant!='colon')) {
			print "Found invalid $last_significant followed by a boolean at line $line char $linechar\n";
			exit;
		}
		$last_significant='boolean';
	}
	if ($file[$char]==',') {
		if (($last_significant=='comma') OR ($last_significant=='colon') OR ($last_significant=='decimal-point')) {
			print "Found invalid $last_significant followed by comma at line $line char $linechar\n";
			exit;
		}
		$last_significant="comma";
	}
	if (in_array($file[$char],array_keys($pairs))) {
		// open brackets
		if (($last_significant!='comma') AND ($last_significant!='open-bracket') AND ($last_significant!='colon')) {
			print "Found invalid $last_significant followed by an open-bracket at line $line char $linechar\n";
			exit;
		}
		$last_significant="open-bracket";
		array_push($queue,array($char,$file[$char],$line,$linechar));
		continue;
	}
	if (in_array($file[$char],array_values($pairs))) {
		//close brackets
		if (($last_significant=='comma') OR ($last_significant=='colon') OR ($last_significant=='decimal-point')) {
			print "Found invalid $last_significant followed by close-bracket at line $line char $linechar\n";
			exit;
		}
		$last_significant="close";
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

