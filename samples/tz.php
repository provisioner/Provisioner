<?php
/*

	Please read past the license, for important differences between this
	class and the PHP5.3 implementation.

    DateTimeZone class, by jort.bloem@btg.co.nz.
    (C) Copyright 2011 BTG www.btg.co.nz

***** BEGIN LICENSE BLOCK *****
Version: MPL 1.1/GPL 2.0/LGPL 2.1

The contents of this file are subject to the Mozilla Public License Version 
1.1 (the "License"); you may not use this file except in compliance with 
the License. You may obtain a copy of the License at 
http://www.mozilla.org/MPL/

Software distributed under the License is distributed on an "AS IS" basis,
WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
for the specific language governing rights and limitations under the
License.

The Original Code is the Provisioner System for VoIP/PBX/SWITCH Servers.

The Initial Developer of the Original Code is Jort Bloem working for 
Business Techology Group.  Portions created by the Initial Developer are 
Copyright (C) 2011 Business Technology Group http://www.btg.co.nz/ . 
All Rights Reserved.

Contributor(s): 
	Jort Bloem

Alternatively, the contents of this file may be used under the terms of
either the GNU General Public License Version 2 or later (the "GPL"), or
the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
in which case the provisions of the GPL or the LGPL are applicable instead
of those above. If you wish to allow use of your version of this file only
under the terms of either the GPL or the LGPL, and not to allow others to
use your version of this file under the terms of the MPL, indicate your
decision by deleting the provisions above and replace them with the notice
and other provisions required by the GPL or the LGPL. If you do not delete
the provisions above, a recipient may use your version of this file under
the terms of any one of the MPL, the GPL or the LGPL.

***** END LICENSE BLOCK *****

    If you wish to use this software under a different license, please
    contact us - we don't bite.



	The interface to this class is approximately a subset of PHP 5.3's
	DateTimeZone. This lets you use the DateTimeZone class (or a subset
	thereof) in earlier versions of PHP. The name of the class is the
	same, and if DateTimeZone is already provided, this file does nothing.

	This class seems to have slightly different timezone info than
	the standard DateTimeZone, this has only been confirmed in December 
	1901; probably due to a different time database, or different handling.
	There may be other discrepancies.

DIFFERENCES:
	The following things are different in this library from the standard
	PHP5.3 implementation:

	* This library will only work on Linux boxes, with timezone information
		in /usr/share/zoneinfo, and with tzdump.
	* None of the procedural-style functions. You have to create an object
		and query it.
	* NO CONSTANTS 
	* getLocation - NOT IMPLEMENTED - I don't know where this data 
		comes from
	* getTransitions - this does not take any parameters, and always
		returns ALL transitions. NOTE ALSO - the data used to create
		the transition list is the data from zdump - this seems to
		differ slightly from the native PHP5.3 implementation
		(so far only confirmed around December 1901).
	* listAbbreviations - NOT IMPLEMENTED
	* listIdentifiers - List of timezones differs slightly from the
		PHP 5.3 implementation on my box - there are some extra
		timezones reported by this library. I have not done an 
		exhaustive comparison. For example, on my box, this function
		lists both Africa/Asmara and Africa/Asmera, whereas PHP 5.3
		only lists one of these.
*/

if (!class_exists("DateTime")) {
	class DateTime {
            
        }
}

if (!class_exists("DateTimeZone")) {
	class DateTimeZone {
		private $ZDump;
		function __construct($name) {
			if (preg_match('|^[a-z]+\/[a-z_\-]+$|i',$name)!=1) {
				# Invalid characters - maybe ../ or something.
				throw new exception("DateTimeZone::__construct(): Unknown or bad timezone ($name)");
			}
			if (!file_exists("/usr/share/zoneinfo/$name")) {
				# No such timezone.
				throw new exception("DateTimeZone::__construct(): Unknown or bad timezone ($name)");
			}
			$this->name=$name;
		}
		function getName() {
			return $this->name;
		}
		private function getZDump() {
			if (!is_array($this->ZDump)) {
				$this->ZDump=array();
				$months=array(
					'Jan'=>1,'Feb'=>2,'Mar'=>3,'Apr'=>4,
					'May'=>5,'Jun'=>6,'Jul'=>7,'Aug'=>8,
					'Sep'=>9,'Oct'=>10,'Nov'=>11,'Dec'=>12);
				$zdump=popen("zdump -v ".$this->name,"r");
				$last="none";
				while (($line = fgets($zdump))!==false) {
					$line=preg_split('/[\s:=]+/',rtrim($line));
					$line[2]=$months[$line[2]];
					$utc=gmmktime($line[4],$line[5],$line[6],$line[2],$line[3],$line[7]);
					if ($last!==intval($line[20])) {
						$this->ZDump[]=array('ts'=>$utc,
							'offset'=>intval($line[20]),
							'dst'=>($line[18]=='1'?true:false));
						$last=intval($line[20]);
					}
				}
				pclose($zdump);
			}
		}
		function getOffset() {
			# Gets the current time offset.
			# This is found in the LAST transition happening on or 
			# before now().
			$this->getZDump();
			foreach ($this->ZDump AS $atransition) {
				if ($atransition['ts']<=time()) {
					$transition=$atransition;
				} else {
					return $transition['offset'];
				}
			}
			return;
		}
		function getTransitions() {
			$this->getZDump();
			return $this->ZDump;
		}
		function listIdentifiers() {
			$result=array();
			foreach (explode(" ","Africa America Antarctica Arctic Asia Atlantic Australia Europe Indian Pacific") AS $dir) {
				$rdir=opendir("/usr/share/zoneinfo/$dir");
				while (($file=readdir($rdir))!==false) {
					if (is_file("/usr/share/zoneinfo/$dir/$file")) {
						$result[]="$dir/$file";
					}
				}
			}
			sort($result);
			$result[]="UTC";
			return $result;
		}
	}
}
