<?php
/**
 * HTTP Configuration Sever
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class provisioner_http {
	public $provisioner_path = ''; //Must be set to the location of provisioner!
	public $final_data = NULL; //Final output
	public $mac_address = NULL; //mac address gleaned from file
	public $global_file = FALSE; //is a global file?
        public $provisioner_libary = NULL; //provisioner lib object store
	
	function get($request) {
		if(preg_match('/[0-9a-f]{12}/i',$request,$matches)) {
			if(preg_match('/0000000/',$request)) {
				$this->global_file = TRUE;
				$final_data = $this->generate_global($request);
				return(TRUE);
			} else {
                                //This is a specific file, meaning we need to get data from/for it, so go back to the outside script
				$this->mac_address = $matches[0];
				return(TRUE);				
			}
		} else {
			$this->global_file = TRUE;
			$final_data = $this->generate_global($request);
			return(TRUE);
		}
	}
	
	function generate_global($file) {
                if(preg_match("/y[0]{11}[1-7].cfg/i", $file)) {
                    $file = 'y000000000000.cfg';
                }
		switch($file) {
                        //yealink
			case 'y000000000000.cfg':
				$this->final_data = "#left blank";
				break;
                        //aastra
			case "aastra.cfg":
				$this->final_data = "#left blank";
				break;
	                case "security.tuz":
	                    if(file_exists($provisioner_path."endpoint/aastra/security.tuz")) {
	                        $handle = fopen($provisioner_path."endpoint/aastra/security.tuz", "rb");
	                        $this->final_data = stream_get_contents($handle);
	                        fclose($handle);
	                    }else {
	                        header("HTTP/1.0 404 Not Found");
	                    }
	                    break;
	                case "aastra.tuz":
	                    if(file_exists($provisioner_path."endpoint/aastra/aastra.tuz")) {
	                        $handle = fopen($provisioner_path."endpoint/aastra/aastra.tuz", "rb");
	                        $this->final_data = stream_get_contents($handle);
	                        fclose($handle);
	                    } else {
	                        header("HTTP/1.0 404 Not Found");
	                    }
	                    break;
	                default:
	                    header("HTTP/1.0 404 Not Found");
	                    break;

		}
	}

        function generate_config_data($requested_file) {
            // Because every brand is an extension (eventually) of endpoint, you know this function will exist regardless of who it is
            //Start timer
            $time_start = microtime(true);
            $returned_data = $this->provisioner_libary->generate_config();
            //End timer
            $time_end = microtime(true);
            $time = $time_end - $time_start;

            if(array_key_exists($requested_file, $returned_data)) {
                $this->final_data = $returned_data[$requested_file];
                return(TRUE);
            } else {
                return(FALSE);
            }
        }

        function load_provisioner($brand,$model) {
            if(file_exists($this->provisioner_path.'setup.php')) {
                if(!class_exists('ProvisionerConfig')) {
                    require($this->provisioner_path.'setup.php');
                }

                //Load Provisioner
                $class = "endpoint_" . $brand . "_" . $model . '_phone';
                $base_class = "endpoint_" . $brand. '_base';
                $master_class = "endpoint_base";

                if(!class_exists($master_class)) {
                    ProvisionerConfig::endpointsAutoload($master_class);
                }
                if(!class_exists($base_class)) {
                    ProvisionerConfig::endpointsAutoload($base_class);
                }
                if(!class_exists($class)) {
                    ProvisionerConfig::endpointsAutoload($class);
                }
                //end quick fix

                if(class_exists($class)) {
                    $this->provisioner_libary = new $class();
                    return(TRUE);
                } else {
                    return(FALSE);
                }
            } else {
                return(FALSE);
            }
        }

}