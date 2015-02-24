<?PHP

/**
 * Snom Base File
 *
 * @author Jort
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_snom_base extends endpoint_base {

    public $brand_name = 'snom';
    public $protected_files = 'general_custom.xml';
    public $mapfields=array(
	'dateformat'=>array('middle-endian'=>'on','big-endian'=>'off','default'=>'off'),
    );

    function prepare_for_generateconfig() {
        parent::prepare_for_generateconfig();
        $this->mac = strtoupper($this->mac);

	if ((!isset($this->settings["vlan"])) or ($this->settings["vlan"]==="")) {
		$this->settings["vlan"]="0";
	}
			
        if (isset($this->DateTimeZone)) {
            $transitions = $this->DateTimeZone->getTransitions();
            // Find the last 2 transitions before (1 year from now).
            // 5 day extra window just in case.
            // this can either be the next two, or the last one (very recent) and the next one.
            while ((count($transitions) > 1) && ($transitions[2]['ts'] < time() + 360 * 24 * 60 * 60)) {
                array_shift($transitions);
            }
            if (count($transitions) == 1) {
                $options['dstoffset'] = ''; // no more transitions - are there no transitions at all for this timezone (E.g. GMT)?
            } else {
                if ($transitions[0]['isdst']) {
                    $summer = 0;
                    $winter = 1;
                } else {
                    $summer = 1;
                    $winter = 0;
                }
            }
            $this->timezone['gmtoffset'] = $transitions[$summer]['offset'];
            $dst = $transitions[$summer]['offset'] - $transitions[$winter]['offset'];
            // Have to calculate as gmt, because otherwise DST would throw it off - the time the clocks change never
            // happens; the instant we get there, the clocks have moved.
            $summerstart = gmdate("d.m H:i:s", $transitions[$summer]['ts'] + $this->timezone['gmtoffset'] - $dst);
            $summerend = gmdate("d.m H:i:s", $transitions[$winter]['ts'] + $this->timezone['gmtoffset']);
            $this->settings['dstrule'] = "$dst $summerstart $summerend";
        }
    }

    function reboot() {
        if (($this->engine == "asterisk") AND ($this->system == "unix")) {
            exec($this->engine_location . " -rx 'sip notify reboot-snom " . $this->settings['line'][0]['username'] . "'");
        }
    }

}
