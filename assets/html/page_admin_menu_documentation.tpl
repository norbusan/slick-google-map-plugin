<h2>Simple Google Map by Norbert Preining and Alexander Zagniotov</h2>
<div class="tools-tabs">
	<ul class="tools-tabs-nav hide-if-no-js">
		<li class="">
			<a title="Documentation" href="#documentation">Settings Explained</a>
		</li>
		<li class="">
			<a title="Shortcode Explained" href="#shortcodedocs">Shortcode Explained</a>
		</li>
	</ul>
	<div class="tools-tab-body" id="googleanalytics" style="">
		<div class="tools-tab-content documentation">
			<h3 class="hide-if-js">Documentation</h3>
			<h4>Widget and shortcode builder settings explained</h4>
			DOCUMENTATION_TOKEN
		</div>
		<div><span style="color: gray; font-size: 9px;">Disclaimer: The content text used in the plugin documentation and tooltips, has been adopted from Google Map API reference pages.</span></div>
	</div>
	<div class="tools-tab-body" id="shortcodedocs" style="">
		<div class="tools-tab-content">
			<h3 class="hide-if-js">Contribute</h3>
			<div style="border: 1px solid #FFCC99; width: 95%; padding: 5px 15px; -webkit-border-radius: 6px; -moz-border-radius: 6px; border-radius: 6px;">
			<p>Please note, <b>DO NOT copy the below shortcode and paste it into your posts/pages content</b>. Use the shortcode builder found under the post/page HTML editor. In other words, do you know the screen where you input content for your posts/pages? Look for the shortcode builder below that editor. The shortcode called "Simple Google Map Shortcode Builder"</p></div>
			<h4>List of allowed values for the manual shortcode configuration</h4>
			<p style="">
				[google-map-v3<br />
                &nbsp;&nbsp;&nbsp;&nbsp;shortcodeid="<span class="italic">Very important its MUST be unique per shortcode, do not copy/paste! Make sure that it s a random string containing letters and numbers (length of 8-10 characters is enough)</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;addmarkerlist="<span class="italic">one or more full geo address strings or latitude/longitude seperated by comma. When providing multiple locations, they must be seperated by the  <span class="sep">|</span>  sign</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;addmarkermashup="<span class="italic">true <span class="sep">or</span> false. Marker mashup gets marker locations from your other posts that have map on them. Anything in 'addmarkerlist' property will be ignored.</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;addmarkermashupbubble="<span class="italic">true <span class="sep">or</span> false. The "true" - displays in the marker info bubble marker's original post title and a few words from excerpt, while "false" - displays in the marker info bubble marker's address and lat/long.</span>". This property should be used together with  <span class="italic">addmarkermashup</span>.<br />
                &nbsp;&nbsp;&nbsp;&nbsp;animation="<span class="italic">DROP <span class="sep">or</span> BOUNCE</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;bubbleautopan="<span class="italic">true <span class="sep">or</span> false. The "true" - enables auto-pan, while "false" - disables auto-pan</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;directionhint="<span class="italic">true <span class="sep">or</span> false</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;distanceunits="<span class="italic">miles <span class="sep">or</span> km</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;draggable="<span class="italic">true <span class="sep">or</span> false</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;enablegeolocationmarker="<span class="italic">true <span class="sep">or</span> false. If selected, the generated map will add end-user's GPS current location and accuracy circle to a map upon end-user's confirmation. The end-user can choose not to disclose his current location. The GPS marker position is automatically updated as the end-user's position changes. Useful for users on mobile devices that want to find directions from their current location to map's marker or vice versa. Please note, this feature  will function in browsers supporting the W3C Geolocation API. This excludes Internet Explorer versions 8 and older.</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;enablemarkerclustering="<span class="italic">true <span class="sep">or</span> false. If selected, the marker information displayed on the map will be simplified by organizing markers into clusters.</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;height="<span class="italic">any positive numeric character, without decimal points</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;language="<span class="italic">Please choose one of the supported language codes from the v3 API from Google's <a target="_blank" href="https://spreadsheets.google.com/pub?key=p9pdwsai2hDMsLkXsoM05KQ&gid=1">spreadsheet</a></span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;mapalign="<span class="italic">left <span class="sep">or</span> center <span class="sep">or</span> right</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;maptype="<span class="italic">ROADMAP <span class="sep">or</span> SATELLITE <span class="sep">or</span> HYBRID <span class="sep">or</span> TERRAIN <span class="sep">or</span> OSM </span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;maptypecontrol="<span class="italic">true <span class="sep">or</span> false</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;pancontrol="<span class="italic">true <span class="sep">or</span> false</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;panoramiouid="<span class="italic">Any valid Panoramio user ID (Optional. If specified, the photos will be filtered based on the specified user ID)</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;poweredby="<span class="italic">true <span class="sep">or</span> false</span>" The 'true' displays 'Powered By ...' notice under the generated map<br />
                &nbsp;&nbsp;&nbsp;&nbsp;scalecontrol="<span class="italic">true <span class="sep">or</span> false</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;scrollwheelcontrol="<span class="italic">true <span class="sep">or</span> false</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;showbike="<span class="italic">true <span class="sep">or</span> false</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;showmarker="<span class="italic">true <span class="sep">or</span> false</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;showpanoramio="<span class="italic">true <span class="sep">or</span> false</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;showtraffic="<span class="italic">true <span class="sep">or</span> false</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;streetviewcontrol="<span class="italic">true <span class="sep">or</span> false</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;tiltfourtyfive="<span class="italic">true <span class="sep">or</span> false</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;zoom="<span class="italic">any positive numeric character, without decimal points between zero and 20</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;zoomcontrol="<span class="italic">true <span class="sep">or</span> false</span>"<br />
                &nbsp;&nbsp;&nbsp;&nbsp;width="<span class="italic">any positive numeric character, without decimal points</span>"]
            </p>
		</div>
	</div>
</div>
