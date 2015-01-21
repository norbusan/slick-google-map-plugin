var SGMPTinyMCE = tinymce;

(function ($) {
    $.create('tinymce.plugins.shortcode', {

        init: function (ed, url) {
            ed.onBeforeSetContent.add(function (ed, o) {
                if (typeof window.CrayonTinyMCE !== "undefined" && window.CrayonTinyMCE != null) {
                    return false;
                }
            });
        },
        createControl: function (n, cm) {
            switch (n) {
                case 'shortcode':
                    var csm = cm.createSplitButton( 'shortcode', {
                        title	: "Load saved shortcodes",
                        image: SGMPGlobal.assets + '/css/images/google_map.png',
                        onclick: function () {
                            jQuery.post(ajaxurl, {action: 'sgmp_mce_ajax_action'}, function (response) {
                                //alert(response);
                            });
                        }
                    });

                    csm.onRenderMenu.add( function(c, m) {
                        var shortcodesJson = jQuery.parseJSON(SGMPGlobal.shortcodes);
                        var title = jQuery.isArray(shortcodesJson) ? 'No saved shortcodes' : 'Select a shortcode';

                        m.add({title: title, 'class': 'mceMenuItemTitle'}).setDisabled(1);
                        jQuery.each(shortcodesJson, function () {
                            m.add({title : this.title, icon: 'sgmp-mce-split-button-menu-item-icon'});
                        });
                    });

                    return csm;
            }
            return null;
        },
        getInfo : function() {
            return {
                longname : 'Simple Google Map Plugin',
                author : 'Norbert Preining and Alexander Zagniotov',
                authorurl : 'http://wordpress.org/plugins/simple-google-map-plugin/',
                infourl : 'http://wordpress.org/plugins/simple-google-map-plugin/',
                version : SGMPGlobal.version
            };
        }
    });
    $.PluginManager.add('shortcode', $.plugins.shortcode);

    jQuery(document).ready(function () {
        jQuery(document).on("click", "div#menu_content_content_shortcode_menu span.mce_sgmp-mce-split-button-menu-item-icon", function(event) {

            var clickedIcon = jQuery(this);
            var shortcodeTitle = jQuery(this).next().text();
            jQuery.post(ajaxurl, {action: 'sgmp_mce_ajax_action', title: shortcodeTitle}, function (response) {
                if (response === "OK") {
                    jQuery(clickedIcon).closest("tr").remove();

                    if (jQuery("div#menu_content_content_shortcode_menu span.mceText").size() == 1) {
                        jQuery("div#menu_content_content_shortcode_menu span.mceText").text("No saved shortcodes");
                    }

                    alert("Shortcode deleted!");
                }
            });

            return false;
        });

        jQuery(document).on("click", "div#menu_content_content_shortcode_menu span.mceText", function(event) {
            var menuTitle = jQuery(this).text();
            var shortcodesJson = jQuery.parseJSON(SGMPGlobal.shortcodes);
            jQuery.each(shortcodesJson, function () {
                if (this.title === menuTitle) {
                    var code = this.code.replace(new RegExp("\\\\\"", "g"), "\""); // replace escaped quote and escaping slash with just quote
                    code = code.replace(new RegExp("TO_BE_GENERATED", "g"), muid()); // replace escaped quote and escaping slash with just quote
                    $.activeEditor.setContent($.activeEditor.getContent() + code);
                }
            });
            return false;
        });

        function muid() {
            return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1) + "" + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
        }
    });
})(SGMPTinyMCE);


