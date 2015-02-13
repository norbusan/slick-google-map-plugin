/*
Copyright (C) 2011 - 2012 Alexander Zagniotov
Copyright (C) 2015 Norbert Preining

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

var SGMPGlobal = {};
var jQuerySgmp = jQuery.noConflict();

function sendShortcodeToEditor(container_id) {
	(function ($) {
		var id = '#' + container_id;
		var code = buildShortcode(id, muid(), $);
		send_to_editor('<br />' + code + '<br />');
	}(jQuerySgmp));
}

function confirmShortcodeDelete(url, title) {
    var r = confirm("Are you sure you want to delete shortcode\n'" + title + "' ?");
    if (r == true) {
        window.location.href = url;
    }
}

function displayShortcodeInPopup(container_id) {
	(function ($) {
		var id = '#' + container_id;
		var code = buildShortcode(id, "TO_BE_GENERATED", $);
		var content = "Upon saving, the shortcode will be available to you in post/page WYSIWYG editor -<br />just look for the map icon in the editor panel<br /><br /><div id='inner-shortcode-dialog'><b>"
			+ code + "</b></div><br />";
		displayPopupWithContent(content, code, $);
	}(jQuerySgmp));
}

function displayPopupWithContent(content, code, $)  {

		var mask = $('<div id="sgmp-popup-mask"/>');
		var id = Math.random().toString(36).substring(3);
		var shortcode_dialog = $('<div id="' + id + '" class="sgmp-popup-shortcode-dialog sgmp-popup-window">');
		shortcode_dialog.html("<div class='dismiss-container'><a class='dialog-dismiss' href='javascript:void(0)'>×</a></div><p style='padding: 10px 10px 0 10px'>" + content + "</p><div align='center'><input type='button' class='save-dialog' value='Save' /></div>");

		$('body').append(mask);
		$('body').append(shortcode_dialog);

		var maskHeight = $(document).height();
		var maskWidth = $(window).width();
		$('#sgmp-popup-mask').css({'width':maskWidth,'height':maskHeight, 'opacity':0.1});

		if ($("#sgmp-popup-mask").length == 1) {
			$('#sgmp-popup-mask').show();
		}

		var winH = $(window).height();
		var winW = $(window).width();
		$("div#" + id).css('top',  winH/2-$("div#" + id).height()/2);
		$("div#" + id).css('left', winW/2-$("div#" + id).width()/2);
		$("div#" + id).fadeIn(500); 
		$('.sgmp-popup-window .save-dialog').click(function (e) {

            var title = $("input#shortcode-title").val();
            if (typeof title === "undefined" || title.replace(/^\s+|\s+$/g, '') === "") {
                title = "Nameless";
            }
            title = title.replace(new RegExp("'", "g"), "");
			$("input#hidden-shortcode-title").val(title);

            code = code.replace(new RegExp("'", "g"), "");
			$("input#hidden-shortcode-code").val(code);

            $("form#shortcode-save-form").submit();
		});
		$('.sgmp-popup-window .dialog-dismiss').click(function (e) {
			 close_dialog(e, $(this));
		});

		function close_dialog(e, object) {
			e.preventDefault();

			var parentDialog = $(object).closest("div.sgmp-popup-shortcode-dialog");
			if (parentDialog) {
				$(parentDialog).remove();
			}

			if ($("div.sgmp-popup-shortcode-dialog").length == 0) {
				$('#sgmp-popup-mask').remove();
			}
		}

		$('#sgmp-popup-mask').click(function () {
			$(this).remove();
			$('.sgmp-popup-window').remove();
		});
		$(window).resize(function () {
			var box = $('.window');
			var maskHeight = $(document).height();
			var maskWidth = $(window).width();
			$('#sgmp-popup-mask').css({'width':maskWidth,'height':maskHeight});
			var winH = $(window).height();
			var winW = $(window).width();
			box.css('top',  winH/2 - box.height()/2);
			box.css('left', winW/2 - box.width()/2);
		});
}

function muid() {
    return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1) + "" + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
}

function buildShortcode(id, shortcodeId, $) {
	var used_roles = {};
	var code = "[google-map-v3 shortcodeid=\"" + shortcodeId + "\" ";
	$(id + ' .shortcodeitem').each(function() {
		var role = $(this).attr('role');
		var val =  $(this).val();

		if (role === 'addmarkerlisthidden') {
			val = $('<div />').text(val).html(); // from text to HTML
			val = val.replace(new RegExp("'", "g"), "");
			val = val.replace(new RegExp("\"", "g"), "");
            val = val.replace(new RegExp("\\[|\\]", "g"), "");
		}

        if (role === 'styles') {
            val = val.replace(/\s+/g, " ");
            val = base64_encode(val);
        }

		if ($(this).attr('type') === "checkbox") {
			val = $(this).is(":checked");
		}

		if ($(this).attr('type') === "radio") {
			var name = $(this).attr('name');
			val = $('input[name=' + name + ']:checked').val();
			role = name;
		}
	
		if (role === null || typeof role === "undefined" || role === "undefined") {
			role = $(this).attr('id');
		}

		if (role !== null && role !== "" && val !== null && val !== "") {

			if (role.indexOf("_") > 0) {
				role = role.replace(/_/g,"");
			} if (role.indexOf("hidden") > 0) {
				role = role.replace(/hidden/g,"");
			}
		
			if (used_roles[role] === null || typeof used_roles[role] === "undefined") {
				used_roles[role] = role;
				code += role + "=" + "\"" + val + "\" ";
			}
		}
	});
	code = code.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
	code += "]";
	return code;
}


(function ($) {

	SGMPGlobal.sep = $("object#global-data-placeholder param#sep").val();

	if (SGMPGlobal.sep == null || SGMPGlobal.sep == "undefined") {
		SGMPGlobal.sep = "{}";
	}
	SGMPGlobal.customMarkersUri = $("object#global-data-placeholder param#customMarkersUri").val();
	SGMPGlobal.defaultLocationText = $("object#global-data-placeholder param#defaultLocationText").val();
	SGMPGlobal.defaultBubbleText = $("object#global-data-placeholder param#defaultBubbleText").val();
    SGMPGlobal.assets = $("object#global-data-placeholder param#assets").val();
    SGMPGlobal.version = $("object#global-data-placeholder param#version").val();
    SGMPGlobal.shortcodes = $("object#global-data-placeholder param#shortcodes").val();
    SGMPGlobal.ajaxurl = $("object#global-data-placeholder param#ajaxurl").val();

	var lists = [];

		function initTokenHolders()  {

				lists = [];
				var parentElements = "div.widget-google-map-container ul.token-input-list, div#google-map-container-metabox ul.token-input-list";

				$.map($(parentElements), function(element) {
					var id = $(element).attr("id");

					if (id != null && id.indexOf('__i__') == -1) {
						var hiddenInput = "#" + element.id + "hidden";
						var csv = $(hiddenInput).val();

						var holderList = $(element).tokenInput({holderId: id});

						if (csv != null && csv != "") {
							var locations = csv.split("|");
							$.map(locations, function (element) {
								holderList.add(element);
							});
						}

						lists.push({id : id, obj: holderList});
					}
				});
		}

		function initMarkerInputDataFieldsEvent()  {

            $(document).on("focus", "input.marker-text-details", function () {

				if ($(this).val().indexOf("Enter marker") != -1) {
					$(this).val("");
					$(this).removeClass("marker-input-info-text");
				} else {
					$(this).removeClass("marker-input-info-text");
				}
			});

            $(document).on("blur", "input.marker-text-details", function () {
				var value = $(this).val().replace(/^\s+|\s+$/g, '');
				if (value == "") {

					$(this).addClass("marker-input-info-text");

					if ($(this).attr("id").indexOf("bubble") == -1) {
						$(this).val(SGMPGlobal.defaultLocationText);
					} else {
						$(this).val(SGMPGlobal.defaultBubbleText);
					}
				}
			});

		}


		function initAddLocationEevent()  {

            $(document).on("click", "input.add-additonal-location", function (source) {

				var listId = $(this).attr("id") + "list";
				var tokenList = {};
				$.map($(lists), function(element) {
					if (element.id == listId) {
						tokenList = element.obj;
						return;
					}
				});

				var iconHolderInput = "#" + $(this).attr("id") + "input"; //addmarkerinput
				var targetInput = "#" + $(this).attr("id").replace("addmarker", "locationaddmarkerinput"); //locationaddmarkerinput
				var customBubbleTextInput = "#" + $(this).attr("id").replace("addmarker", "bubbletextaddmarkerinput"); //bubbletextaddmarkerinput
				var customBubbleText = $(customBubbleTextInput).val();
				customBubbleText = customBubbleText.replace(/^\s+|\s+$/g, '');
				var customIconListId = "#" + $(this).attr("id") + "icons";
				var selectedIcon = $(customIconListId + " input[name='custom-icons-radio']:checked").val();

				if ($(targetInput).val() != null && $(targetInput).val() != "" && $(targetInput).val().indexOf("Enter marker") == -1) {

					var target = $(targetInput).val().replace(/^\s+|\s+$/g, '');
					var hasValidChars = (target !== "" && target.length > 1);
					if (hasValidChars) {

						customBubbleText = SGMPGlobal.sep + customBubbleText;
						if (customBubbleText.indexOf("Enter marker") != -1) {
							customBubbleText = '';
						}
						target = target.replace(new RegExp("'", "g"), "");
						customBubbleText = customBubbleText.replace(new RegExp("'", "g"), "");
						customBubbleText = customBubbleText.replace(new RegExp("\"", "g"), "");
						
						tokenList.add(target + SGMPGlobal.sep + selectedIcon + customBubbleText);

						resetPreviousIconSelection($(customIconListId));

						$(customIconListId + " img#default-marker-icon").attr("style", "cursor: default; ");
						$(customIconListId + " img#default-marker-icon").addClass('selected-marker-image');
						$(customIconListId + " input#default-marker-icon-radio").prop('checked', true);

						$(iconHolderInput).attr("style", "");
						$(iconHolderInput).addClass("default-marker-icon");
						$(targetInput).val(SGMPGlobal.defaultLocationText);
						$(customBubbleTextInput).val(SGMPGlobal.defaultBubbleText);
						$(targetInput).addClass("marker-input-info-text");
						$(customBubbleTextInput).addClass("marker-input-info-text");
						//$(targetInput).focus();

					} else {
						fadeInOutOnError(targetInput);
					}
				} else {
					fadeInOutOnError(targetInput);
				}

				return false;
			});
		}

		function fadeInOutOnError(targetInput)  {

			$(targetInput).fadeIn("slow", function() {
				$(this).addClass("errorToken");
			});

			$(targetInput).fadeOut(function() {
				$(this).removeClass("errorToken");
				$(this).fadeIn("slow");
			});
		}


		function resetPreviousIconSelection(parentDiv)  {
			$.each(parentDiv.children(), function() {
				var liImg = $(this).find("img");

				if (liImg != null) {
					$(liImg).attr("style", "");
					$(liImg).removeClass('selected-marker-image');
				}
			});
		}

		function initMarkerIconEvents() {

            $(document).on("click", "div.custom-icons-placeholder a img", function () {
				var currentSrc = $(this).attr('src');
				if (currentSrc != null) {

					var parentDiv = $(this).closest("div.custom-icons-placeholder");
					resetPreviousIconSelection(parentDiv);
					$(this).parent("a").siblings('input[name="custom-icons-radio"]').prop("checked", true);
					doMarkerIconUpdateOnSelection(parentDiv, $(this));
				}
			});


            $(document).on("click", "input[name='custom-icons-radio']", function () {

				var img = $(this).siblings("a").children('img');
				var currentSrc = $(img).attr('src');
					if (currentSrc != null) {
						var parentDiv = $(this).closest("div.custom-icons-placeholder");
						resetPreviousIconSelection(parentDiv);
						doMarkerIconUpdateOnSelection(parentDiv, img);
					}
			});
		}

		function doMarkerIconUpdateOnSelection(parentDiv, img)  {

			$(img).attr("style", "cursor: default; ");
			$(img).addClass('selected-marker-image');

			var currentSrc = $(img).attr('src');
			var inputId = $(parentDiv).attr("id").replace("icons", "input");
			$("#" + inputId).attr("style", "background: url('" + currentSrc + "') no-repeat scroll 0px 0px transparent !important");
			$("#" + inputId).prop("readonly", true);
			$("#" + inputId).removeClass("default-marker-icon");
			//$("#" + inputId).focus();
		}

		function initTooltips()  {

            $(document).on("hover", 'a.google-map-tooltip-marker', function() {
			var tooltip_marker_id = $(this).attr('id');

				$("a#" + tooltip_marker_id + "[title]").tooltip({
					effect: 'slide',
					opacity: 0.8,
					tipClass : "google-map-tooltip",
					offset: [-5, 0],
					events: {
						def: "click, mouseleave"
					}
				});

                $(document).on("mouseout", "a#" + tooltip_marker_id, function(event) {
					if ($(this).data('tooltip')) {
						$(this).data('tooltip').hide();
					}
				});
			});
		}

		function initGeoMashupEvent() {

            $(document).on("change", "input.marker-geo-mashup", function (source) {
				var checkboxId = $(this).attr("id");
				var customIconsId = checkboxId.replace("mashup", "icons");
				var kmlId = checkboxId.replace("addmarkermashup", "kml");

				if ($(this).is(":checked")) {
					$("#" + kmlId).closest("fieldset").fadeOut();
					$("#" + customIconsId).closest("fieldset").fadeOut();
					$("#" + checkboxId + "hidden").val("true");
				} else {
					$("#" + kmlId).closest("fieldset").fadeIn();
					$("#" + customIconsId).closest("fieldset").fadeIn();
					$("#" + checkboxId + "hidden").val("false");
				}
			});
		}

        function initInsertShortcodeToPostEvent() {
            var dataName = 'sgmp-find-posts-target';
            $(document).on("click", "a.insert-shortcode-to-post", function (source) {
                var shortcodeName = $(this).attr("id");
                $("div.find-box-search input#affected").val(shortcodeName);
                $('#find-posts').data(dataName, $(this));
                findPosts.open();

                $('#find-posts-submit').click(function(e) {
                    e.preventDefault();

                    // Be nice!
                    if ( !$('#find-posts').data(dataName)) {
                        return;
                    }

                    var selected = $('#find-posts-response').find('input:checked');
                    if (!selected.length) {
                        return false;
                    }

                    var postId = selected.val();
                    var _ajax_nonce = $("div.find-box-search input#_ajax_nonce").val();
                    var shortcodeName = $("div.find-box-search input#affected").val();

                    $.post(SGMPGlobal.ajaxurl, {action: 'sgmp_insert_shortcode_to_post_action', postId: postId, shortcodeName: shortcodeName}, function (response) {
                        console.log("Posting selected post ID#" + postId + " and shortcode name '" + shortcodeName + "' to the server..");
                        if (response != null && response.length > 1) {
                            alert("Shortcode '" + shortcodeName + "' was injected into post titled '" + response + "', ID#" + postId);
                            $('#find-posts-close' ).click();
                        }
                    });
                });

                $('#find-posts-close' ).click(function() {
                    $('#find-posts').removeData(dataName);
                });
            });
        }

		function checkedGeoMashupOnInit() {

			$.each($("input.marker-geo-mashup"), function() {
				var checkboxId = $(this).attr("id");
				var hiddenIdVal = $("#" + checkboxId + "hidden").val();
				var customIconsId = checkboxId.replace("mashup", "icons");
				var kmlId = checkboxId.replace("addmarkermashup", "kml");

				if (hiddenIdVal == "true") {
					$(this).attr("checked", "checked");
					$("#" + kmlId).closest("fieldset").hide();
					$("#" + customIconsId).closest("fieldset").hide();
				} else {
					$(this).removeAttr("checked");
					$("#" + kmlId).closest("fieldset").show();
					$("#" + customIconsId).closest("fieldset").show();
				}
			});
		}

        function initGPSMarkerEvent() {

            $(document).on("change", "input.gps-location-marker", function (source) {
                var checkboxId = $(this).attr("id");

                if ($(this).is(":checked")) {
                    $("#" + checkboxId + "hidden").val("true");
                } else {
                    $("#" + checkboxId + "hidden").val("false");
                }
            });
        }

        function checkedGPSMarkerOnInit() {
            $.each($("input.gps-location-marker"), function() {
                var checkboxId = $(this).attr("id");
                var hiddenIdVal = $("#" + checkboxId + "hidden").val();
                if (hiddenIdVal === "true") {
                    $(this).prop("checked", true);
                } else {
                    $(this).removeAttr("checked");
                }
            });
        }

        function initMarkerClusteringEvent() {

            $(document).on("change", "input.marker-clustering", function (source) {
                var checkboxId = $(this).attr("id");

                if ($(this).is(":checked")) {
                    $("#" + checkboxId + "hidden").val("true");
                } else {
                    $("#" + checkboxId + "hidden").val("false");
                }
            });
        }

        function checkedMarkerClusteringOnInit() {
            $.each($("input.marker-clustering"), function() {
                var checkboxId = $(this).attr("id");
                var hiddenIdVal = $("#" + checkboxId + "hidden").val();
                if (hiddenIdVal === "true") {
                    $(this).prop("checked", true);
                } else {
                    $(this).removeAttr("checked");
                }
            });
        }

		$(document).ready(function() {
			initTokenHolders();
			initAddLocationEevent();
			initMarkerInputDataFieldsEvent();
			initTooltips();
			initMarkerIconEvents();
            checkedGPSMarkerOnInit();
            checkedMarkerClusteringOnInit();
            initGPSMarkerEvent();
			checkedGeoMashupOnInit();
			initGeoMashupEvent();
            initMarkerClusteringEvent();
            initInsertShortcodeToPostEvent() ;

			if (typeof $("ul.tools-tabs-nav").tabs == "function") {
				$("ul.tools-tabs-nav").tabs("div.tools-tab-body", {
					tabs: 'li',
					effect: 'default'
				});
			}
		});


		$(document).ajaxSuccess(
			function (e, x, o) {
				if (o != null && o.data != null)	{
                    var indexOf = o.data.indexOf('id_base=slickgooglemap');
                    if (indexOf > 0) {
                        initTokenHolders();
                        checkedGPSMarkerOnInit();
                        checkedGeoMashupOnInit();
                        checkedMarkerClusteringOnInit();
                    }
				}
			}
		);

}(jQuerySgmp));

function base64_encode (data) {
    // From: http://phpjs.org/functions
    // +   original by: Tyler Akins (http://rumkin.com)
    // +   improved by: Bayron Guevara
    // +   improved by: Thunder.m
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Pellentesque Malesuada
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Rafał Kukawski (http://kukawski.pl)
    // *     example 1: base64_encode('Kevin van Zonneveld');
    // *     returns 1: 'S2V2aW4gdmFuIFpvbm5ldmVsZA=='
    // mozilla has this native
    // - but breaks in 2.0.0.12!
    //if (typeof this.window['btoa'] === 'function') {
    //    return btoa(data);
    //}
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
        ac = 0,
        enc = "",
        tmp_arr = [];

    if (!data) {
        return data;
    }

    do { // pack three octets into four hexets
        o1 = data.charCodeAt(i++);
        o2 = data.charCodeAt(i++);
        o3 = data.charCodeAt(i++);

        bits = o1 << 16 | o2 << 8 | o3;

        h1 = bits >> 18 & 0x3f;
        h2 = bits >> 12 & 0x3f;
        h3 = bits >> 6 & 0x3f;
        h4 = bits & 0x3f;

        // use hexets to index into b64, and append result to encoded string
        tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
    } while (i < data.length);

    enc = tmp_arr.join('');

    var r = data.length % 3;

    return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);

}
