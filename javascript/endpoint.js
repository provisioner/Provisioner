/**
 * Main endpoint manager
 */
endpoints = {
    brands: {},

    init: function() {
        // Find all brands that are registered
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


endpointBrand = {
    directory : '',
    families : {},

    /**
     * Register this brand in the system
     */
    register: function() {
        endpoints.register(this.brandName, this);
        this.loadFamilies(this.brandName);
    },

    /**
     * Load all known data about a brand
     */
    loadFamilies: function() {
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
    loadTemplate: function(brand, product, model) {
        // Load stuff?

        $.ajax({
            url: 'endpoint/' + brand + '/' + product + '/template_data.xml',
            global: false,
            type: "GET",
            dataType: "xml",
            async:false,
            success: function(data){
                console.log('Successfully loaded template info for ' + + '!');
                // Store the template for use everywhere
            }
        });

    }
};


// Initialize all loaded endpoint drivers when the document is all setup
$(document).ready(function() {
    endpoints.init();
    endpoints.initSelectors();
    endpoints.initConfigurators();
});
