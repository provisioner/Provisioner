<?PHP

/**
 * Aastra Base File
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
        if (is_executable($this->root_dir . self::$modules_path . $this->brand_name . "/anacrypt")) {
            if (!file_exists($this->root_dir . self::$modules_path . $this->brand_name . "/security.tuz")) {
                exec($this->root_dir . self::$modules_path . $this->brand_name . "/anacrypt -i -p 1234abcd");
                rename("security.tuz", $this->root_dir . self::$modules_path . $this->brand_name . "/security.tuz");
                if (!file_exists($this->root_dir . self::$modules_path . $this->brand_name . "/security.tuz")) {
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
        if (file_exists($this->root_dir . self::$modules_path . $this->brand_name . "/security.tuz")) {
            unlink($this->root_dir . self::$modules_path . $this->brand_name . "/security.tuz");
        }
    }

    function encrypt_files($returned_array) {
        if (is_executable($this->root_dir . self::$modules_path . $this->brand_name . "/anacrypt")) {
            if (file_exists($this->root_dir . self::$modules_path . $this->brand_name . "/security.tuz")) {
                foreach ($returned_array as $key => $data) {
                    mkdir($this->root_dir . self::$modules_path . $this->brand_name . "/secure_temp");

                    $file = $this->root_dir . self::$modules_path . $this->brand_name . "/secure_temp/" . $key;
                    $fp = fopen($file, 'w');
                    fwrite($fp, $data);
                    fclose($fp);

                    exec($this->root_dir . self::$modules_path . $this->brand_name . "/anacrypt " . $file . " -p 1234abcd");

                    if (file_exists($this->mac . ".tuz")) {
                        rename($this->mac . ".tuz", $this->root_dir . self::$modules_path . $this->brand_name . "/" . $this->mac . ".tuz");
                        $handle = fopen($this->root_dir . self::$modules_path . $this->brand_name . "/" . $this->mac . ".tuz", "rb");
                        $contents = stream_get_contents($handle);
                        fclose($handle);
                        unlink($this->root_dir . self::$modules_path . $this->brand_name . "/" . $this->mac . ".tuz");
                        $encrypted_array[$this->mac . '.tuz'] = $contents;
                    } elseif (file_exists("aastra.tuz")) {
                        rename("aastra.tuz", $this->root_dir . self::$modules_path . $this->brand_name . "/aastra.tuz");
                        $handle = fopen($this->root_dir . self::$modules_path . $this->brand_name . "/aastra.tuz", "rb");
                        $contents = stream_get_contents($handle);
                        fclose($handle);
                        //unlink($this->root_dir. self::$modules_path . $this->brand_name . "/aastra.tuz");
                        $encrypted_array['aastra.tuz'] = $contents;
                    }
                    unlink($this->root_dir . self::$modules_path . $this->brand_name . "/secure_temp/" . $key);
                }
                return($encrypted_array);
            }
        }
    }

}