<div class="sgmp-centering-container-handle" align="MAP_ALIGN_TOKEN">MARKER_DIRECTIONS_HINT_TOKEN<div class="google-map-placeholder" id="MAP_PLACEHOLDER_ID_TOKEN" style="width: MAP_PLACEHOLDER_WIDTH_TOKEN; height: MAP_PLACEHOLDER_HEIGHT_TOKEN;"><div align="center" style="background:url('IMAGES_DIRECTORY_URI/loading.gif') no-repeat 0 0 transparent !important; height:100px; width:100px; position: relative; top: LOADING_INDICATOR_TOP_POS_TOKENpx !important;"></div></div>
			MAP_POWEREDBY_NOTICE_TOKEN
			<div class="direction-controls-placeholder" id="direction-controls-placeholder-MAP_PLACEHOLDER_ID_TOKEN" style="background: white; width: MAP_PLACEHOLDER_WIDTH_TOKEN; margin-top: 5px; border: 1px solid #EBEBEB; display: none; padding: 18px 0 9px 0;">
			<div class="d_close-wrapper">
				<a id="d_close" href="javascript:void(0)"> 
					<img src="IMAGES_DIRECTORY_URI/transparent.png" class="close"> 
				</a>
			</div>

			<div style="" id="travel_modes_div" class="dir-tm kd-buttonbar">
				<a tabindex="3" class="kd-button kd-button-left selected" href="javascript:void(0)" id="dir_d_btn" title="By car"> 
					<img class="dir-tm-d" src="IMAGES_DIRECTORY_URI/transparent.png" /> 
				</a>
				<a tabindex="3" class="kd-button kd-button-right" href="javascript:void(0)" id="dir_w_btn" title="Walking"> 
					<img class="dir-tm-w" src="IMAGES_DIRECTORY_URI/transparent.png"> 
				</a>
			</div>
			<div class="dir-clear"></div>
			<div id="dir_wps">
				<div id="dir_wp_0" class="dir-wp">
					<div class="dir-wp-hl">
						<div id="dir_m_0" class="dir-m" style="cursor: -moz-grab;">
							<div style="width: 24px; height: 24px; overflow: hidden; position: relative;">
								<img style="position: absolute; left: 0px; top: -141px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="IMAGES_DIRECTORY_URI/directions.png">
							</div>
						</div>
						<div class="dir-input">
							<div class="kd-input-text-wrp">
								<input type="text" maxlength="2048" tabindex="4" value="" name="a_address" id="a_address" title="Start address" class="wp kd-input-text" autocomplete="off" autocorrect="off">
							</div>
						</div>
					</div>
				</div>
				<div class="dir-rev-wrapper">
					<div id="dir_rev" title="Get reverse directions">
						<a id="reverse-btn" href="javascript:void(0)" class="kd-button"> 
							<img class="dir-reverse" src="IMAGES_DIRECTORY_URI/transparent.png"> 
						</a>
					</div>
				</div>
				<div id="dir_wp_1" class="dir-wp">
					<div class="dir-wp-hl">
						<div id="dir_m_1" class="dir-m" style="cursor: -moz-grab;">
							<div style="width: 24px; height: 24px; overflow: hidden; position: relative;">
								<img style="position: absolute; left: 0px; top: -72px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="IMAGES_DIRECTORY_URI/directions.png">
							</div>
						</div>
						<div class="dir-input">
							<div class="kd-input-text-wrp">
								<input type="text" maxlength="2048" tabindex="4" value="" name="b_address" id="b_address" title="End address" class="wp kd-input-text" autocomplete="off" autocorrect="off">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="dir_controls">
				<div class="d_links">
					<span id="d_options_toggle">
						<a id="d_options_show" class="no-wrap" href="javascript:void(0)" style="display: none !important;">Show options</a> 
						<a id="d_options_hide" class="no-wrap" href="javascript:void(0)" style="display: none !important;">Hide options</a>
					   	<b><span style="color: blue">LABEL_ADDITIONAL_OPTIONS</span></b>
					</span>
				</div>
				<div id="d_options" style="margin-bottom: 5px; text-align: left;">
					<input type="checkbox" tabindex="5" name="MAP_PLACEHOLDER_ID_TOKEN_avoid_hway" id="MAP_PLACEHOLDER_ID_TOKEN_avoid_hway" />
					<label for="MAP_PLACEHOLDER_ID_TOKEN_avoid_hway">LABEL_AVOID_HIGHWAYS</label>
					<input type="checkbox" tabindex="5" name="MAP_PLACEHOLDER_ID_TOKEN_avoid_tolls" id="MAP_PLACEHOLDER_ID_TOKEN_avoid_tolls" />
					<label for="MAP_PLACEHOLDER_ID_TOKEN_avoid_tolls">LABEL_AVOID_TOLLS</label>
					<input type="radio" name="MAP_PLACEHOLDER_ID_TOKEN_travel_mode" id="MAP_PLACEHOLDER_ID_TOKEN_radio_km" />
					<label for="MAP_PLACEHOLDER_ID_TOKEN_radio_km">LABEL_KM</label>
					<input type="radio" name="MAP_PLACEHOLDER_ID_TOKEN_travel_mode" id="MAP_PLACEHOLDER_ID_TOKEN_radio_miles" checked="checked" />
					<label for="MAP_PLACEHOLDER_ID_TOKEN_radio_miles">LABEL_MILES</label>
				</div>
				<div class="dir-sub-cntn">
					<button tabindex="6" name="btnG" type="submit" id="d_sub" class="kd-button kd-button-submit">LABEL_GET_DIRECTIONS</button>
					<button tabindex="6" name="btnG" type="button" style="display: none;" id="print_sub" class="kd-button kd-button-submit">LABEL_PRINT_DIRECTIONS</button>
				</div>
			</div>
		</div>
		<div id="rendered-directions-placeholder-MAP_PLACEHOLDER_ID_TOKEN" style="display: none; border: 1px solid #ddd; width: DIRECTIONS_WIDTH_TOKEN; margin-top: 10px; direction: ltr; overflow: auto; height: 180px; padding: 5px;" class="rendered-directions-placeholder"></div>
	</div>
