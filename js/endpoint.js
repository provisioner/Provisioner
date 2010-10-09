/**
 * Loading & execution order:
 * 1. Fire the event "endpoints.init" letting all endpoints extend any classes they need to and/or set any initial vars they need to
 * 2. Each endpoint will call 'this.loadFamilies' to load it's family, model and template data into memory. (We may optimize this later)
 * 3. Endpoints will now be registered in the array endpoints.brand
 *    (NOTE: It is presumed that a PHP or other script includes the JS files for whatever models you want to make available to your users.
 *     In this way, firing endpoints.init will allow each endpoint to "register" that it exists in the main subsystem)
 * 4. Attach live bindings to all CSS tags in the selector
 * 5. Attach bindings to existing CSS tags that represent the phones (if a selector is not being used)
 * 6. Wait for click events on popups/saves/etc. and paint the screen or post the new data back to the server
 * 7. Display a response from the server after any JSON posts
 */

(function($) {

    /**
     * Main endpoint manager
     */
    $.endpoints = {
        brands: {},

        init: function() {
            // Find all brands that are registered
            $(document).trigger('endpoints.init');
            console.log(this.brands);
        },

        register: function(brandName, obj) {
            this.brands[brandName] = obj;

            console.log ('Registered brand ' + brandName);
        },

        /**
         * Load all known brands
         * NOTE: Don't use this. We would only use this if we wanted to force loading of every brand. But in this case,
         * we get more flexibility letting the caller load the JS files for the models/brands he/she cares about.
         */
        /*loadBrands: function() {
            console.log('Loading all known brand info...');
            $.ajax({
                url: '../endpoint/master.xml',
                global: false,
                type: "GET",
                dataType: "xml",
                async:false,
                success: function(data){
                    $(data).find('data brands').each(function() {
                        // Here, we have the <model_list></model_list> guts
                        name = $(this).find('name').text();
                        directory = $(this).find('directory').text();
                        console.log('Brand name ' + name + ' in directory ' + directory);
                    });
                }
            });

            this.brands[name] = directory;
            console.log('Done loading brand info...');
        },*/

        listBrands: function() {
            for ( var i in endpoints.brands )
            {
                console.log('Brand ' + i + ' is registered.');
            }
        }
    };
})(jQuery);


/**
 * Individual brands
 */
endpointClass = {
    brandData : {},
    families : {},

    /**
     * Load all known data about a brand
     */
    loadFamilies: function() {
        console.log('Loading family info for ' + this.brandName + '...');
        $.ajax({
            url: '../EndpointData.php?filename=endpoint/' + this.brandName + '/brand_data.xml',
            global: false,
            type: "GET",
            dataType: "json",
            async:false,
            success: function(data){
                // Take data and populate this class
                brandData = data['brands'];
                families = data['brands']['family_list'];
                delete(brandData['family_list']); // Don't need copies of this data laying around
            }
        });

        this.brandData = brandData;
        this.families = families['family'];
        console.log('Loaded family data for brand ' + this.brandName, this.brandData, this.families);

        if (this.families['name']) {
            // We know there's only one family in this particular brand'
            console.log('Loading models for family ' + this.families['name']);
            this.families['models'] = this.loadModels(this.brandName,this.families['directory']);
        } else {
            // Cycle through each family
            for (i = 0; i < this.families.length; i++) {
                console.log('Loading models for family ' + this.families[i]['name']);
                this.families['models'] = this.loadModels(this.brandName,this.families[i]['directory']);
            }
        }
        console.log('Done loading family info...');
    },

    /**
     *
     */
    loadModels: function(brand, family) {
        // Load stuff?

        $.ajax({
            url: '../endpoint/' + brand + '/' + family + '/family_data.xml',
            global: false,
            type: "GET",
            dataType: "xml",
            async:false,
            success: function(data){
                templateFields = {};
                $(data).find('data model_list').each(function() {
                    // Here, we have the <model_list></model_list> guts
                    modelName = $(this).find('model').text();
                    templateFields[modelName] = [];
                    $(this).find('template_data files').each(function() {
                        filename = $(this).text();
                        templateData = endpointClass.loadTemplatesData(brand,family,$(this).text());
                        $(templateData).each(function() {
                            templateFields[modelName].push(this);
                        });
                        console.log('Loaded template file ' + filename + ' for ' + modelName + '...')
                    });
                // Store the template for use everywhere
                });
            }
        })

        console.log(templateFields);

        return templateFields;

    },

    loadTemplatesData: function(brand, family, file) {
        console.log('Loading template for ' + brand + ' / ' + family + ' / ' + file);
        // Load stuff?
        $.ajax({
            url: '../EndpointData.php?filename=endpoint/' + brand + '/' + family + '/'+file,
            global: false,
            type: "GET",
            dataType: "json",
            async:false,
            success: function(data){
                result = data;
                console.log('Successfully loaded template ' + file, data);
            }
        });


        if (result['item'][0] && result['item'][0]['data']) {
            for (i = 0; i < result['item'].length; i++) {
                // We're in a list - do a loop'
                templateData = this.processTemplate(result['item'][i]);
            }
        } else {
            templateData = this.processTemplate(result['item']);
        }

        return templateData;
    },

    processTemplate: function(result) {
        //console.log('Post processing', result);
        // If there is any loop data, process it
        if (result['type'] == 'loop') {
            //console.log('In a loop for this file!');
            templateData = [];
            start = result['loop_start'];
            end = result['loop_end'];
            for (i = start; i <= end; i++) {
                //console.log('Creating key #' + i);
                keys = $.extend(true, {}, result['data']['item']);
                numKeys = result['data']['item'].length;
                for (field = 0 ; field < numKeys; field++) {
                    option = keys[field];
                    if (option['description']) {
                        option['description'] = option['description'].replace('{$count}', i + '');
                    }
                    if (option['variable']) {
                        tmp = option['variable'].split('_', 2);
                        option['variable'] = tmp[0] + '[' + i + '][' + tmp[1] + ']';
                    }
                    templateData[(i * numKeys) + field] = option;
                    //console.log('In the loop.', field, );
                }
            }
        } else {
            templateData = result;
        }

        // Strip dollar signs from all variable names
        $(templateData).each(function() {
            if (this['variable']) {
                this['variable'] = this['variable'].replace('$', '');
            }
        })

        return templateData;
    },

    displayOptions : function(selector, model, category) {
        console.log('Display config options for ' + model + ' / category ' + category);

        content = '';

        // Go get all fields and make some content
        $(this.families.models[model]).each(function() {
            if (this['category'] == category) {
                if (this['type'] == 'break') {
                    content += '<div class="break"></div>';
                } else {
                    content += '<div class="field"><label class="' + this['variable'] + '">' + this['description'] + '</label><input name="' + this['variable'] + '" value="' + this['value'] + '"></div>';
                }
            }
        });

        $(selector).html(content);

        // Add the content
        $.colorbox({
            width:"50%",
            inline:true,
            href:selector
        });
    }
};




// Initialize all loaded endpoint drivers when the document is all setup
$(document).ready(function() {
    $.endpoints.init();
});

