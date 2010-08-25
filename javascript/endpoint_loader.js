endpoint = {
    brands: [],

    init: function(brands, options) {
        this.loadBrands(brands);
    },

    loadBrands: function(brands) {
        for (var i = 0; i < brands.length; i++) {
            // Load a list of brands
            this.loadBrand(brands[i]);
        }
    },

    /**
     * Load all known data about a brand
     */
    loadBrand: function(brand) {
        // Load stuff?

        $.ajax({
            url: 'endpoint/' + brand + '/brand_data.xml',
            global: false,
            type: "GET",
            dataType: "xml",
            async:false,
            success: function(data){
                console.log('Successfully loaded brand info for ' + brand + '!');
                // Add all found brands & relevant data about them in our endpoint manager
                endpoint.brands[brand] = data; // Skip past the <data> tag and get to the brands!
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

    },

    listBrands: function() {
        for ( var i in endpoint.brands )
        {
            $(endpoint.brands[i]).find('family').each(function() {
                console.log('Model name ' + $(this).find('name').text());
            });

        }

        console.log('Hey Andrew - Inspect me! ', endpoint.brands);
    }
};
