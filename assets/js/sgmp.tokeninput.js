/*
 * jQuery Plugin: Tokenizing Autocomplete Text Entry
 * Version 1.6.0
 *
 * Copyright (c) 2009 James Smith (http://loopj.com)
 * Licensed jointly under the GPL and MIT licenses,
 * choose which one suits your project best!
 * 
 * Please note that this is not the full and original version script.
 * It was amended and castrated by Alexander Zagniotov for a specific task
 *
 */

var jQuerySgmp = jQuery.noConflict();

(function ($) {

		var SGMPGlobal = {};

		SGMPGlobal.sep = $("object#global-data-placeholder param#sep").val();
		SGMPGlobal.customMarkersUri = $("object#global-data-placeholder param#customMarkersUri").val();

		
		var DEFAULT_SETTINGS = {
			holderId: null,	
			theme: null,
			tokenDataId: "id",
			tokenDataValue: "value",
			tokenFormatter: function(item) { 
					
					var value = item[this.tokenDataValue] ;
					var value_arr = value.split(SGMPGlobal.sep);
					var bubbleText = "<p style='padding-left: 50px'><i>No description provided</i> ..</p>";

                var description = value_arr[2];
                if (value_arr[0] != null && value_arr[0] !== "") {
                    value_arr[0] = value_arr[0].replace(new RegExp("\\|", "g"), " - ");
                }
                if (description != null && description !== "") {
                    description = description.replace(new RegExp("\\[|\\]", "g"), "");
                    description = description.replace(new RegExp("\\|", "g"), " - ");
                    bubbleText = "<p style='padding-left: 50px'><i>" + description + "</i></p>";
                }
					
					return "<li><img src='" + SGMPGlobal.customMarkersUri + value_arr[1] + 
		"' border='0' style='float: left; margin-right: 8px;'><p><b>" + value_arr[0] + 
		"</b></p>" + bubbleText + "</li>" }
		};
		var DEFAULT_CLASSES = {
   		    tokenList: "token-input-list",
   		    token: "token-input-token",
   		    inputToken: "token-input-input-token",
   		    highlightedToken: "token-input-highlighted-token",
			hidden: "marker-destinations",
   		    tokenDelete: "token-input-delete-token"
   		};
		
		var methods = {
		    init: function(options) {
		        var settings = $.extend({}, DEFAULT_SETTINGS, options || {});
		        
		        if(settings.theme) {
		            settings.classes = {};
		            $.each(DEFAULT_CLASSES, function(key, value) {
		                settings.classes[key] = value + "-" + settings.theme;
		            });
		        } else  {
		        	settings.classes = $.extend({}, DEFAULT_CLASSES);
		        }

		        return this.each(function () {
		            $(this).data("tokenInputObject", new $.TokenList(this, settings));
		        });
		    },
		    clear: function() {
		        this.data("tokenInputObject").clear();
		        return this;
		    },
		    add: function(item) {
				this.data("tokenInputObject").add(item);
		        return this;
		    },
		    get: function() {
		        return this.data("tokenInputObject").getTokens();
		    }
		}
		
		$.fn.tokenInput = function (method) {
		    // Method calling and initialization logic
		    if(methods[method]) {
		        return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		    } else {
		        methods.init.apply(this, arguments);
		        return this.data("tokenInputObject");
		    }
		};
		
		$.TokenList = function (token_list, settings) {
			
			var saved_tokens = [];
			var token_list = $(token_list);
			var input_token = $("<li><p></p></li>").addClass(settings.classes.inputToken);

			if (!token_list.has("li." + settings.classes.token)) {
				input_token.appendTo(token_list);
			}
			
			token_list.mouseover(function (event) {
	            var li = $(event.target).closest("li");
	            if(li && !li.hasClass(settings.classes.inputToken)) {
	                li.addClass(settings.classes.highlightedToken);
	            }
	        }).mouseout(function (event) {
	            var li = $(event.target).closest("li");
	            if(li && !li.hasClass(settings.classes.inputToken)) {
	                li.removeClass(settings.classes.highlightedToken);
	            }
	        }); 
			
			this.getTokens = function() {
		        return saved_tokens;
		    }
			
			this.add = function(input_value) {
				add_token(input_value);
		    }
			
			this.clear = function() {
		        clear_tokens();
		    }
			
			function add_token(input_value) {
				if (input_value != null && input_value != "") {
                    input_value = input_value.replace(new RegExp("\\|", "g"), " - ");
					var token_data = {id: (saved_tokens.length + 1), value: input_value};
					var exists = false;
            		token_list.children().each(function () {
                        var existing_token = $(this);
                        var existing_data = $.data(existing_token.get(0), "tokeninput");
                       
                        if(existing_data && existing_data.value === token_data.value) {
                        	//console.log(existing_data);
                        	exists = true;
                        }
            		});
            		
            		if (!exists) {
                        token_list.css("border", "1px solid #C9C9C9");
						input_token.remove();
                        saved_tokens.push(token_data);
                        
                        var new_token = $(settings.tokenFormatter(token_data));
               		 	new_token = $(new_token).addClass(settings.classes.token).appendTo(token_list);
               		 	
            		 	$.data(new_token.get(0), "tokeninput", token_data);
            		 	
            		 	$("<span></span>").addClass(settings.classes.tokenDelete).addClass("uiCloseButton")
                        .appendTo(new_token).click(function () {
                           delete_token($(this).parent());
                            if (!(token_list.children().size() > 0)) {
                           	 	input_token.appendTo(token_list);
								 token_list.css("border", "");
                            }
                            return false;
                        });
            		 	
            		 	update_hidden_input();
	            		//console.log(saved_tokens);
            		}
				}
			}
			
			function clear_tokens() {
			  	token_list.children("li").each(function() {
		                delete_token($(this));
		        });
			  	input_token.appendTo(token_list);
			}
			
			function delete_token(token) {
	        	var token_data = $.data(token.get(0), "tokeninput");
	        	var index = token.prevAll().length;
	        	token.remove();
	        	//console.log("Index: " + index);
	        	saved_tokens = saved_tokens.slice(0,index).concat(saved_tokens.slice(index+1));
	        	//console.log(saved_tokens);
	        	update_hidden_input();
	        }
			
			function update_hidden_input() {
	        	var saved_token_values = $.map(saved_tokens, function (element) {
		            return element[settings.tokenDataValue];
	    	   	});
				//console.log("ID of this holder: " + settings.holderId);
	    		jQuerySgmp("#" + settings.holderId + "hidden").val((saved_token_values.join("|")));
	        }
		};
		
	}(jQuerySgmp));
