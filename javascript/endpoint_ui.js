function arraysearchrecursivemulti(Needle, Haystack, NeedleKey, Strict, Path) {
    if (!this.is_array(Haystack))
        return false;
	var i = 0;
	var final = new Array();
	for (var Key in Haystack) {
        if ((this.is_array(Haystack[Key])) && (SubPath = this.arraysearchrecursivemulti(Needle, Haystack[Key], NeedleKey, Strict, Path))) {
            Path = array_merge(Path, Array(Key), SubPath);
			final[i] = Path;
			Path = new Array();
			i++;
        } else if ((!Strict && Haystack[Key] == Needle && Key == (this.strlen(NeedleKey) > 0 ? NeedleKey : Key)) || (Strict && Haystack[Key] === Needle && Key == (this.strlen(NeedleKey) > 0 ? NeedleKey : Key))) {				
			final[i] = Key;
			Path = new Array();
			i++;
        }
    }
	if(!this.empty(final)) {
		return final;
	} else {
		return false;
	}
}

function isset () {
    // !No description available for isset. @php.js developers: Please update the function summary text file.
    // 
    // version: 1008.1718
    // discuss at: http://phpjs.org/functions/isset
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: FremyCompany
    // +   improved by: Onno Marsman
    // +   improved by: Rafał Kukawski
    // *     example 1: isset( undefined, true);
    // *     returns 1: false
    // *     example 2: isset( 'Kevin van Zonneveld' );
    // *     returns 2: true
    
    var a = arguments, l = a.length, i = 0, undef;
    
    if (l === 0) {
        throw new Error('Empty isset'); 
    }
    
    while (i !== l) {
        if (a[i] === undef || a[i] === null) {
            return false; 
        }
        i++; 
    }
    return true;
}

function generate_gui_html(cfg_data,custom_cfg_data, admin, user_cfg_data) {
	var count = 0;
    count = this.count(cfg_data);

    //Check to see if there is a custom template for this phone already listed in the endpointman_mac_list database
    if (this.isset(custom_cfg_data)) {
        custom_cfg_data = this.unserialize($custom_cfg_data);
    } else {
        //No custom template so let's pull the default values for this model into the custom_cfg_data array and populate it from there so that we won't have to make two completely different functions below
		for ( var key in cfg_data )
            if((cfg_data['type'] != 'group') && (cfg_data['type'] != 'break')) {
                key_default = this.str_replace('$','',cfg_data['variable']);
                if(!this.is_array(cfg_data['default_value'])) {
                    custom_cfg_data[key_default]['value'] = cfg_data['default_value'];
                } else {
                    custom_cfg_data[key_default]['value'] = "";
                }
            }
        }
    }
    if(this.isset(user_cfg_data)) {
        user_cfg_data = this.unserialize(user_cfg_data);
    }

    template_variables_array = new Array();

    group_count = 0;
    //Fill the html form data with values from either the database or the default values to display to the end user
	for (i=0;i<count;i++) {
        if(this.array_key_exists('variable',cfg_data[i])) {
            key = str_replace('$','',cfg_data[i]['variable']);
        } else {
            key = "";
        }
        if((admin) || (this.isset(custom_cfg_data[key]['ari']))) {
            //Checks to see if values are defined in the database, if not then we assume this is a new option and we need a default value here!
            if(!this.isset(custom_cfg_data[key]['value'])) {
                //xml2array will take values that have no data and turn them into arrays, we want to avoid the word 'array' as a default value, so we blank it out here if we are an array
                if((this.array_key_exists('default_value',cfg_data[i])) && (this.is_array(cfg_data[i]['default_value']))) {
                    custom_cfg_data[key]['value'] = "";
                } else if((this.array_key_exists('default_value',cfg_data[i])) && (!this.is_array(cfg_data[i]['default_value']))) {
                    custom_cfg_data[key]['value'] = cfg_data[i]['default_value'];
                }
            }

            if (cfg_data[i]['type'] == "group") {
                group_count++;
                template_variables_array[group_count]['title'] = cfg_data[i]['description'];
                variables_count = 0;
            } else if (cfg_data[i]['type'] == "input") {
                if((!admin) && (isset(user_cfg_data[key]['value']))) {
                    custom_cfg_data[key]['value'] = user_cfg_data[key]['value'];
                }
                template_variables_array[group_count]['data'][variables_count]['type'] = "input";
                template_variables_array[group_count]['data'][variables_count]['key'] = key;
                template_variables_array[group_count]['data'][variables_count]['value'] = custom_cfg_data[key]['value'];
                template_variables_array[group_count]['data'][variables_count]['description'] = cfg_data[i]['description'];
            } else if (cfg_data[i]['type'] == "radio") {
                if((!admin) && (isset(user_cfg_data[key]['value']))) {
                    custom_cfg_data[key]['value'] = user_cfg_data[key]['value'];
                }
                num = custom_cfg_data[key]['value'];
                template_variables_array[group_count]['data'][$variables_count]['type'] = "radio";
                template_variables_array[group_count]['data'][$variables_count]['key'] = key;
                template_variables_array[group_count]['data'][$variables_count]['description'] = cfg_data[i]['description'];
                var z = 0;
                while(z < this.count(cfg_data[i]['data'])) {
                    template_variables_array[group_count]['data'][variables_count]['data'][z]['key'] = key;
                    template_variables_array[group_count]['data'][variables_count]['data'][z]['value'] = cfg_data[i]['data'][z]['value'];
                    template_variables_array[group_count]['data'][variables_count]['data'][z]['description'] = cfg_data[i]['data'][z]['text'];
                    if (cfg_data[i]['data'][z]['value'] == $num) {
                        template_variables_array[group_count]['data'][variables_count]['data'][z]['checked'] = 'checked';
                    }
                    z++;
                }
            } else if (cfg_data[i]['type'] == "list") {
                if((!admin) && (isset(user_cfg_data[key]['value']))) {
                    custom_cfg_data[key]['value'] = user_cfg_data[key]['value'];
                }
                num = custom_cfg_data[key]['value'];
                template_variables_array[group_count]['data'][variables_count]['type'] = "list";
                template_variables_array[group_count]['data'][variables_count]['key'] = key;
                template_variables_array[group_count]['data'][variables_count]['description'] = cfg_data[i]['description'];
                var z = 0;
                while(z < count(cfg_data[i]['data'])) {
                    template_variables_array[group_count]['data'][variables_count]['data'][z]['value'] = cfg_data[i]['data'][z]['value'];
                    template_variables_array[group_count]['data'][variables_count]['data'][z]['description'] = cfg_data[i]['data'][z]['text'];
                    if (cfg_data[i]['data'][z]['value'] == num) {
                        template_variables_array[group_count]['data'][variables_count]['data'][z]['selected'] = 'selected';
                    }
                    z++;
                }
            } else if (cfg_data[i]['type'] == "break") {
                template_variables_array[group_count]['data'][variables_count]['type'] = "break";
            }
            $variables_count++;
		}
		return template_variables_array;
}

