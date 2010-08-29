/**
 * Main endpoint manager
 */
endpoints = {
    brands: {},

    init: function() {
        // Find all brands that are registered
        $(document).trigger('endpoints.init');
        $(document).trigger('endpoints.register');
    },

    register: function(brandName, obj) {
        this.brands[brandName] = obj;

        console.log ('Registered brand ' + brandName);
    },

    initSelectors: function() {

    },

    initConfigurators: function() {

    },

    listBrands: function() {
        for ( var i in endpoints.brands )
        {
            console.log('Brand ' + i + ' is registered.');
        }
    }
};


/**
 * Individual brands
 */
endpointBrand = {
    directory : '',
    families : {},
	template_data : new Array(),


    /**
     * Register this brand in the system
     */
    register: function() {
        endpoints.register(this.brandName, this);
		//this.loadBrands();
        this.loadFamilies(this.brandName);
		this.loadTemplates(this.brandName,this.familyName,'T20');
    },

    /**
     * Load all known data about a brand
     */
    loadBrands: function() {
        $.ajax({
            url: 'endpoint/master.xml',
            global: false,
            type: "GET",
            dataType: "xml",
            async:false,
            success: function(data){
				$(data).find('data brands').each(function() {
					// Here, we have the <model_list></model_list> guts
					brandName = $(this).find('name').text();
					brandDir = $(this).find('directory').text();
                    console.log('Brand name ' + brandName);
	            });
			}
        });
    },

    /**
     * Load all known data about a brand
     */
    loadFamilies: function(brand) {
        $.ajax({
            url: 'endpoint/' + this.brandName + '/brand_data.xml',
            global: false,
            type: "GET",
            dataType: "xml",
            async:false,
            success: function(data){
                // Take data and populate this class
                this.directory = $(data).find('directory').text();

                // Add all found brands & relevant data to this class
                families = {};
                $(data).find('family').each(function() {
                    modelName = $(this).find('name').text();
                    modelDirectory = $(this).find('directory').text();
                    families[modelDirectory] = modelName;

                    console.log('Model name ' + modelName + ' in ' + modelDirectory + '/');
                });
            }
        });

        this.families = families;
        console.log('Loaded family data for brand ' + this.brandName);
    },

    /**
     * Load all known data about a model
     */
    loadModels: function(brand) {
        // Load stuff?
        $(endpoints.brands[i]).find('family').each(function() {
            console.log('Model name ' + $(this).find('name').text());
        });

        $.ajax({
            url: 'endpoint/' + brand + '/brand_data.xml',
            global: false,
            type: "GET",
            dataType: "xml",
            async:false,
            success: function(data){
                console.log('Successfully loaded brand info for ' + brand + '!');
                // Add all found brands & relevant data about them in our endpoint manager
                endpoints.brands[brand] = data; // Skip past the <data> tag and get to the brands!
            }
        });
    },

    /**
     *
     */
    loadTemplates: function(brand, product, model) {
        // Load stuff?

        $.ajax({
            url: 'endpoint/' + brand + '/' + product + '/family_data.xml',
            global: false,
            type: "GET",
            dataType: "xml",
            async:false,
            success: function(data){
				$(data).find('data model_list').each(function() {
					// Here, we have the <model_list></model_list> guts
					modelName = $(this).find('model').text();
					if (modelName == model) {
						$(this).find('template_data files').each(function() {
							console.log('Successfully loaded template info for ' + $(this).text() + '!');
							endpointBrand.loadTemplatesData(brand,product,$(this).text());
						});
					}
	                // Store the template for use everywhere
	            });
			}
        })

    },

    loadTemplatesData: function(brand, family, file) {
        // Load stuff?
        $.ajax({
            url: 'endpoint/' + brand + '/' + family + '/'+file,
            global: false,
            type: "GET",
            dataType: "xml",
            async:false,
            success: function(data){
				$(data).find('template_data item').each(function() {
					type = $(this).find('type').text();
					console.log('Successfully loaded template info for ' + type + '!');
	            });
			}
        })

    }
};


// Initialize all loaded endpoint drivers when the document is all setup
$(document).ready(function() {
    endpoints.init();
    endpoints.initSelectors();
    endpoints.initConfigurators();
});
