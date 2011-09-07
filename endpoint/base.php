<?PHP

/**
 * Base Class for Provisioner
 *
 * @author Darren Schreiber & Andrew Nagy & Jort Bloem
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
abstract class endpoint_base {

    public static $modules_path = "endpoint/";

    public $brand_name = "undefined";
    public $family_line = "undefined";

    public $config_files_override;

    public $mac;            // Device mac address
    public $model;			// Model of phone, must match the model name inside of the famil_data.xml file in each family folder.
    public $timezone;       // Global timezone var
    public $DateTimeZone;   // timezone, as a DateTimezone object, much more flexible than just an offset and name.
    public $server;         // Contains an array of valid server IPs & ports, in case phones support backups
    public $proxy;			// Contains an array of valid proxy IPs & ports
    public $ntp;            //network time protocol server
    public $daylight_savings = FALSE;	//Daylight savings time on or off.
    public $lines;          // Individual line settings
    public $options;        // Misc. options for phones
    public $root_dir = "";		//need to define the root directory for the location of the library (/var/www/html/)
    public $engine;			//Can be asterisk or freeswitch. This is for the reboot commands.
    public $engine_location = "";	//Location of the executable for said engine above
    public $system;			//unix or windows or bsd. etc
    public $directory_structure = array();	//Directory structure to create as an array
    public $protected_files = array();	//array list of file to NOT over-write on every config file build. They are protected.
    public $copy_files = array();		//array of files or directories to copy. Directories will be recursive
    public $en_htmlspecialchars = TRUE;	//Enable or Disable PHP's htmlspecialchars() function for variables
    public $server_type = 'file';		//Can be file or dynamic
    public $provisioning_type = 'tftp';		//can be tftp,http,ftp ??
    public $enable_encryption = FALSE;		//Enable file encryption
    public $provisioning_path;                  //Path to provisioner, used in http/https/ftp/tftp
    public $dynamic_mapping;		// e.g. ARRAY('thisfile.htm'=>'# Intentionally left blank','thatfile$mac.htm'=>array('thisfile.htm','thatfile$mac.htm'));
									// files not in this array are passed through untouched. Strings are returned as is. For arrays, generate_file is called for each entry, and they are combined.
    public $config_file_replacements=array();

    // Note: these can be override by descendant classes.
    private $server_type_list=array('file','dynamic');  // acceptable values for $server_type
    private $default_server_type='file';		// if server_type is invalid
    private $provisioning_type_list=array('tftp','http','ftp'); //acceptable values for $provisioning_type
    private $default_provisioning_type='tftp';	// if provisioning_type is invalid


    function __construct() {
		$this->root_dir=dirname(dirname(__FILE__))."/";
    }

    public static function get_modules_path() {
        return self::$modules_path;
    }

    public static function set_modules_path($path) {
        self::$modules_path = $path;
    }

    //Initialize all child functions
    function reboot() {

    }

    /**
     * This is hooked into the middle of the line loop function to allow parsing of variables without having to create a sub foreach or for statement
     * @param String $line The Line number.
     */
    function parse_lines_hook($line,$line_total) {

    }

    //Set all default values here and fix errors before they hit us in the ass later on.
    function data_integrity() {
	if (!in_array($this->server_type,$this->server_type_list)) {
		$this->server_type=$this->default_server_type;
	}
	if (!in_array($this->provisioning_type,$this->provisioning_type_list)) {
		$this->provisioning_type=$this->default_provisioning_type;
	}
    }

    function generate_info($file_contents, $brand_ts, $family_ts) {
        if($this->server_type == "file") {
            $file_contents = str_replace('{$provisioner_generated_timestamp}', date('l jS \of F Y h:i:s A'), $file_contents);
        } else {
            $file_contents = str_replace('{$provisioner_generated_timestamp}', 'N/A (Prevents reboot loops if set to static value)', $file_contents);
        }
        $file_contents = str_replace('{$provisioner_processor_info}', $this->processor_info, $file_contents);
        $file_contents = str_replace('{$provisioner_timestamp}', $this->processor_info, $file_contents);
        $file_contents = str_replace('{$provisioner_brand_timestamp}', $brand_ts ." (".date('l jS \of F Y h:i:s A', $brand_ts).")", $file_contents);
        $file_contents = str_replace('{$provisioner_family_timestamp}', $family_ts." (".date('l jS \of F Y h:i:s A', $family_ts).")", $file_contents);
        return($file_contents);
    }

    function setup_ntp() {
        if(!isset($this->ntp)) {
            $this->ntp = $this->server[1]['ip'];
        }
    }

    /**
     * NOTE: Wherever possible, try $this->DateTimeZone->getOffset(new DateTime) FIRST, which takes Daylight savings into account, too.
     * Turns a string like PST-7 or UTC+1 into a GMT offset in seconds
     * @param Send this a timezone like PST-7
     * @return Offset from GMT, in seconds (eg. -25200, =3600*-7)
	 * @author Jort Bloem
     */
    function get_gmtoffset($timezone) {
		# Divide the timezone up into it's 3 interesting parts; the sign (+/-), hours, and if they exist, minutes.
		# note that matches[0] is the entire matched string, so these 3 parts are $matches[1], [2] and [3].
		preg_match('/([\-\+])([\d]+):?(\d*)/',$timezone,$matches);
		# $matches is now an array; $matches[1] is the sign (+ or -); $matches[2] is number of hours, $matches[3] is minutes (or empty)
		return intval($matches[1]."1")*($matches[2]*3600+$matches[3]*60);
    }

    /**
     * Turns an integer like -3600 (seconds) into a GMT offset like GMT-1
     * @param Time offset in seconds, like 3600 or -25200 or -27000
     * @return timezone (eg. GMT+1 or GMT-7 or GMT-7:30)
	 * @author Jort Bloem
     */
    function get_timezone($offset) {
		if ($offset<0) {
			$result="GMT-";
			$offset=abs($offset);
		} else {
			$result="GMT+";
		}
		$result.=(int)($offset/3600);
		if ($result%3600>0) {
			$result.=":".(($offset%3600)/60);
		}
		return $result;
    }

    /**
     * Setup and fill in timezone data
	 * @author Jort Bloem
     */
    function setup_tz() {
		if (isset($this->DateTimeZone)) {
			$this->timezone=array(
				'gmtoffset'=>$this->DateTimeZone->getOffset(new DateTime),
				'timezone' =>$this->get_timezone($this->DateTimeZone->getOffset(new DateTime))
			);
		} elseif(is_array($this->timezone)) {
			#Do nothing
		} elseif (is_numeric($this->timezone)) {
			$this->timezone=array(
				'gmtoffset'=>$this->timezone,
				'timezone'=>$this->get_timezone($this->timezone),
			);
	        } else {
			$this->timezone=array(
				'gmtoffset'=>$this->get_gmtoffset($this->timezone),
				'timezone'=>$this->timezone,
			);
	    }
    }

    /**
     * Override this to do any configuration testing/sorting/preparing
     * Dont forget to call parent::prepare_for_generateconfig if you
     * do override it.
	 * @author Jort Bloem
    **/
    function prepare_for_generateconfig() {
        $this->setup_tz();
        $this->setup_ntp();
    	$this->data_integrity();
	if (!in_array('$mac',$this->config_file_replacements)) {
		$this->config_file_replacements['$mac']=$this->mac;
	}
	if (!in_array('$model',$this->config_file_replacements)) {
		$this->config_file_replacements['$model']=$this->model;
	}
    }

    /**
     * This generates a list of config files, and the files on which they
     * are based.
	 * @author Jort Bloem
     * @return array ($outputfilename=>$sourcefilename,...)
     *		both filenames are strings, sourcefilename may occur more 
     *          than once.
     * override this, if you feel so inclined - you probably want to call
     *    $result=parent::config_files() first, then modify $result as you like.
     *
     * You should call prepare_for_generateconfig() before calling this.
    **/
    function config_files() {
        $family_data = $this->xml2array($this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/family_data.xml",1,'tag',array('model_list'));
	foreach (explode(",",$family_data['data']['configuration_files']) AS $configfile) {
		$outputfile=str_replace(array_keys($this->config_file_replacements),array_values($this->config_file_replacements),$configfile);
		$result[$outputfile]=$configfile;
	}
	return $result;
    }

    /** 
     * Generate one config file. Most settings are taken from $this.
     * This is a good thing to overide.
     * if you do, you can do a first cut by calling 
     *    $result=parent::generate_file, then tweaking the result,
     *    or if ($sourcefile=..) {} else {return parent::generate_file}
     *
     * Note that, if you use dynamic a server type, $filename refers to the
     *    FINAL output file, not the piece that we're generating. In general,
     *    $filename is probably unlikely to be used.
     *
     * You should call prepare_for_generateconfig() before calling this.
	 * @author Jort Bloem
     */
    function generate_file($filename,$extradata,$ignoredynamicmapping=FALSE) {
	# Note: server_type='dynamic' is ignored if ignoredynamicmapping, if there is no $this->dynamic_mapping, or that is not an array.
	if ((!$ignoredynamicmapping) || ($this->server_type!='dynamic') || (!is_array($this->dynamic_mapping)) || (!array_key_exists($extradata,$this->dynamic_mapping))) {
		$data=$this->open_config_file($extradata);
		return $this->parse_config_file($data);
	} elseif (!is_array($this->dynamic_mapping[$extradata])) {
		return $this->dynamic_mapping[$extradata];
	} else {
		$data="";
		foreach ($this->dynamic_mapping[$extradata] AS $recurseextradata) {
			$data.=$this->generate_file($filename,$recurseextradata,TRUE);
		}
		return $data;
	}
    }

    /**
     * generate_config() - this shouldn't need to be overridden.
	 * @author Jort Bloem
     */
    function generate_config() {
	$this->prepare_for_generateconfig();
	$output=array();
	foreach ($this->config_files() AS $filename=>$sourcefile) {
		$output[$filename]=$this->generate_file($filename,$sourcefile);
	}
	return $output;
    }

    /**
     * $type is either gmt or tz
	 * @author Jort Bloem
     */
    function setup_timezone($timezone,$type) {
        if($type == 'GMT') {
            return $this->timezone['gmtoffset'];
        } elseif($type == 'TZ') {
            return $this->timezone['timezone'];
        } else {
            return FALSE;
        }
    }

    function setup_languages() {
        return $languages;
    }

    /**
     * Takes the name of a local configuration file and either returns that file from the hard drive as a string or takes the string from the array and returns that as a string
     * @param string $filename Configuration File name
     * @return string Full Configuration File (From Hard Drive or Array)
     * @example
     * <code>
     * 	$full_file = $this->open_config_file("local_file.cfg");
     * </code>
     * @author Andrew Nagy
     */
    function open_config_file($filename) {
        $this->data_integrity();
        //if there is no configuration file over ridding the default then load up $contents with the file's information, where $key is the name of the default configuration file
        if (!isset($this->config_files_override[$filename])) {
            return file_get_contents($this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/" . $filename);
        } else {
            return($this->config_files_override[$filename]);
        }
    }

    /**
     * This will parse configuration values that are either {$variable}, {$variable|default}, {$variable.line.num}, or {$variable.line.num|default}
     * It will determine the line ammount and then run the function to parse lines and then run parse config values (to replace any remaining values)
     * @param string $file_contents full contents of the configuration file
     * @param boolean $keep_unknown Keep Unknown variables as {$variable} instead of erasing them (blanking the space), can be used to parse these variables later
     * @param integer $lines The total number of lines for this model, NULL if defining a model
     * @param integer $specific_line The specific line number to manipulate. If no line number set then assume All Lines
     * @return string Full Contents of the configuration file (After Parsing)
     * @author Andrew Nagy
     */
    function parse_config_file($file_contents, $keep_unknown=FALSE, $lines=NULL, $specific_line='ALL') {
        $family_data = $this->xml2array($this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/family_data.xml",1,'tag',array('model_list'));
        $brand_data = $this->xml2array($this->root_dir. self::$modules_path . $this->brand_name . "/brand_data.xml");

        //Get number of lines for this model from the family_data.xml file
        if (is_array($family_data['data']['model_list'])) {
            $key = $this->arraysearchrecursive($this->model, $family_data, "model");
            $line_total = $family_data['data']['model_list'][$key[2]]['lines'];
        } else {
            $line_total = $family_data['data']['model_list']['lines'];
        }


        if (($line_total <= 0) AND (!isset($lines))) {
            //There is no max number of lines for this phone. We default to 1 to be safe
            $line_total = 1;
        } elseif ((isset($lines)) AND ($lines > 0)) {
            $line_total = $lines;
        }

        $this->setup_tz();
        $this->setup_ntp();

        if(empty($this->engine_location)) {
			if($this->engine == 'asterisk') {
				$this->engine_location = 'asterisk';
			} elseif($this->engine == 'freeswitch') {
				$this->engine_location = 'freeswitch';
			}
        }

        $this->timezone['gmtoffset'] = $this->setup_timezone($this->timezone['gmtoffset'], 'GMT');
        $this->timezone['timezone'] = $this->setup_timezone($this->timezone['timezone'], 'TZ');

        if (array_key_exists('0', $brand_data['data']['brands']['family_list']['family'])) {
            $key = $this->arraysearchrecursive($family_data['data']['directory'], $brand_data['data']['brands']['family_list'], "directory");
            $brand_mod = $brand_data['data']['brands']['family_list']['family'][$key[1]]['last_modified'];
        } else {
            $brand_mod = $brand_data['data']['brands']['family_list']['family']['last_modified'];
        }

        $file_contents = $this->generate_info($file_contents, $brand_data['data']['brands']['last_modified'], $brand_mod);

        $file_contents = $this->parse_conditional_model($file_contents);
        $file_contents = $this->parse_lines($line_total, $file_contents, $keep_unknown, $specific_line);
        $file_contents = $this->parse_loops($line_total,$file_contents, $keep_unknown, $specific_line);
        $file_contents = $this->parse_config_values($file_contents);

        return $file_contents;
    }

    /**
     * Simple Model if then statement, should be called before any parsing!
     * @param string $file_contents Full Contents of the configuration file
     * @return string Full Contents of the configuration file (After Parsing)
     * @example {if model="6757*"}{/if}
     * @author Andrew Nagy
     */
    function parse_conditional_model($file_contents) {
        $pattern = "/{if model=\"(.*?)\"}(.*?){\/if}/si";
        while (preg_match($pattern, $file_contents, $matches)) {
            //This is exactly like the fnmatch function except it will work on POSIX compliant systems
            //http://php.net/manual/en/function.fnmatch.php
            if(preg_match("#^".strtr(preg_quote($matches[1], '#'), array('\*' => '.*', '\?' => '.', '\[' => '[', '\]' => ']'))."$#i", $this->model)) {
                $file_contents = preg_replace($pattern, $matches[2], $file_contents, 1);
            } else {
                $file_contents = preg_replace($pattern, "", $file_contents, 1);
            }
        }
        return($file_contents);
    }

    /**
     * Parse data between {loop_*}{/loop_*}
     * @param string $line_total Total Number of Lines on the specific Phone
     * @param string $file_contents Full Contents of the configuration file
     * @param boolean $keep_unknown Keep Unknown variables as {$variable} instead of erasing them (blanking the space), can be used to parse these variables later
     * @param integer $specific_line The specific line number to manipulate. If no line number set then assume All Lines
     * @return string Full Contents of the configuration file (After Parsing)
     * @example {loop_keys}{/loop_keys}
     * @author Andrew Nagy
     */
    function parse_loops($line_total, $file_contents, $keep_unknown=FALSE, $specific_line='ALL') {
        //Find line looping data betwen {line_loop}{/line_loop}
        $pattern = "/{loop_(.*?)}(.*?){\/loop_(.*?)}/si";
        while (preg_match($pattern, $file_contents, $matches)) {
            if(isset($this->options[$matches[3]])) {
                $count = count($this->options[$matches[3]]);
                $parsed = "";
                if($count) {
                    foreach($this->options[$matches[3]] as $number => $data) {
                        $data['number'] = $number;
						$data['count'] = $number;
                        $parsed .= $this->parse_config_values($matches[2], FALSE, "GLOBAL", $data);
                    }
                }
                $file_contents = preg_replace($pattern, $parsed, $file_contents, 1);
            } else {
                $file_contents = preg_replace($pattern, "", $file_contents, 1);
            }
        }
        return($file_contents);
    }

    /**
     * Parse each individual line through use of {$variable.line.num} or {line_loop}{/line_loop}
     * @param string $line_total Total Number of Lines on the specific Phone
     * @param string $file_contents Full Contents of the configuration file
     * @param boolean $keep_unknown Keep Unknown variables as {$variable} instead of erasing them (blanking the space), can be used to parse these variables later
     * @param integer $specific_line The specific line number to manipulate. If no line number set then assume All Lines
     * @return string Full Contents of the configuration file (After Parsing)
     * @author Andrew Nagy
     */
    function parse_lines($line_total, $file_contents, $keep_unknown=FALSE, $specific_line='ALL') {
        //Find line looping data betwen {line_loop}{/line_loop}
        $pattern = "/{line_loop}(.*?){\/line_loop}/si";
        while (preg_match($pattern, $file_contents, $matches)) {
            $i = 1;
            $parsed = "";
            //If specific line is set to ALL then loop through all lines
            if ($specific_line == "ALL") {
                while ($i <= $line_total) {
                    $this->parse_lines_hook($i,$line_total);
                    if (isset($this->lines[$i]['secret'])) {
                        $parsed_2 = $this->replace_static_variables($matches[1], $i, TRUE);
                        $parsed .= $this->parse_config_values($parsed_2, FALSE, $i);
                        //echo $parsed;
                    }
                    $i++;
                }
                //If Specific Line is set to a number greater than 0 then only process the loop for that line
            } else {
                $this->parse_lines_hook($specific_line,$line_total);
                $parsed_2 = $this->replace_static_variables($matches[1], $specific_line, TRUE);
                $parsed = $this->parse_config_values($parsed_2, TRUE, $specific_line);
            }
            $file_contents = preg_replace($pattern, $parsed, $file_contents, 1);
        }


        //If secret is set for said line then assume it's active and pull it's data but only if said line can be used on the phone
        //This will replace {$variable.line.num}
        $i = 1;
        while ($i <= $line_total) {
            if (isset($this->lines[$i]['secret'])) {
                $file_contents = $this->replace_static_variables($file_contents, $i, FALSE);
            }
            $i++;
        }

        $file_contents = $this->replace_static_variables($file_contents, "GLOBAL");

        return($file_contents);
    }

    /**
     * This function will replace variables, eg {$variable}
     * @param string $file_contents Full Contents of the configuration file
     * @param boolean $keep_unknown Keep Unknown variables as {$variable} instead of erasing them (blanking the space), can be used to parse these variables later
     * @param string $specific_line_master The specific line number to manipulate. If no line number set then assume GLOBAL variable. This will reset back to whatever it was sent for every variable
     * @param string $options
     * @return string
     * @author Andrew Nagy
     */
    function parse_config_values($file_contents, $keep_unknown=FALSE, $specific_line_master="GLOBAL", $options=NULL) {
        if(!isset($options)) {
            $options=$this->options;
        }
        $family_data = $this->xml2array($this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/family_data.xml",1,'tag',array('model_list'));

        if (is_array($family_data['data']['model_list'])) {
            $key = $this->arraysearchrecursive($this->model, $family_data, "model");
            if ($key === FALSE) {
                die("You need to specify a valid model. Or change how this function works (line 110 of base.php)");
            } else {
                $template_data_list = $family_data['data']['model_list'][$key[2]]['template_data'];
            }
        } else {
            $template_data_list = $family_data['data']['model_list']['template_data'];
        }

        $template_data = array();
        $template_data_multi = "";
        if (is_array($template_data_list['files'])) {
            foreach ($template_data_list['files'] as $files) {
                if (file_exists($this->root_dir.self::$modules_path . $this->brand_name . "/" . $this->family_line . "/" . $files)) {
                    $template_data_multi = $this->xml2array($this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/" . $files);
                    $template_data_multi = $this->fix_single_array_keys($template_data_multi['template_data']['category']);
                    foreach($template_data_multi as $categories) {
                        $subcats = $this->fix_single_array_keys($categories['subcategory']);
                        foreach($subcats as $subs) {
                            $items = $this->fix_single_array_keys($subs['item']);
                            $template_data = array_merge($template_data, $items);
                        }
                    }
                }
            }
        } else {
            if (file_exists($this->root_dir.self::$modules_path . $this->brand_name . "/" . $this->family_line . "/" . $template_data_list['files'])) {
                $template_data_multi = $this->xml2array($this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/" . $template_data_list['files']);
                $template_data_multi = $this->fix_single_array_keys($template_data_multi['template_data']['category']);
                foreach($template_data_multi as $categories) {
                    $subcats = $this->fix_single_array_keys($categories['subcategory']);
                    foreach($subcats as $subs) {
                        $items = $this->fix_single_array_keys($subs['item']);
                        $template_data = array_merge($template_data, $items);
                    }
                }
            }
        }

        if (file_exists($this->root_dir.self::$modules_path . $this->brand_name . "/" . $this->family_line . "/template_data_custom.xml")) {
            $template_data_multi = $this->xml2array($this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/template_data_custom.xml");
            $template_data_multi = $this->fix_single_array_keys($template_data_multi['template_data']['category']);
            foreach($template_data_multi as $categories) {
                $subcats = $this->fix_single_array_keys($categories['subcategory']);
                foreach($subcats as $subs) {
                    $items = $this->fix_single_array_keys($subs['item']);
                    $template_data = array_merge($template_data, $items);
                }
            }
        }

        if (file_exists($this->root_dir.self::$modules_path . $this->brand_name . "/" . $this->family_line . "/template_data_" . $this->model . "_custom.xml")) {
            $template_data_multi = $this->xml2array($this->root_dir. self::$modules_path . $this->brand_name . "/" . $this->family_line . "/template_data_" . $this->model . "_custom.xml");
            $template_data_multi = $this->fix_single_array_keys($template_data_multi['template_data']['category']);
            foreach($template_data_multi as $categories) {
                $subcats = $this->fix_single_array_keys($categories['subcategory']);
                foreach($subcats as $subs) {
                    $items = $this->fix_single_array_keys($subs['item']);
                    $template_data = array_merge($template_data, $items);
                }
            }
        }

        //Find all matched variables in the text file between "{$" and "}"
        preg_match_all('/[{\$](.*?)[}]/i', $file_contents, $match);
        //Result without brackets (but with the $ variable identifier)
        $no_brackets = array_values(array_unique($match[1]));
        //Result with brackets
        $brackets = array_values(array_unique($match[0]));


        //loop though each variable found in the text file
        foreach ($no_brackets as $variables) {
            $variables = str_replace("$", "", $variables);
            $specific_line = $specific_line_master;
            //Users can set defaults within template files with pipes, they will over-ride whatever is in the XML file.
            if (strstr($variables, "|")) {
                $original_variable = $variables;
                $variables = explode("|", $variables,2);
                $default = $variables[1];
                $variables = $variables[0];
                if (strstr($variables, ".")) {
                    $original_variable = $variables;
                    $variables = explode(".", $variables);
                    $specific_line = $variables[2];
                    $variables = $variables[0];
                } else {
                    $original_variable = $variables;
                }
            } else {
                unset($default);
                $original_variable = $variables;
                if (strstr($variables, ".")) {
                    $original_variable = $variables;
                    $variables = explode(".", $variables);
                    $specific_line = $variables[2];
                    $variables = $variables[0];
                }
            }

            //If the variable we found in the text file exists in the variables array then replace the variable in the text file with the value under our key
            if (($specific_line == "GLOBAL") AND (isset($options[$variables]))) {
                if($this->en_htmlspecialchars) {
                    $options[$variables] = htmlspecialchars($options[$variables]);
                } else {
                    $options[$variables] = $options[$variables];
                }
                $options[$variables] = $this->replace_static_variables($options[$variables]);
                if (isset($default)) {
                    $file_contents = str_replace('{$' . $original_variable . '|' . $default . '}', $options[$variables], $file_contents);
                } else {
                    $file_contents = str_replace('{$' . $original_variable . '}', $options[$variables], $file_contents);
                }
            } elseif (($specific_line != "GLOBAL") AND (isset($this->lines[$specific_line]['options'][$variables]))) {
                if($this->en_htmlspecialchars) {
                    $this->lines[$specific_line]['options'][$variables] = htmlspecialchars($this->lines[$specific_line]['options'][$variables]);
                } else {
                    $this->lines[$specific_line]['options'][$variables] = $this->lines[$specific_line]['options'][$variables];
                }

                $this->lines[$specific_line]['options'][$variables] = $this->replace_static_variables($this->lines[$specific_line]['options'][$variables]);
                if (isset($default)) {
                    $file_contents = str_replace('{$' . $original_variable . '|' . $default . '}', $this->lines[$specific_line]['options'][$variables], $file_contents);
                } else {
                    $file_contents = str_replace('{$' . $original_variable . '}', $this->lines[$specific_line]['options'][$variables], $file_contents);
                }
            } else {
                if (!$keep_unknown) {
                    //read default template values here, blank unknowns or arrays (which are blanks anyways)
                    $key1 = $this->arraysearchrecursive('$' . $variables, $template_data, 'variable');
                    $default_hard_value = NULL;

                    //Check for looping statements. They are all setup logically the same. Ergo if the first multi-dimensional array has a variable key its not a loop.
                    if($key1['1'] == 'variable') {
                        $default_hard_value = $this->replace_static_variables($this->fix_single_array_keys($template_data[$key1[0]]['default_value']));
                    } elseif($key1['4'] == 'variable') {

                        //replace count variable with line number
                        $template_data[$key1[0]][$key1[1]][$key1[2]][$key1[3]]['default_value'] = str_replace('{$count}', $specific_line, $template_data[$key1[0]][$key1[1]][$key1[2]][$key1[3]]['default_value']);
                        $template_data[$key1[0]][$key1[1]][$key1[2]][$key1[3]]['default_value'] = str_replace('{$number}', $specific_line, $template_data[$key1[0]][$key1[1]][$key1[2]][$key1[3]]['default_value']);

                        $default_hard_value = $this->replace_static_variables($this->fix_single_array_keys($template_data[$key1[0]][$key1[1]][$key1[2]][$key1[3]]['default_value']));
                    }

                    if (isset($default)) {
                        $file_contents = str_replace('{$' . $original_variable . '|' . $default . '}', $default, $file_contents);
                    } elseif (isset($default_hard_value)) {
                        $file_contents = str_replace('{$' . $original_variable . '}', $default_hard_value, $file_contents);
                    } else {
                        $file_contents = str_replace('{$' . $original_variable . '}', "", $file_contents);
                    }
                }
            }
        }


        return $file_contents;
    }

    /**
     * This will replace statically known variables
     * variables: {$server.ip.*}, {$server.port.*}, {$mac}, {$model}, {$line}, {$ext}, {$displayname}, {$secret}, {$pass}, etc.
     * @param string $contents
     * @param string $specific_line
     * @param boolean $looping
     * @return string
     */
    function replace_static_variables($contents, $specific_line="GLOBAL", $looping=TRUE) {
        foreach($this->server as $key => $servers) {
            $contents = str_replace('{$server.ip.'.$key.'}', $servers['ip'], $contents);
            $contents = str_replace('{$server.port.'.$key.'}', $servers['port'], $contents);
        }
        if(isset($this->proxy)) {
            foreach($this->proxy as $key => $proxies) {
                $contents = str_replace('{$proxy.ip.'.$key.'}', $proxies['ip'], $contents);
                $contents = str_replace('{$proxy.port'.$key.'}', $proxies['port'], $contents);
            }
        }
        $contents = str_replace('{$mac}', $this->mac, $contents);
        $contents = str_replace('{$model}', $this->model, $contents);
        $contents = str_replace('{$timezone_gmtoffset}', $this->timezone['gmtoffset'], $contents);
        $contents = str_replace('{$timezone_timezone}', $this->timezone['timezone'], $contents);
        $contents = str_replace('{$timezone}', $this->timezone['timezone'], $contents);
        $contents = str_replace('{$network_time_server}', $this->ntp, $contents);
		$contents = str_replace('{$provisioning_type}', $this->provisioning_type, $contents);
		$contents = str_replace('{$provisioning_path}', $this->provisioning_path, $contents);
		
        //Depreciated
        $contents = str_replace('{$gmtoff}', $this->timezone['gmtoffset'], $contents);
        $contents = str_replace('{$gmthr}', $this->timezone['gmtoffset'], $contents);
        if (($specific_line != "GLOBAL") AND ($looping == TRUE)) {
            $contents = str_replace('{$line}', $specific_line, $contents);
            $contents = str_replace('{$ext}', $this->lines[$specific_line]['ext'], $contents);
            $contents = str_replace('{$displayname}', $this->lines[$specific_line]['displayname'], $contents);
            $contents = str_replace('{$secret}', $this->lines[$specific_line]['secret'], $contents);
            $contents = str_replace('{$pass}', $this->lines[$specific_line]['secret'], $contents);
        } elseif (($specific_line != "GLOBAL") AND ($looping == FALSE)) {
            $contents = str_replace('{$line.line.' . $specific_line . '}', $specific_line, $contents);
            $contents = str_replace('{$ext.line.' . $specific_line . '}', $this->lines[$specific_line]['ext'], $contents);
            $contents = str_replace('{$displayname.line.' . $specific_line . '}', $this->lines[$specific_line]['displayname'], $contents);
            $contents = str_replace('{$secret.line.' . $specific_line . '}', $this->lines[$specific_line]['secret'], $contents);
            $contents = str_replace('{$pass.line.' . $specific_line . '}', $this->lines[$specific_line]['secret'], $contents);
        } elseif ($specific_line == 'GLOBAL') {
            //Find all matched variables in the text file between "{$" and "}"
            preg_match_all('/[{\$](.*?)[}]/i', $contents, $match);
            //Result without brackets (but with the $ variable identifier)
            $no_brackets = array_values(array_unique($match[1]));
            //Result with brackets
            $brackets = array_values(array_unique($match[0]));
            //loop though each variable found in the text file
            foreach ($no_brackets as $variables) {
                $variables = str_replace("$", "", $variables);
                $original_variable = $variables;
                if (strstr($variables, ".")) {
                    $original_variable = $variables;
                    $variables = explode(".", $variables);
                    $specific_line = $variables[2];
                    $variables = $variables[0];
                    switch ($variables) {
                        case "ext":
                            if(isset($this->lines[$specific_line]['ext'])) {
                                $contents = str_replace('{$ext.line.' . $specific_line . '}', $this->lines[$specific_line]['ext'], $contents);
                            } else {
                                $contents = str_replace('{$ext.line.' . $specific_line . '}', '', $contents);
                            }
                            break;
                        case "displayname":
                            if(isset($this->lines[$specific_line]['displayname'])) {
                                $contents = str_replace('{$displayname.line.' . $specific_line . '}', $this->lines[$specific_line]['displayname'], $contents);
                            } else {
                                $contents = str_replace('{$displayname.line.' . $specific_line . '}', '', $contents);
                            }
                            break;
                        case "secret":
                            if(isset($this->lines[$specific_line]['secret'])) {
                                $contents = str_replace('{$secret.line.' . $specific_line . '}', $this->lines[$specific_line]['secret'], $contents);
                            } else {
                                $contents = str_replace('{$secret.line.' . $specific_line . '}', '', $contents);
                            }
                            break;
                        case "pass":
                            if(isset($this->lines[$specific_line]['secret'])) {
                                $contents = str_replace('{$pass.line.' . $specific_line . '}', $this->lines[$specific_line]['secret'], $contents);
                            } else {
                                $contents = str_replace('{$pass.line.' . $specific_line . '}', '', $contents);
                            }
                            break;
                    }
                }
            }
        }
        return($contents);
    }

    /**
     * Merge two arrays only if the old array is an array, otherwise just return the new array
     * @param array $array_old
     * @param array $array_new
     * @return array
     * @deprecated
     */
    function array_merge_check($array_old, $array_new) {
        if (is_array($array_old)) {
            return(array_merge($array_old, $array_new));
        } else {
            return($array_new);
        }
    }

    /**
     * Function xml2array has a bad habit of returning blank xml values as empty arrays.
     * Also if the xml children only loops once then the array is put into a normal array (array[variable]).
     * However if it loops more than once then it is put into a counted array (array[0][variable])
     * We fix that issue here by returning blank values on empty arrays or always returning array[0]
     * @param array $array
     * @return mixed
     * @author Karl Anderson
     */
    function fix_single_array_keys($array) {
        if (!is_array($array)) {
            return $array;
        }

        if((empty($array[0])) AND (!empty($array))) {
            $array_n[0] = $array;

            return($array_n);
        }

        return empty($array) ? '' : $array;

        /*
        if((empty($array[0])) AND (!empty($array))) {
            $array_n[0] = $array;
            return($array_n);
        } elseif(!empty($array)) {
            return($array);
        //This is so stupid?! PHP gets confused.
        } elseif($array == '0') {
            return($array);
        } else {
            return("");
        }
         * *
        */
    }

    /**
     * Search Recursively through an array
     * @param string $Needle
     * @param array $Haystack
     * @param string $NeedleKey
     * @param boolean $Strict
     * @param array $Path
     * @return array
     */
    function arraysearchrecursive($Needle, $Haystack, $NeedleKey="", $Strict=false, $Path=array()) {
        if (!is_array($Haystack))
            return false;
        foreach ($Haystack as $Key => $Val) {
            if (is_array($Val) &&
                    $SubPath = $this->arraysearchrecursive($Needle, $Val, $NeedleKey, $Strict, $Path)) {
                $Path = array_merge($Path, Array($Key), $SubPath);
                return $Path;
            } elseif ((!$Strict && $Val == $Needle &&
                            $Key == (strlen($NeedleKey) > 0 ? $NeedleKey : $Key)) ||
                    ($Strict && $Val === $Needle &&
                            $Key == (strlen($NeedleKey) > 0 ? $NeedleKey : $Key))) {
                $Path[] = $Key;
                return $Path;
            }
        }
        return false;
    }

    /**
     * xml2array() will convert the given XML text to an array in the XML structure.
     * @author http://www.bin-co.com/php/scripts/xml2array/
     * @param <type> $url The XML file
     * @param <type> $get_attributes 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
     * @param <type> $priority Can be 'tag' or 'attribute'. This will change the way the resulting array structure. For 'tag', the tags are given more importance.
     * @param <type> $array_tags - any tag names listed here will allways be returned as an array, even if there is only one of them.
     * @return <type> The parsed XML in an array form. Use print_r() to see the resulting array structure.
     */
    function xml2array($url, $get_attributes = 1, $priority = 'tag', $array_tags=array()) {
        $contents = "";
        if (!function_exists('xml_parser_create')) {
            return array();
        }
        $parser = xml_parser_create('');
        if (!($fp = @ fopen($url, 'rb'))) {
            return array();
        }
        while (!feof($fp)) {
            $contents .= fread($fp, 8192);
        }
        fclose($fp);
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);
        if (!$xml_values) {
            return; //Hmm...
        }
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();
        $current = & $xml_array;
        $repeated_tag_index = array();
        foreach ($xml_values as $data) {
            unset($attributes, $value);
            extract($data);
            $result = array();
            $attributes_data = array();
            if (isset($value)) {
                if ($priority == 'tag') {
                    $result = $value;
                } else {
                    $result['value'] = $value;
                }
            }
            if (isset($attributes) and $get_attributes) {
                foreach ($attributes as $attr => $val) {
                    if ($priority == 'tag') {
                        $attributes_data[$attr] = $val;
                    } else {
                        $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                    }
                }
            }
            if ($type == "open") {
                $parent[$level - 1] = & $current;
		if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
		    if (in_array($tag,$array_tags)) {
                        $current[$tag][0] = $result;
                        $repeated_tag_index[$tag . '_' . $level]=1;
                    	$current = & $current[$tag][0];
		    } else {
			$current[$tag] = $result;
			if ($attributes_data) {
				$current[$tag . '_attr'] = $attributes_data;
			}
			$repeated_tag_index[$tag . '_' . $level] = 1;
			$current = & $current[$tag];
		   }
                } else {
                    if (isset($current[$tag][0])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array($current[$tag], $result);
                        $repeated_tag_index[$tag . '_' . $level] = 2;
                        if (isset($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = & $current[$tag][$last_item_index];
                }
            } else if ($type == "complete") {
                if (!isset($current[$tag])) {
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributes_data) {
                        $current[$tag . '_attr'] = $attributes_data;
                    }
                } else {
                    if (isset($current[$tag][0]) and is_array($current[$tag])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array($current[$tag], $result);
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $get_attributes) {
                            if (isset($current[$tag . '_attr'])) {
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }
                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                    }
                }
            } else if ($type == 'close') {
                $current = & $parent[$level - 1];
            }
        }
        return ($xml_array);
    }
}
