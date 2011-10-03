<?php
/**
 * Grandstream GXP Phone File
 *
 * @author Andrew Nagy
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 */
class endpoint_grandstream_gxp_phone extends endpoint_grandstream_base {

	public $family_line = 'gxp';

	function prepare_for_generateconfig() {
		parent::prepare_for_generateconfig();
                // Grandstreams support lines 2-6, so let's add them if they're set
                for ($i = 1; $i < 6; $i++) {
                    $this->lines[$i]['options']['line_active'] = (isset($this->lines[$i]['secret']) ? '1' : '0');
                }

				if(isset($this->options['ext1'])) {
					foreach($this->options['ext1'] as $key => $data) {
						if ($this->options['ext1'][$key]['mode'] == '999') {
							
							$this->options['ext1'][$key]['account'] = '';
							$this->options['ext1'][$key]['name'] = '';
							$this->options['ext1'][$key]['uid'] = '';
							$this->options['ext1'][$key]['mode'] = '';
						}
						
						$this->options['ext1'][$key]['pnum'] = (strlen($key) == '1') ? '0'.$key : $key;
					}
				}
				
				if(isset($this->options['ext2'])) {
					foreach($this->options['ext2'] as $key => $data) {
						if ($this->options['ext2'][$key]['mode'] == '999') {
							
							$this->options['ext2'][$key]['account'] = '';
							$this->options['ext2'][$key]['name'] = '';
							$this->options['ext2'][$key]['uid'] = '';
							$this->options['ext2'][$key]['mode'] = '';
						}
						
						$this->options['ext2'][$key]['pnum'] = (strlen($key) == '1') ? '0'.$key : $key;
					}
				}
	}
	
}
