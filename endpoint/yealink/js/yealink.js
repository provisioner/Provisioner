// "Extend" our endpointBrand "file"class"/object
$(document).bind('endpoints.init', function() {
    yealink = $.extend({}, endpointClass, yealink);   // Merge base classes in
    yealink.init();
});

yealink = {
    brandName : 'yealink',


    init: function(){
        $.endpoints.register(this.brandName, this);
        this.loadFamilies();    // Call our inherited family, model & template loader
        this.bind();
    },

    configureHandset: function () {
        yealink.displayOptions('.phone_options .display', 'T26', 'handset');
    },

    configureDisplay: function () {
        yealink.displayOptions('.phone_options .display', 'T26', 'display');
    },

    configureKey: function (){
        yealink.displayOptions('.phone_options .display', 'T26', 'softkey');
    },

    configureVoicemail: function (){
        yealink.displayOptions('.phone_options .display', 'T26', 'voicemail');
    },

    configureHeadset: function (){
        yealink.displayOptions('.phone_options .display', 'T26', 'headset');
    },

    configureForward: function (){
        yealink.displayOptions('.phone_options .display', 'T26', 'forward');
    },

    bind: function() {
        $('.endpoint_select .yealink_t22').live('click', function() {
            yealink.display('t22', $('.endpoint_configure', $(this).parent().parent()));
        });

        // Init already drawn items
        $('.endpoint_configure.yealink_t22').each(function() {
            yealink.display('t22', this)
        });
    },

    display: function(model, obj) {
        $(obj).addClass('yealink_' + model);
        // phone handset - 18,3   88,3  18,287  88, 287
        // display - 154,60  247,60    155,107    247,018
        // key 1 - 268,61    279, 73
        // key 2 - 282,78    292, 90
        // key 3 - 269,91    278,105
        // key 4 - 145,135   165,143
        // key 5 - 175,135   195,143
        // key 6 - 205,135   225,143
        // key 7 - 235,135   255,143
        // messages - 287,175   308,182
        // headset -  287,195   308,204
        // forward -  287,215   308,222

        points = [
            {top_x: 18,  top_y: 3,  bottom_x: 88,  bottom_y: 287, callback: this.configureHandset},
            {top_x: 154, top_y: 60, bottom_x: 247, bottom_y: 107, callback: this.configureDisplay},
            {top_x: 268, top_y: 61, bottom_x: 279, bottom_y: 73, callback: this.configureKey},
            {top_x: 282, top_y: 78, bottom_x: 292, bottom_y: 90, callback: this.configureKey},
            {top_x: 269, top_y: 91, bottom_x: 278, bottom_y: 105, callback: this.configureKey},
            {top_x: 287, top_y: 175, bottom_x: 308, bottom_y: 182, callback: this.configureVoicemail},
            {top_x: 287, top_y: 195, bottom_x: 308, bottom_y: 204, callback: this.configureHeadset},
            {top_x: 287, top_y: 215, bottom_x: 308, bottom_y: 222, callback: this.configureForward}
        ];

        // Soft keys
        for (i = 4; i <= 7; i = i+1) {
            points.push ({
                top_x: 25 + (i * 30),
                top_y: 135,
                bottom_x: 45 + (i * 30),
                bottom_y: 143,
                callback: this.configureKey
            });
        }

        $(obj).imagemap(points);
    }

};
