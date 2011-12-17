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
    public static $root_dir = "";  //need to define the root directory for the location of the library (/var/www/html/)
    
    public $brand_name = "undefined";   //Brand Name
    public $family_line = "undefined";  //Family Line
    public $model = "undefined";        // Model of phone, must match the model name inside of the famil_data.json file in each family folder.
    public $config_files_override;  //Array list of config files to override, data being the contents, key being the name of said file
    public $settings = array();
    public $debug = FALSE;  //Enable or disable debug
    public $debug_return = array(); //Debug fill. I question if this is needed, or perhaps remove above line, seems redudant to have both 
    public $mac;    // Device mac address, should this be in settings?
    public $timezone = array();       // Global timezone array
    public $DateTimeZone;   // timezone, as a DateTimezone object, much more flexible than just an offset and name.
    public $engine;   //Can be asterisk or freeswitch. This is for the reboot commands.
    public $engine_location = ""; //Location of the executable for said engine above
    public $system;   //unix or windows or bsd. etc
    public $directory_structure = array(); //Directory structure to create as an array
    public $protected_files = array(); //array list of file to NOT over-write on every config file build. They are protected.
    public $copy_files = array();  //array of files or directories to copy. Directories will be recursive
    
    protected $en_htmlspecialchars = TRUE; //Enable or Disable PHP's htmlspecialchars() function for variables
    protected $server_type = 'file';  //Can be file or dynamic
    protected $provisioning_type = 'tftp';  //can be tftp,http,ftp ??
    protected $enable_encryption = FALSE;  //Enable file encryption
    protected $provisioning_path;                  //Path to provisioner, used in http/https/ftp/tftp
    protected $dynamic_mapping;  // e.g. ARRAY('thisfile.htm'=>'# Intentionally left blank','thatfile$mac.htm'=>array('thisfile.htm','thatfile$mac.htm'));
    // files not in this array are passed through untouched. Strings are returned as is. For arrays, generate_file is called for each entry, and they are combined.
    protected $config_file_replacements = array();
    protected $config_files = array();
    protected $brand_data;    //Brand Data file in array form
    protected $family_data;   //family data file in array form
    protected $model_data;    //model data from family data in array form
    protected $template_data; //Merged template files for specified model in array form
    protected $max_lines = array();   //Max lines from said model.
    
    private $server_type_list = array('file', 'dynamic');  // acceptable values for $server_type
    private $default_server_type = 'file';  // if server_type is invalid
    private $provisioning_type_list = array('tftp', 'http', 'ftp'); //acceptable values for $provisioning_type
    private $default_provisioning_type = 'tftp'; // if provisioning_type is invalid
    
    private $initialized = FALSE;   //Initialized data or not.

    function __construct() {
        self::$root_dir = dirname(dirname(__FILE__)) . "/";
    }

    /*     * *PUBLIC FUNCTIONS** */
    /* These can be called from outside the class */

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
     * @author Jort Bloem
     */
    public function generate_file($filename, $extradata, $ignoredynamicmapping=FALSE,$prepare=FALSE) {
        if($prepare) {
            $this->prepare_for_generateconfig();
        }
        # Note: server_type='dynamic' is ignored if ignoredynamicmapping, if there is no $this->dynamic_mapping, or that is not an array.
        if (($ignoredynamicmapping) || ($this->server_type != 'dynamic') || (!is_array($this->dynamic_mapping)) || (!array_key_exists($extradata, $this->dynamic_mapping))) {
            $data = $this->open_config_file($extradata);
            return $this->parse_config_file($data);
        } elseif (!is_array($this->dynamic_mapping[$extradata])) {
            return $this->dynamic_mapping[$extradata];
        } else {
            $data = "";
            foreach ($this->dynamic_mapping[$extradata] AS $recurseextradata) {
                $data.=$this->generate_file($filename, $recurseextradata, TRUE);
            }
            return $data;
        }
    }

    /**
     * generate_config() - this shouldn't need to be overridden.
     * @author Jort Bloem
     */
    public function generate_all_files() {
        $this->prepare_for_generateconfig();
        $output = array();
        foreach ($this->config_files() AS $filename => $sourcefile) {
            $output[$filename] = $this->generate_file($filename, $sourcefile,FALSE,FALSE);
        }
        return $output;
    }

    /**
     * 
     */
    public function reboot() {
        
    }

    public function __toString() {
        
    }

    public function __invoke($x) {
        
    }

    /*     * *INTERNAL FUNCTIONS** */
    /* These can only be called from within the parent or child classes */

    /*     * *ALL HOOKS BELOW** */

    /**
     * This is hooked into the middle of the line loop function to allow parsing of variables without having to create a sub foreach or for statement
     * @param String $line The Line number.
     */
    protected function parse_lines_hook($line_data, $line_total) {
        return($line_data);
    }

    /**
     * This generates a list of config files, and the files on which they
     * are based.
     * @author Jort Bloem
     * @return array ($outputfilename=>$sourcefilename,...)
     * 		both filenames are strings, sourcefilename may occur more 
     *          than once.
     * override this, if you feel so inclined - you probably want to call
     *    $result=parent::config_files() first, then modify $result as you like.
     *
     * You should call prepare_for_generateconfig() before calling this.
     * */
    protected function config_files() {
        foreach (explode(",", $this->family_data['data']['configuration_files']) AS $configfile) {
            $outputfile = str_replace(array_keys($this->config_file_replacements), array_values($this->config_file_replacements), $configfile);
            $result[$outputfile] = $configfile;
        }
        return $result;
    }

    /**
     * Override this to do any configuration testing/sorting/preparing
     * Dont forget to call parent::prepare_for_generateconfig if you
     * do override it.
     * @author Jort Bloem
     * */
    protected function prepare_for_generateconfig() {
        $this->initialize();
        if (!in_array('$mac', $this->config_file_replacements)) {
            $this->config_file_replacements['$mac'] = $this->mac;
        }
        if (!in_array('$model', $this->config_file_replacements)) {
            $this->config_file_replacements['$model'] = $this->model;
        }
    }

    /**
     * $type is either gmt or tz
     * @author Jort Bloem
     */
    private function setup_timezone($timezone, $type) {
        if ($type == 'GMT') {
            return $this->timezone['gmtoffset'];
        } elseif ($type == 'TZ') {
            return $this->timezone['timezone'];
        } else {
            return FALSE;
        }
    }

    private function setup_languages() {
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
    private function open_config_file($filename) {
        //if there is no configuration file over ridding the default then load up $contents with the file's information, where $key is the name of the default configuration file
        if (!isset($this->config_files_override[$filename])) {
            return file_get_contents(self::$root_dir . self::$modules_path . $this->brand_name . "/" . $this->family_line . "/" . $filename);
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
    private function parse_config_file($file_contents) {
        //TODO: why is this in here?
        $this->timezone['gmtoffset'] = $this->setup_timezone($this->timezone['gmtoffset'], 'GMT');
        $this->timezone['timezone'] = $this->setup_timezone($this->timezone['timezone'], 'TZ');

        $file_contents = $this->generate_info($file_contents);

        $file_contents = $this->parse_conditional_model($file_contents);

        $file_contents = $this->parse_lines($this->max_lines, $file_contents, TRUE);
        $file_contents = $this->parse_loops($this->max_lines, $file_contents, TRUE);

        $file_contents = $this->replace_static_variables($file_contents);
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
    private function parse_conditional_model($file_contents) {
        $pattern = "/{if model=\"(.*?)\"}(.*?){\/if}/si";
        while (preg_match($pattern, $file_contents, $matches)) {
            //This is exactly like the fnmatch function except it will work on POSIX compliant systems
            //http://php.net/manual/en/function.fnmatch.php
            if (preg_match("#^" . strtr(preg_quote($matches[1], '#'), array('\*' => '.*', '\?' => '.', '\[' => '[', '\]' => ']')) . "$#i", $this->model)) {
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
    private function parse_loops($line_total, $file_contents, $keep_unknown=FALSE) {
        //Find line looping data betwen {line_loop}{/line_loop}
        $pattern = "/{loop_(.*?)}(.*?){\/loop_(.*?)}/si";
        while (preg_match($pattern, $file_contents, $matches)) {
            $loop_name = $matches[3];
            $loop_contents = $matches[2];
            //TODO: This should be $this->settings['loop'][$loop_name]
            if (isset($this->settings['loops'][$loop_name])) {
                $count = count($this->settings['loops'][$loop_name]);
                $this->debug("Replacing loop '" . $loop_name . "' " . $count . " times");
                $parsed = "";
                if ($count) {
                    foreach ($this->settings['loops'][$loop_name] as $number => $data) {
                        $data['number'] = $number;
                        $data['count'] = $number;
                        $parsed .= $this->parse_config_values($this->replace_static_variables($loop_contents), $data, FALSE);
                    }
                }
                $file_contents = preg_replace($pattern, $parsed, $file_contents, 1);
            } else {
                $file_contents = preg_replace($pattern, "", $file_contents, 1);
                $this->debug("Blanking loop '" . $loop_name . "'");
            }
        }
        return($file_contents);
    }

    private function find_model($family_data) {
        if (is_array($family_data['data']['model_list'])) {
            $key = $this->arraysearchrecursive($this->model, $family_data, "model");
            if ($key !== FALSE) {
                return($family_data['data']['model_list'][$key[2]]);
            }
        }
        throw new Exception('Could Not find model');
    }

    /**
     * Parse each individual line through use of {$variable.line.num} or {line_loop}{/line_loop}
     * @param string $line_total Total Number of Lines on the specific Phone
     * @param string $file_contents Full Contents of the configuration file
     * @param boolean $keep_unknown Keep Unknown variables as {$variable} instead of erasing them (blanking the space), can be used to parse these variables later
     * @return string Full Contents of the configuration file (After Parsing)
     * @author Andrew Nagy
     */
    private function parse_lines($line_total, $file_contents, $keep_unknown=FALSE) {
        //Find line looping data betwen {line_loop}{/line_loop}
        $pattern = "/{line_loop}(.*?){\/line_loop}/si";
        while (preg_match($pattern, $file_contents, $matches)) {
            $loop_contents = $matches[1];
            $parsed = "";
            foreach ($this->settings['line'] as $key => $data) {
                $line = $data['line'];
                $line_settings = $this->parse_lines_hook($this->settings['line'][$key], $line_total); //This is after parse_lines_hook, because that function could change these values.
                $parsed .= $this->parse_config_values($this->replace_static_variables($loop_contents, $line_settings), $line_settings, $keep_unknown);
            }
            $file_contents = preg_replace($pattern, $parsed, $file_contents, 1);
        }
        return($file_contents);
    }

    private function merge_files() {
        $template_data_list = $this->model_data['template_data'];

        $template_data = array();
        $template_data_multi = "";
        foreach ($template_data_list as $files) {
            if (file_exists(self::$root_dir . self::$modules_path . $this->brand_name . "/" . $this->family_line . "/" . $files)) {
                $template_data_multi = $this->file2json(self::$root_dir . self::$modules_path . $this->brand_name . "/" . $this->family_line . "/" . $files);
                $template_data_multi = $template_data_multi['template_data']['category'];
                foreach ($template_data_multi as $categories) {
                    $subcats = $categories['subcategory'];
                    foreach ($subcats as $subs) {
                        $items = $subs['item'];
                        $template_data = array_merge($template_data, $items);
                    }
                }
            } else {
                throw new Exception("Template File: ".$files." doesnt exist!");
            }
        }


        if (file_exists(self::$root_dir . self::$modules_path . $this->brand_name . "/" . $this->family_line . "/template_data_custom.json")) {
            $template_data_multi = $this->file2json(self::$root_dir . self::$modules_path . $this->brand_name . "/" . $this->family_line . "/template_data_custom.json");
            $template_data_multi = $template_data_multi['template_data']['category'];
            foreach ($template_data_multi as $categories) {
                $subcats = $categories['subcategory'];
                foreach ($subcats as $subs) {
                    $items = $subs['item'];
                    $template_data = array_merge($template_data, $items);
                }
            }
        }

        if (file_exists(self::$root_dir . self::$modules_path . $this->brand_name . "/" . $this->family_line . "/template_data_" . $this->model . "_custom.json")) {
            $template_data_multi = $this->file2json(self::$root_dir . self::$modules_path . $this->brand_name . "/" . $this->family_line . "/template_data_" . $this->model . "_custom.json");
            $template_data_multi = $template_data_multi['template_data']['category'];
            foreach ($template_data_multi as $categories) {
                $subcats = $categories['subcategory'];
                foreach ($subcats as $subs) {
                    $items = $subs['item'];
                    $template_data = array_merge($template_data, $items);
                }
            }
        }
        return($template_data);
    }

    private function parse_config_values($file_contents, $data=NULL, $keep_unknown=FALSE) {
        //Find all matched variables in the text file between "{$" and "}"
        preg_match_all('/[{\$](.*?)[}]/i', $file_contents, $match);
        //Result without brackets (but with the $ variable identifier)
        $no_brackets = array_values(array_unique($match[1]));
        //Result with brackets
        $brackets = array_values(array_unique($match[0]));

        foreach ($no_brackets as $variables) {
            $original_variable = $variables;
            $variables = str_replace("$", "", $variables);
            $default_exp = preg_split("/\|/i", $variables);
            $default = isset($default_exp[1]) ? $default_exp[1] : null;

            if (is_array($data)) {
                if (isset($data[$variables])) {
                    $data[$variables] = $this->replace_static_variables($data[$variables]);
                    $this->debug("Replacing '{" . $original_variable . "}' with " . $data[$variables]);
                    $file_contents = str_replace('{' . $original_variable . '}', $data[$variables], $file_contents);
                }
            } else {
                if (isset($this->settings[$variables])) {
                    $this->settings[$variables] = $this->replace_static_variables($this->settings[$variables]);
                    $file_contents = str_replace('{' . $original_variable . '}', $this->settings[$variables], $file_contents);
                } elseif (!$keep_unknown) {
                    //read default template values here, blank unknowns or arrays (which are blanks anyways)
                    $key1 = $this->arraysearchrecursive('$' . $variables, $this->template_data, 'variable');
                    $default_hard_value = NULL;

                    //Check for looping statements. They are all setup logically the same. Ergo if the first multi-dimensional array has a variable key its not a loop.
                    if ($key1['1'] == 'variable') {
                        $default_hard_value = $this->replace_static_variables($this->template_data[$key1[0]]['default_value']);
                    } elseif ($key1['4'] == 'variable') {
                        $default_hard_value = $this->replace_static_variables($this->template_data[$key1[0]][$key1[1]][$key1[2]][$key1[3]]['default_value']);
                    }

                    if (isset($default)) {
                        $default = $this->replace_static_variables($default);
                        $file_contents = str_replace('{' . $original_variable . '}', $default, $file_contents);
                        $this->debug('Replacing {' . $original_variable . '} with default piped value of:' . $default);
                    } elseif (isset($default_hard_value)) {
                        $default_hard_value = $this->replace_static_variables($default_hard_value);
                        $file_contents = str_replace('{' . $original_variable . '}', $default_hard_value, $file_contents);
                        $this->debug("Replacing {" . $original_variable . "} with default json value of: " . $default_hard_value);
                    } else {
                        //do one last replace statice here.
                        $file_contents = str_replace('{' . $original_variable . '}', "", $file_contents);
                        $this->debug("Blanking {" . $original_variable . "}");
                    }
                }
            }
        }

        return($file_contents);
    }

    /**
     * This will replace statically known variables
     * variables: {$server.ip.*}, {$server.port.*}, {$mac}, {$model}, {$line}, {$ext}, {$displayname}, {$secret}, {$pass}, etc.
     * @param string $contents
     * @param string $specific_line
     * @param boolean $looping
     * @return string
     */
    private function replace_static_variables($contents, $data=NULL) {
        $replace = array(
            # These first ones have an identical field name in the object and the template.
            # This is a good thing, and should be done wherever possible.
            '{$mac}' => $this->mac,
            '{$model}' => $this->model,
            '{$provisioning_type}' => $this->settings['provision']['protocol'],
            '{$provisioning_path}' => $this->settings['provision']['path'],
            '{$vlan_id}' => $this->settings['network']['vlan']['id'],
            '{$vlan_qos}' => $this->settings['network']['vlan']['qos'],
            # These are not the same.
            '{$timezone_gmtoffset}' => $this->timezone['gmtoffset'],
            '{$timezone_timezone}' => $this->timezone['timezone'],
            '{$timezone}' => $this->timezone['timezone'], # Should this be depricated??
            '{$network_time_server}' => $this->settings['ntp'],
            #old
            '{$srvip}' => $this->settings['line'][0]['server_host'],
            '{$server.ip.1}' => $this->settings['line'][0]['server_host'],
            '{$server.port.1}' => $this->settings['line'][0]['server_port'],
            '{$server.ip.2}' => $this->settings['line'][0]['backup_server_host'],
            '{$server.port.2}' => $this->settings['line'][0]['backup_server_port']
        );

        $contents = str_replace(array_keys($replace), array_values($replace), $contents);

        if (is_array($data)) {
            //not needed I dont think
        } else {
            //Find all matched variables in the text file between "{$" and "}"
            preg_match_all('/[{\$](.*?)[}]/i', $contents, $match);
            //Result without brackets (but with the $ variable identifier)
            $no_brackets = array_values(array_unique($match[1]));
            //Result with brackets
            $brackets = array_values(array_unique($match[0]));
            //loop though each variable found in the text file
            foreach ($no_brackets as $variables) {
                $original_variable = $variables;
                $variables = str_replace("$", "", $variables);

                $line_exp = preg_split("/\./i", $variables);
                                
                if ((isset($line_exp[2])) && ($line_exp[0] == 'line')) {
                    $line = $line_exp[1];
                    $key1 = $this->arraysearchrecursive($line, $this->settings['line'], 'line');
                    $var = $line_exp[2];
                    $stored = isset($this->settings['line'][$key1[0]][$var]) ? $this->settings['line'][$key1[0]][$var] : '';
                    $contents = str_replace('{' . $original_variable . '}', $stored, $contents);
                }
            }
        }
        return($contents);
    }

    /**
     * NOTE: Wherever possible, try $this->DateTimeZone->getOffset(new DateTime) FIRST, which takes Daylight savings into account, too.
     * Turns a string like PST-7 or UTC+1 into a GMT offset in seconds
     * @param Send this a timezone like PST-7
     * @return Offset from GMT, in seconds (eg. -25200, =3600*-7)
     * @author Jort Bloem
     */
    private function get_gmtoffset($timezone) {
        # Divide the timezone up into it's 3 interesting parts; the sign (+/-), hours, and if they exist, minutes.
        # note that matches[0] is the entire matched string, so these 3 parts are $matches[1], [2] and [3].
        preg_match('/([\-\+])([\d]+):?(\d*)/', $timezone, $matches);
        # $matches is now an array; $matches[1] is the sign (+ or -); $matches[2] is number of hours, $matches[3] is minutes (or empty)
        return intval($matches[1] . "1") * ($matches[2] * 3600 + $matches[3] * 60);
    }

    /**
     * Turns an integer like -3600 (seconds) into a GMT offset like GMT-1
     * @param Time offset in seconds, like 3600 or -25200 or -27000
     * @return timezone (eg. GMT+1 or GMT-7 or GMT-7:30)
     * @author Jort Bloem
     */
    private function get_timezone($offset) {
        if ($offset < 0) {
            $result = "GMT-";
            $offset = abs($offset);
        } else {
            $result = "GMT+";
        }
        $result.=(int) ($offset / 3600);
        if ($result % 3600 > 0) {
            $result.=":" . (($offset % 3600) / 60);
        } else {
            $result.=":00";
        }
        return $result;
    }

    /**
     * Setup and fill in timezone data
     * @author Jort Bloem
     */
    private function setup_tz() {
        if (isset($this->DateTimeZone)) {
            $this->timezone = array(
                'gmtoffset' => $this->DateTimeZone->getOffset(new DateTime),
                'timezone' => $this->get_timezone($this->DateTimeZone->getOffset(new DateTime))
            );
        } elseif (is_array($this->timezone)) {
            #Do nothing
        } elseif (is_numeric($this->timezone)) {
            $this->timezone = array(
                'gmtoffset' => $this->timezone,
                'timezone' => $this->get_timezone($this->timezone),
            );
        } else {
            $this->timezone = array(
                'gmtoffset' => $this->get_gmtoffset($this->timezone),
                'timezone' => $this->timezone,
            );
        }
    }

    function debug($message) {
        if ($this->debug) {
            $this->debug_return[] = $message;
        }
    }

    function file2json($file) {
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $data = json_decode($json, TRUE);
            return($data);
        } else {
            throw new Exception("Could not load: " . $file);
        }
    }

    private function generate_info($file_contents) {
        if ($this->server_type == "file") {
            $file_contents = str_replace('{$provisioner_generated_timestamp}', date('l jS \of F Y h:i:s A'), $file_contents);
        } else {
            $file_contents = str_replace('{$provisioner_generated_timestamp}', 'N/A (Prevents reboot loops if set to static value)', $file_contents);
        }
        $file_contents = str_replace('{$provisioner_processor_info}', $this->processor_info, $file_contents);
        $file_contents = str_replace('{$provisioner_timestamp}', $this->processor_info, $file_contents);
        $file_contents = str_replace('{$provisioner_brand_timestamp}', $this->brand_data['data']['brands']['last_modified'] . " (" . date('l jS \of F Y h:i:s A', $this->brand_data['data']['brands']['last_modified']) . ")", $file_contents);
        $file_contents = str_replace('{$provisioner_family_timestamp}', $this->brand_data['data']['brands']['last_modified'] . " (" . date('l jS \of F Y h:i:s A', $this->brand_data['data']['brands']['last_modified']) . ")", $file_contents);
        return($file_contents);
    }

    private function initialize() {
        if (!$this->initialized) {

            //Load files for quicker processing
            $this->family_data = $this->file2json(self::$root_dir . self::$modules_path . $this->brand_name . "/" . $this->family_line . "/family_data.json");
            $this->brand_data = $this->file2json(self::$root_dir . self::$modules_path . $this->brand_name . "/brand_data.json");

            $this->model_data = $this->find_model($this->family_data);
            $this->max_lines = isset($this->model_data['lines']) ? $this->model_data['lines'] : 1;

            $this->template_data = $this->merge_files();

            $this->setup_tz();

            if (empty($this->engine_location)) {
                if ($this->engine == 'asterisk') {
                    $this->engine_location = 'asterisk';
                } elseif ($this->engine == 'freeswitch') {
                    $this->engine_location = 'freeswitch';
                }
            }

            //TODO: fix NTP
            if (!isset($this->ntp)) {
                $this->ntp = $this->server[1]['ip'];
            }

            //TODO: Shorten
            if (!in_array($this->settings['provision']['type'], $this->server_type_list)) {
                $this->server_type = $this->default_server_type;
            } else {
                $this->server_type = $this->settings['provision']['type'];
            }
            
            if (!in_array($this->settings['provision']['protocol'], $this->provisioning_type_list)) {
                $this->provisioning_type = $this->default_provisioning_type;
            } else {
                $this->provisioning_type = $this->settings['provision']['protocol'];
            }

            //TODO:fix
            if (!isset($this->vlan_id)) {
                $this->vlan_id = 0;
            }
            if (!isset($this->vlan_qos)) {
                $this->vlan_qos = 5;
            }

            if(empty($this->mac)) {
                    throw new Exception("mac can not be blank!");
            }

            $this->initialized = TRUE;
        }
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

}

//This Class is for checking for global files, which in the case of a provisioner shouldn't really need to exist, but some phones need these so we generate blanks

class Provisioner_Globals {

    /**
     * List all global files as reg statements here.
     * This should be called statically eg: $data=Provisioner_Globals:dynamic_global_files($filename);
     * Return data for global if valid
     * else just return false (eg file does not exist)
     * @param String $filename Name of the file: eg aastra.cfg
     * @return String, data of that file: eg # This file intentionally left blank!
     */
    function dynamic_global_files($file, $provisioner_path='/tmp/', $web_path='/') {
        if (preg_match("/y[0]{11}[1-7].cfg/i", $file)) {
            $file = 'y000000000000.cfg';
        }
        if (preg_match("/spa.*.cfg/i", $file)) {
            $file = 'spa.cfg';
        }
        switch ($file) {
            //spa-cisco-linksys
            case 'spa.cfg':
                return("<flat-profile>
                    <!-- The Phone will load up this file first -->
                    <!-- Don't put anything else into this file except the two lines below!! It will never be referenced again! -->
                    <!-- Trick the Phone into loading a specific file for JUST that phone -->
                    <!-- Set the resync to 3 second2 so it reboots automatically, we set this to 86400 seconds in the other file -->
                    <Resync_Periodic>3</Resync_Periodic>
                    <Profile_Rule>" . $web_path . "spa\$MA.json</Profile_Rule>
                    <Text_Logo group=\"Phone/General\">~PLEASE WAIT~</Text_Logo>
                    <Select_Background_Picture ua=\"ro\">Text Logo</Select_Background_Picture>
                </flat-profile>");
                break;
            //yealink
            case 'y000000000000.cfg':
                return("#left blank");
                break;
            //aastra
            case "aastra.cfg":
                return("#left blank");
                break;
            default:
                if (file_exists($provisioner_path . $file)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename=' . basename($provisioner_path . $file));
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($provisioner_path . $file));
                    ob_clean();
                    flush();
                    readfile($provisioner_path . $file);
                    return('empty');
                } else {
                    return(FALSE);
                }
                break;
        }
    }

}