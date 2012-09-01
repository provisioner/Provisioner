<?PHP

/**
 * Aastra Base File
 *
 * 
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_aastra_base extends endpoint_base {

    public $brand_name = 'aastra';

    function reboot() {
        if (($this->engine == "asterisk") AND ($this->system == "unix")) {
            exec($this->engine_location . " -rx 'sip notify aastra-check-cfg " . $this->settings['line'][0]['username'] . "'");
        }
    }

    function prepare_for_generateconfig() {
        $this->mac = strtoupper($this->mac);
        parent::prepare_for_generateconfig();
    }

    function enable_encryption() {
        if (is_executable($this->root_dir . $this->modules_path . $this->brand_name . "/anacrypt")) {
            if (!file_exists($this->root_dir . $this->modules_path . $this->brand_name . "/security.tuz")) {
                exec($this->root_dir . $this->modules_path . $this->brand_name . "/anacrypt -i -p 1234abcd");
                rename("security.tuz", $this->root_dir . $this->modules_path . $this->brand_name . "/security.tuz");
                if (!file_exists($this->root_dir . $this->modules_path . $this->brand_name . "/security.tuz")) {
                    return(FALSE);
                } else {
                    return(TRUE);
                }
            } else {
                return(TRUE);
            }
        } else {
            return(FALSE);
        }
    }

    function disable_encryption() {
        if (file_exists($this->root_dir . $this->modules_path . $this->brand_name . "/security.tuz")) {
            unlink($this->root_dir . $this->modules_path . $this->brand_name . "/security.tuz");
        }
    }

    function encrypt_files($returned_array) {
        if (is_executable($this->root_dir . $this->modules_path . $this->brand_name . "/anacrypt")) {
            if (file_exists($this->root_dir . $this->modules_path . $this->brand_name . "/security.tuz")) {
                foreach ($returned_array as $key => $data) {
                    mkdir($this->root_dir . $this->modules_path . $this->brand_name . "/secure_temp");

                    $file = $this->root_dir . $this->modules_path . $this->brand_name . "/secure_temp/" . $key;
                    $fp = fopen($file, 'w');
                    fwrite($fp, $data);
                    fclose($fp);

                    exec($this->root_dir . $this->modules_path . $this->brand_name . "/anacrypt " . $file . " -p 1234abcd");

                    if (file_exists($this->mac . ".tuz")) {
                        rename($this->mac . ".tuz", $this->root_dir . $this->modules_path . $this->brand_name . "/" . $this->mac . ".tuz");
                        $handle = fopen($this->root_dir . $this->modules_path . $this->brand_name . "/" . $this->mac . ".tuz", "rb");
                        $contents = stream_get_contents($handle);
                        fclose($handle);
                        unlink($this->root_dir . $this->modules_path . $this->brand_name . "/" . $this->mac . ".tuz");
                        $encrypted_array[$this->mac . '.tuz'] = $contents;
                    } elseif (file_exists("aastra.tuz")) {
                        rename("aastra.tuz", $this->root_dir . $this->modules_path . $this->brand_name . "/aastra.tuz");
                        $handle = fopen($this->root_dir . $this->modules_path . $this->brand_name . "/aastra.tuz", "rb");
                        $contents = stream_get_contents($handle);
                        fclose($handle);
                        //unlink($this->root_dir. $this->modules_path . $this->brand_name . "/aastra.tuz");
                        $encrypted_array['aastra.tuz'] = $contents;
                    }
                    unlink($this->root_dir . $this->modules_path . $this->brand_name . "/secure_temp/" . $key);
                }
                return($encrypted_array);
            }
        }
    }

	function aastra_timezone_conversion() {
		//Review manual for aastra page 376
$list[] = array(
            'name' => 'AD-Andorra',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'AE-Dubai',
            'code' => 'GST',
            'offset' => '4'
        );
$list[] = array(
            'name' => 'AG-Antigua',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'AI-Anguilla',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'AL-Tirane',
            'code' => 'CET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'AN-Curacao',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'AR-Buenos Aires',
            'code' => 'ART',
            'offset' => '-3'
        );
$list[] = array(
            'name' => 'AS-Pago Pago',
            'code' => 'BST',
            'offset' => '-11'
        );
$list[] = array(
            'name' => 'AT-Vienna',
            'code' => 'CET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'AU-Lord Howe',
            'code' => 'LHS',
            'offset' => '10.5'
        );
$list[] = array(
            'name' => 'AU-Tasmania',
            'code' => 'EST',
            'offset' => '10'
        );
$list[] = array(
            'name' => 'AU-Melbourne',
            'code' => 'EST',
            'offset' => '10'
        );
$list[] = array(
            'name' => 'AU-Sydney',
            'code' => 'EST',
            'offset' => '10'
        );
$list[] = array(
            'name' => 'AU-Broken Hill',
            'code' => 'CST',
            'offset' => '9.5'
        );
$list[] = array(
            'name' => 'AU-Brisbane',
            'code' => 'EST',
            'offset' => '10'
        );
$list[] = array(
            'name' => 'AU-Lindeman',
            'code' => 'EST',
            'offset' => '10'
        );
$list[] = array(
            'name' => 'AU-Adelaide',
            'code' => 'CST',
            'offset' => '9.5'
        );
$list[] = array(
            'name' => 'AU-Darwin',
            'code' => 'CST',
            'offset' => '9.5'
        );
$list[] = array(
            'name' => 'AU-Perth',
            'code' => 'WST',
            'offset' => '8'
        );
$list[] = array(
            'name' => 'AW-Aruba',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'AZ-Baku',
            'code' => 'AZT',
            'offset' => '5'
        );
$list[] = array(
            'name' => 'BA-Sarajevo',
            'code' => 'EET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'BB-Barbados',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'BE-Brussels',
            'code' => 'CET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'BG-Sofia',
            'code' => 'EET',
            'offset' => '3'
        );
$list[] = array(
            'name' => 'BM-Bermuda',
            'code' => 'AST',
            'offset' => '-3'
        );
$list[] = array(
            'name' => 'BO-La Paz',
            'code' => 'BOT',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'BR-Noronha',
            'code' => 'FNT',
            'offset' => '-2'
        );
$list[] = array(
            'name' => 'BR-Belem',
            'code' => 'BRT',
            'offset' => '-3'
        );
$list[] = array(
            'name' => 'BR-Fortaleza',
            'code' => 'BRT',
            'offset' => '-3'
        );
$list[] = array(
            'name' => 'BR-Recife',
            'code' => 'BRT',
            'offset' => '-3'
        );
$list[] = array(
            'name' => 'BR-Araguaina',
            'code' => 'BRS',
            'offset' => '-3'
        );
$list[] = array(
            'name' => 'BR-Maceio',
            'code' => 'BRT',
            'offset' => '-3'
        );
$list[] = array(
            'name' => 'BR-Sao Paulo',
            'code' => 'BRS',
            'offset' => '-3'
        );
$list[] = array(
            'name' => 'BR-Cuiaba',
            'code' => 'AMS',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'BR-Porto Velho',
            'code' => 'AMT',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'BR-Boa Vista',
            'code' => 'AMT',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'BR-Manaus',
            'code' => 'AMT',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'BR-Eirunepe',
            'code' => 'ACT',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'BR-Rio Branco',
            'code' => 'ACT',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'BS-Nassau',
            'code' => 'EST',
            'offset' => '-5'
        );
$list[] = array(
            'name' => 'BY-Minsk',
            'code' => 'EET',
            'offset' => '3'
        );
$list[] = array(
            'name' => 'BZ-Belize',
            'code' => 'CST',
            'offset' => '-6'
        );
$list[] = array(
            'name' => 'CA-Newfoundland',
            'code' => 'NST',
            'offset' => '-3.5'
        );
$list[] = array(
            'name' => 'CA-Atlantic',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'CA-Eastern',
            'code' => 'EST',
            'offset' => '-5'
        );
$list[] = array(
            'name' => 'CA-Saskatchewan',
            'code' => 'EST',
            'offset' => '-6'
        );
$list[] = array(
            'name' => 'CA-Central',
            'code' => 'CST',
            'offset' => '-6'
        );
$list[] = array(
            'name' => 'CA-Mountain',
            'code' => 'MST',
            'offset' => '-7'
        );
$list[] = array(
            'name' => 'CA-Pacific',
            'code' => 'PST',
            'offset' => '-8'
        );
$list[] = array(
            'name' => 'CA-Yukon',
            'code' => 'PST',
            'offset' => '-8'
        );
$list[] = array(
            'name' => 'CH-Zurich',
            'code' => 'CET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'CK-Rarotonga',
            'code' => 'CKS',
            'offset' => '-10'
        );
$list[] = array(
            'name' => 'CL-Santiago',
            'code' => 'CLS',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'CL-Easter',
            'code' => 'EAS',
            'offset' => '-6'
        );
$list[] = array(
            'name' => 'CN-China',
            'code' => 'CST',
            'offset' => '8'
        );
$list[] = array(
            'name' => 'CO-Bogota',
            'code' => 'COS',
            'offset' => '-5'
        );
$list[] = array(
            'name' => 'CR-Costa Rica',
            'code' => 'CST',
            'offset' => '-6'
        );
$list[] = array(
            'name' => 'CU-Havana',
            'code' => 'CST',
            'offset' => '-5'
        );
$list[] = array(
            'name' => 'CY-Nicosia',
            'code' => 'EES',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'CZ-Prague',
            'code' => 'CET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'DE-Berlin',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'DK-Copenhagen',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'DM-Dominica',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'DO-Santo Domingo',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'EE-Tallinn',
            'code' => 'EET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'ES-Madrid',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'ES-Canary',
            'code' => 'WET',
            'offset' => '0'
        );
$list[] = array(
            'name' => 'FI-Helsinki',
            'code' => 'EET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'FJ-Fiji',
            'code' => 'NZT',
            'offset' => '12'
        );
$list[] = array(
            'name' => 'FK-Stanley',
            'code' => 'FKS',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'FO-Faeroe',
            'code' => 'WET',
            'offset' => '0'
        );
$list[] = array(
            'name' => 'FR-Paris',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'GB-London',
            'code' => 'GMT',
            'offset' => '0'
        );
$list[] = array(
            'name' => 'GB-Belfast',
            'code' => 'GMT',
            'offset' => '0'
        );
$list[] = array(
            'name' => 'GD-Grenada',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'GE-Tbilisi',
            'code' => 'GET',
            'offset' => '4'
        );
$list[] = array(
            'name' => 'GF-Cayenne',
            'code' => 'GFT',
            'offset' => '-3'
        );
$list[] = array(
            'name' => 'GI-Gibraltar',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'GP-Guadeloupe',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'GR-Athens',
            'code' => 'EET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'GS-South Georgia',
            'code' => 'GST',
            'offset' => '4'
        );
$list[] = array(
            'name' => 'GT-Guatemala',
            'code' => 'CST',
            'offset' => '-6'
        );
$list[] = array(
            'name' => 'GU-Guam',
            'code' => 'CST',
            'offset' => '10'
        );
$list[] = array(
            'name' => 'GY-Guyana',
            'code' => 'GYT',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'HK-Hong Kong',
            'code' => 'HKS',
            'offset' => '8'
        );
$list[] = array(
            'name' => 'HN-Tegucigalpa',
            'code' => 'CST',
            'offset' => '-6'
        );
$list[] = array(
            'name' => 'HR-Zagreb',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'HT-Port-au-Prince',
            'code' => 'EST',
            'offset' => '-5'
        );
$list[] = array(
            'name' => 'HU-Budapest',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'IE-Dublin',
            'code' => 'GMT',
            'offset' => '0'
        );
$list[] = array(
            'name' => 'IS-Reykjavik',
            'code' => 'GMT',
            'offset' => '0'
        );
$list[] = array(
            'name' => 'IT-Rome',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'JM-Jamaica',
            'code' => 'EST',
            'offset' => '-5'
        );
$list[] = array(
            'name' => 'JP-Tokyo',
            'code' => 'JST',
            'offset' => '9'
        );
$list[] = array(
            'name' => 'KY-Cayman',
            'code' => 'EST',
            'offset' => '-5'
        );
$list[] = array(
            'name' => 'LC-St Lucia',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'LI-Vaduz',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'LT-Vilnius',
            'code' => 'EET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'LU-Luxembourg',
            'code' => 'CET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'LV-Riga',
            'code' => 'EET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'MC-Monaco',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'MD-Chisinau',
            'code' => 'EET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'MK-Skopje',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'MQ-Martinique',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'MS-Montserrat',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'MT-Malta',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'MU-Mauritius',
            'code' => 'MUT',
            'offset' => '4'
        );
$list[] = array(
            'name' => 'MX-Mexico City',
            'code' => 'CST',
            'offset' => '-6'
        );
$list[] = array(
            'name' => 'MX-Cancun',
            'code' => 'CST',
            'offset' => '-6'
        );
$list[] = array(
            'name' => 'MX-Merida',
            'code' => 'CST',
            'offset' => '-6'
        );
$list[] = array(
            'name' => 'MX-Monterrey',
            'code' => 'CST',
            'offset' => '-6'
        );
$list[] = array(
            'name' => 'MX-Mazatlan',
            'code' => 'MST',
            'offset' => '-7'
        );
$list[] = array(
            'name' => 'MX-Chihuahua',
            'code' => 'MST',
            'offset' => '-7'
        );
$list[] = array(
            'name' => 'MX-Hermosillo',
            'code' => 'MST',
            'offset' => '-7'
        );
$list[] = array(
            'name' => 'MX-Tijuana',
            'code' => 'PST',
            'offset' => '-8'
        );
$list[] = array(
            'name' => 'NI-Managua',
            'code' => 'CST',
            'offset' => '-6'
        );
$list[] = array(
            'name' => 'NL-Amsterdam',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'NO-Oslo',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'NR-Nauru',
            'code' => 'NRT',
            'offset' => '12'
        );
$list[] = array(
            'name' => 'NU-Niue',
            'code' => 'NUT',
            'offset' => '-11'
        );
$list[] = array(
            'name' => 'NZ-Auckland',
            'code' => 'NZS',
            'offset' => '12'
        );
$list[] = array(
            'name' => 'NZ-Chatham',
            'code' => 'CHA',
            'offset' => '12.75'
        );
$list[] = array(
            'name' => 'OM-Muscat',
            'code' => 'GST',
            'offset' => '4'
        );
$list[] = array(
            'name' => 'PA-Panama',
            'code' => 'EST',
            'offset' => '-5'
        );
$list[] = array(
            'name' => 'PE-Lima',
            'code' => 'PES',
            'offset' => '-5'
        );
$list[] = array(
            'name' => 'PL-Warsaw',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'PR-Puerto Rico',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'PT-Lisbon',
            'code' => 'WET',
            'offset' => '0'
        );
$list[] = array(
            'name' => 'PT-Madeira',
            'code' => 'WET',
            'offset' => '0'
        );
$list[] = array(
            'name' => 'PT-Azores',
            'code' => 'AZO',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'PY-Asuncion',
            'code' => 'PYS',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'RO-Bucharest',
            'code' => 'EET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'RU-Kaliningrad',
            'code' => 'EET',
            'offset' => '3'
        );
$list[] = array(
            'name' => 'RU-Moscow',
            'code' => 'MSK',
            'offset' => '4'
        );
$list[] = array(
            'name' => 'RU-Samara',
            'code' => 'SAM',
            'offset' => '4'
        );
$list[] = array(
            'name' => 'RU-Yekaterinburg',
            'code' => 'YEK',
            'offset' => '6'
        );
$list[] = array(
            'name' => 'RU-Omsk',
            'code' => 'OMS',
            'offset' => '7'
        );
$list[] = array(
            'name' => 'RU-Novosibirsk',
            'code' => 'NOV',
            'offset' => '7'
        );
$list[] = array(
            'name' => 'RU-Krasnoyarsk',
            'code' => 'KRA',
            'offset' => '8'
        );
$list[] = array(
            'name' => 'RU-Irkutsk',
            'code' => 'IRK',
            'offset' => '9'
        );
$list[] = array(
            'name' => 'RU-Yakutsk',
            'code' => 'YAK',
            'offset' => '10'
        );
$list[] = array(
            'name' => 'RU-Vladivostok',
            'code' => 'VLA',
            'offset' => '11'
        );
$list[] = array(
            'name' => 'RU-Sakhalin',
            'code' => 'SAK',
            'offset' => '11'
        );
$list[] = array(
            'name' => 'RU-Magadan',
            'code' => 'MAG',
            'offset' => '12'
        );
$list[] = array(
            'name' => 'RU-Kamchatka',
            'code' => 'PET',
            'offset' => '12'
        );
$list[] = array(
            'name' => 'RU-Anadyr',
            'code' => 'ANA',
            'offset' => '12'
        );
$list[] = array(
            'name' => 'SE-Stockholm',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'SG-Singapore',
            'code' => 'SGT',
            'offset' => '8'
        );
$list[] = array(
            'name' => 'SI-Ljubljana',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'SK-Bratislava',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'SM-San Marino',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'SR-Paramaribo',
            'code' => 'SRT',
            'offset' => '-3'
        );
$list[] = array(
            'name' => 'SV-El Salvador',
            'code' => 'CST',
            'offset' => '-6'
        );
$list[] = array(
            'name' => 'TR-Istanbul',
            'code' => 'EET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'TT-Port of Spain',
            'code' => 'AST',
            'offset' => '-4'
        );
$list[] = array(
            'name' => 'TW-Taipei',
            'code' => 'CST',
            'offset' => '8'
        );
$list[] = array(
            'name' => 'UA-Kiev',
            'code' => 'EET',
            'offset' => '2'
        );
$list[] = array(
            'name' => 'US-Eastern',
            'code' => 'EST',
            'offset' => '-5'
        );
$list[] = array(
            'name' => 'US-Central',
            'code' => 'CST',
            'offset' => '-6'
        );
$list[] = array(
            'name' => 'US-Mountain',
            'code' => 'MST',
            'offset' => '-7'
        );
$list[] = array(
            'name' => 'US-Pacific',
            'code' => 'PST',
            'offset' => '-8'
        );
$list[] = array(
            'name' => 'US-Alaska',
            'code' => 'AKS',
            'offset' => '-9'
        );
$list[] = array(
            'name' => 'US-Aleutian',
            'code' => 'HAS',
            'offset' => '-10'
        );
$list[] = array(
            'name' => 'US-Hawaii',
            'code' => 'HST',
            'offset' => '-10'
        );
$list[] = array(
            'name' => 'UY-Montevideo',
            'code' => 'UYS',
            'offset' => '-3'
        );
$list[] = array(
            'name' => 'VA-Vatican',
            'code' => 'CET',
            'offset' => '1'
        );
$list[] = array(
            'name' => 'YU-Belgrade',
            'code' => 'CET',
            'offset' => '1'
        );
	}

}