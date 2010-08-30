
// "Extend" our endpointBrand "file"class"/object
$(document).bind('endpoints.init', function() {
    $.extend(endpointYealink, endpointBrand);
    endpoints.register(this.brandName, this);
    endpointYealink.init();
});

var endpointYealink = {
    brandName : 'aastra',
    

    init: function(){
        this.loadFamilies();    // Call our inherited family, model & template loader
        this.bind();
    },

    configureHandset: function () {
        $.colorbox({
            width:"50%",
            inline:true,
            href:"#configureHandset"
        });
    },

    configureDisplay: function () {
        $.colorbox({
            width:"50%",
            inline:true,
            href:"#configureDisplay"
        });
    },

    configureKey: function (){
        $.colorbox({
            width:"50%",
            inline:true,
            href:"#configureKey"
        });
    },

    configureVoicemail: function (){
        alert('Configure voicemail here!');
    },

    configureHeadset: function (){
        alert('Configure your headset volume, gain, etc.');
    },

    configureForward: function (){
        alert('Change your forwarding options');
    },
    
    bind: function() {
        $('.phone_selector .yealink_t22').live('click', function() {
            endpointYealink.display('t22', $('.phone_config', $(this).parent().parent()));
        });

        // Init already drawn items
        $('.phone_config.yealink_t22').each(function() {
            endpointYealink.display('t22', this)
        });
    },

    display: function(model, obj) {
        $(obj).addClass('yealink_t22');
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

}
