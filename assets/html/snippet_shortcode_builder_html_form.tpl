
    SHORTCODEBUILDER_FORM_TITLE
	
    <fieldset class="fieldset">
		<legend>Basic Settings</legend>
		<table class="sgmp-widget-table" cellspacing="0" cellpadding="0" border="0">
			<tbody>
				<tr>
					<td valign="top" class="first-td"><a id="tooltip-marker-width" class="google-map-tooltip-marker" href="javascript:;" title="Width of map placeholder DIV. Can be in pixel or percentage">[Help?]</a>&nbsp;LABEL_WIDTH</td>
					<td valign="top" class="second-td">INPUT_WIDTH</td>
					<td valign="top" class="third-td"><a id="tooltip-marker-height" class="google-map-tooltip-marker" href="javascript:void(0);" title="Height of map placeholder DIV in pixels">[Help?]</a>&nbsp;LABEL_HEIGHT</td>
					<td valign="top" class="fourth-td">INPUT_HEIGHT</td>
				</tr>
				<tr>
					<td valign="top"><a id="tooltip-marker-zoom" class="google-map-tooltip-marker" href="javascript:void(0);" title="Defines the resolution of the map view. Zoom levels between 0 (the lowest level, in which the entire world can be seen on one map) to 19 (the highest level, down to individual buildings) are possible within the normal maps view. Zoom levels up to 20 are possible within satellite view. Please note: when using KML or GPX, the zoom needs to be set within the file. Zoom config option does not affect zoom of the map generated from KML/GPX.">[Help?]</a>&nbsp;LABEL_ZOOM</td>
					<td valign="top">INPUT_ZOOM</td>
					<td valign="top"><a id="tooltip-marker-maptype" class="google-map-tooltip-marker" href="javascript:void(0);" title="The following map types are available in the Google Maps: ROADMAP displays the default road map view, SATELLITE displays Google Earth satellite images, HYBRID displays a mixture of normal and satellite views, TERRAIN displays a physical map based on terrain information, OSM displays OpenStreetMap imagery, please read more about it in plugin documentation page on the left side Wordpress menu">[Help?]</a>&nbsp;LABEL_MAPTYPE</td>
					<td valign="top">SELECT_MAPTYPE</td>
				</tr>
				<tr>
					<td valign="top"><a id="tooltip-marker-mapalign" class="google-map-tooltip-marker" href="javascript:void(0);" title="Controls alignment of the generated map on the screen: LEFT, RIGHT or CENTER">[Help?]</a>&nbsp;LABEL_MAPALIGN</td>
					<td valign="top">SELECT_MAPALIGN</td>
					<td valign="top"><a id="tooltip-marker-mapalign" class="google-map-tooltip-marker" href="javascript:void(0);" title="Hint message displayed above the map, telling users if they want to get directions, they should click on map markers. ATM its in English, sorry :( Localization will come soon!">[Help?]</a>&nbsp;LABEL_DIRECTIONHINT</td>
					<td valign="top">SELECT_DIRECTIONHINT</td>
				</tr>
				<tr>
					<td valign="top"><a id="tooltip-marker-language" class="google-map-tooltip-marker" href="javascript:void(0);" title="The Google Maps API uses the browser's preferred language setting when displaying textual information such as the names for controls, copyright notices, driving directions and labels on maps. In most cases, this is preferable; you usually do not wish to override the user's preferred language setting. However, if you wish to change the Maps API to ignore the browser's language setting and force it to display information in a particular language, you can by selecting on of the available languages in this setting">[Help?]</a>&nbsp;LABEL_LANGUAGE</td>
					<td valign="top">SELECT_LANGUAGE</td>
					<td valign="top"><a id="tooltip-powered-by" class="google-map-tooltip-marker" href="javascript:void(0);" title="Displays 'Powered by Slick Google Map Plugin' notice under the generated map. You can choose to hide the notice, but if you want to help spread the word about the plugin, please leave this setting as 'Enable'. Thank you ;)">[Help?]</a>&nbsp;LABEL_POWEREDBY</td>
					<td valign="top">SELECT_POWEREDBY</td>
				</tr>
			</tbody>
		</table>
	</fieldset>

    <fieldset class="fieldset"  class="collapsible">
        <legend>Map Styles</legend>
        <table class="sgmp-widget-table" cellspacing="0" cellpadding="0" border="0">
            <tbody>
                <tr>
                    <td valign="top" class="first-td">
                        Styled maps allow you to customize the presentation of the standard Google base maps, changing the visual display of such elements as roads, parks, and built-up areas. <br /><br />If you want to apply custom map styles, use the <a href='http://gmaps-samples-v3.googlecode.com/svn/trunk/styledmaps/wizard/index.html' target='_blank'>Styled Maps Wizard</a><br />(http://gmaps-samples-v3.googlecode.com/svn/trunk/styledmaps/wizard/index.html) to generate styles JSON. Once generated, copy the JSON (<b>DO NOT COPY</b> the sentence <i>"Google Maps API v3 Styled Maps JSON"</i>, but just the square brackets and including whats within) and paste in the text area
                    </td>
                </tr>
                <tr>
                    <td valign="top" class="first-td">
                        TEXTAREA_STYLES
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>

	<fieldset class="fieldset"  class="collapsible">
		<legend>Map Controls</legend>
		<table class="sgmp-widget-table" cellspacing="0" cellpadding="0" border="0">
			<tbody>
				<tr>
					<td valign="top" class="first-td"><a id="tooltip-marker-maptypecontrol" class="google-map-tooltip-marker" href="javascript:void(0);" title="The MapType control lets the user toggle between map types (such as ROADMAP and SATELLITE). This control appears by default in the top right corner of the map">[Help?]</a>&nbsp;LABEL_M_APTYPECONTROL</td>
					<td valign="top" class="second-td">SELECT_M_APTYPECONTROL</td>
					<td valign="top" class="third-td"><a id="tooltip-marker-pancontrol" class="google-map-tooltip-marker"  href="javascript:void(0);" title="The Pan control displays buttons for panning the map. This control appears by default in the top left corner of the map on non-touch devices. The Pan control also allows you to rotate 45° imagery, if available">[Help?]</a>&nbsp;LABEL_PANCONTROL</td>
					<td valign="top" class="fourth-td">SELECT_PANCONTROL</td>
				</tr>
				<tr>
					<td valign="top"><a id="tooltip-marker-zoomcontrol" class="google-map-tooltip-marker" href="javascript:void(0);" title="The Zoom control displays a slider (for large maps) or small '+/-' buttons (for small maps) to control the zoom level of the map. This control appears by default in the top left corner of the map on non-touch devices or in the bottom left corner on touch devices">[Help?]</a>&nbsp;LABEL_Z_OOMCONTROL</td>
					<td valign="top">SELECT_Z_OOMCONTROL</td>
					<td valign="top"><a id="tooltip-marker-scalecontrol" class="google-map-tooltip-marker" href="javascript:void(0);" title="The Scale control displays a map scale element. This control is not enabled by default">[Help?]</a>&nbsp;LABEL_SCALECONTROL</td>
					<td valign="top">SELECT_SCALECONTROL</td>
				</tr>
				<tr>
					<td valign="top"><a id="tooltip-marker-streetviewcontrol" class="google-map-tooltip-marker" href="javascript:void(0);" title="The Street View control contains a Pegman icon which can be dragged onto the map to enable Street View. This control appears by default in the top left corner of the map">[Help?]</a>&nbsp;LABEL_STREETVIEWCONTROL</td>
					<td valign="top">SELECT_STREETVIEWCONTROL</td>
					<td valign="top"><a id="tooltip-marker-scrollwheelcontrol" class="google-map-tooltip-marker" href="javascript:void(0);" title="The Scroll Wheel control enables user to zoom in/out on mouse wheel scroll. This setting has 'disable' setting by default">[Help?]</a>&nbsp;LABEL_SCROLLWHEELCONTROL</td>
					<td valign="top">SELECT_SCROLLWHEELCONTROL</td>
				</tr>
				<tr>
					<td valign="top"><a id="tooltip-map-draggable" class="google-map-tooltip-marker" href="javascript:void(0);" title="If disabled, prevents the map from being dragged. Dragging is enabled by default">[Help?]</a>&nbsp;LABEL_DRAGGABLE</td>
					<td valign="top">SELECT_DRAGGABLE</td>
					<td valign="top"><a id="tooltip-overview-map" class="google-map-tooltip-marker" href="javascript:void(0);" title="Enables the 45° imagery view. Note that the map type must be set to either SATELLITE or HYBRID for this property to work. As of February 2012, 45° aerials contain imagery of 15 U.S. and 7 international locations. Please refer to plugin docs for more information">[Help?]</a>&nbsp;LABEL_TILTFOURTYFIVE</td>
					<td valign="top">SELECT_TILTFOURTYFIVE</td>
				</tr>
			</tbody>
		</table>
	</fieldset>

    <fieldset class="fieldset">
        <legend>Current GPS Location</legend>
        <table class="sgmp-widget-table" cellspacing="0" cellpadding="0" border="0">
            <tbody>
            <tr>
                <td valign="top" class="first-td first-td-cell" align="left"></td>
                <td valign="top" class="second-td" align="left"></td>
                <td valign="top" class="third-td" align="left"></td>
                <td valign="top" class="fourth-td" align="left"></td>
            </tr>
            <tr>
                <td class="first-td-cell"><a id="tooltip-marker-gps-location" class="google-map-tooltip-marker" href="javascript:;" title="If selected, the generated map will add end-user's GPS current location and accuracy circle to a map upon end-user's confirmation. The end-user can choose not to disclose his current location. Please refer to plugin docs for more information">[Help?]</a></td>
                <td align="left" colspan="3">
						<span>
							HIDDEN_ENABLEGEOLOCATIONMARKERHIDDEN
							INPUT_ENABLEGEOLOCATIONMARKER&nbsp;LABEL_ENABLEGEOLOCATIONMARKER
						</span>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>

    <fieldset class="fieldset">
        <legend>Marker Clustering</legend>
        <table class="sgmp-widget-table" cellspacing="0" cellpadding="0" border="0">
            <tbody>
            <tr>
                <td valign="top" class="first-td first-td-cell" align="left"></td>
                <td valign="top" class="second-td" align="left"></td>
                <td valign="top" class="third-td" align="left"></td>
                <td valign="top" class="fourth-td" align="left"></td>
            </tr>
            <tr>
                <td class="first-td-cell"><a id="tooltip-marker-gps-location" class="google-map-tooltip-marker" href="javascript:;" title="Some applications are required to display a large number of locations or markers. Naively plotting hundreds-to-thousands of markers on a map can quickly lead to a degraded user experience. Too many markers on the map cause both visual overload and sluggish interaction with the map. To overcome this poor performance, the information displayed on the map can be simplified by organizing markers into clusters">[Help?]</a></td>
                <td align="left" colspan="3">
						<span>
							HIDDEN_ENABLEMARKERCLUSTERINGHIDDEN
							INPUT_ENABLEMARKERCLUSTERING&nbsp;LABEL_ENABLEMARKERCLUSTERING
						</span>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>

	<fieldset class="fieldset">
		<legend>Marker GEO Mashup</legend>
		<table class="sgmp-widget-table" cellspacing="0" cellpadding="0" border="0">
			<tbody>
				<tr>
					<td valign="top" class="first-td first-td-cell" align="left"></td>
					<td valign="top" class="second-td" align="left"></td>
					<td valign="top" class="third-td" align="left"></td>
					<td valign="top" class="fourth-td" align="left"></td>
				</tr>
				<tr>
                    <td class="first-td-cell"><a id="tooltip-marker-addmarkermashup" class="google-map-tooltip-marker" href="javascript:;" title="If selected, the generated map will aggregate all markers from other maps created by you in your public published posts and pages. In other words, you get a Geo marker mashup in one map! At the moment, the mashup does not include markers from maps in widgets, POSTS and PAGES ONLY">[Help?]</a></td>
                    <td align="left" colspan="3">
						<span>
							HIDDEN_ADDMARKERMASHUPHIDDEN
							INPUT_ADDMARKERMASHUP&nbsp;LABEL_ADDMARKERMASHUP
						</span>
					</td>
				</tr>
				<tr>
					<td align="left" colspan="4">&nbsp;</td>
				</tr>
				<tr>
					<td class="first-td-cell">&nbsp;</td>
					<td align="left" colspan="3">
						<span>
							GEOBUBBLE_ADDMARKERMASHUPBUBBLE
						</span>
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>


	<fieldset class="fieldset">
		<legend>Map Markers</legend>
		<table class="sgmp-widget-table" cellspacing="0" cellpadding="0" border="0">
			<tbody>
				<tr>
					<td colspan="4">
						CUSTOM_ADDMARKERICONS
					</td>
				</tr>
				<tr>
					<td valign="top" class="first-td">
						<a id="tooltip-marker-addmarkerinput" class="google-map-tooltip-marker" href="javascript:void(0);" title="You can enter either latitude/longitude seperated by comma (or semi-column), or a fully qualified geographical address. You can also select a custom icon for your marker. If none is selected, default Google marker icon is used - the red pin with black dot. When entering custom marker text, <b>no HTML tags are accepted</b>, all HTML tags will be stripped. <br /><br />But, if you wish to insert a hyper link, you can do it using the following format:<br />#Fully qualified URL starting with http(s) followed by space and a link Name#. Please note the opening and closing hash tags. <br />For example: <b>#http://google.com Search Engine#</b> or <br /><b>#http://someblog.com Where I spent last summer#</b>. Check plugin documentation for more information">[Help?]</a>&nbsp;LABEL_ADDMARKERINPUT
					</td>
					<td colspan="2">
						INPUT_ADDMARKERINPUT
						INPUT_LOCATIONADDMARKERINPUT
						INPUT_BUBBLETEXTADDMARKERINPUT
					</td>
					<td align="right" class="btn-add-marker-td">
						BUTTON_ADDMARKER
					</td>
				</tr>
				<tr>
					<!-- <td>&nbsp;</td> -->
					<td colspan="4">
						LIST_ADDMARKERLIST
						INPUT_ADDMARKERLISTHIDDEN
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>


	<fieldset class="fieldset"  class="collapsible">
		<legend>KML/GPX/Geo RSS</legend>
		<table class="sgmp-widget-table" cellspacing="0" cellpadding="0" border="0">
			<tbody>
				<tr>
					<td class="first-td-cell" align="left"></td>
					<td align="left"></td>
					<td align="left"></td>
					<td align="left"></td>
				</tr>
				<tr>
					<td  valign="top" class="first-td first-td-cell"><a id="tooltip-marker-kml" class="google-map-tooltip-marker"  href="javascript:void(0);" title="KML/GPX/GeoRSS is a file format used to display geographic data in an earth browser, such as Google Earth, Google Maps, and Google Maps for mobile. Specify a valid URL here to a remote KML file (Can be stored on your blog), thats starts with http(s). The Google Maps API supports the KML, GPX and GeoRSS data formats for displaying geographic information. These data formats are displayed on a map from a publicly accessible KML, GPX or GeoRSS file. Please note, KML configuration *supersedes* address and latitude/longitude settings">[Help?]</a>&nbsp;LABEL_KML</td>
					<td colspan="3">INPUT_KML</td>
				</tr>

			</tbody>
		</table>
	</fieldset>

	<fieldset class="fieldset"  class="collapsible">
		<legend>Marker Info Bubbles & Distance Units</legend>
		<table class="sgmp-widget-table" cellspacing="0" cellpadding="0" border="0">
			<tbody>
				<tr>
					<td valign="top" class="first-td"><a id="tooltip-marker-bubbleautopan" class="google-map-tooltip-marker" href="javascript:void(0);" title="Enables or disables info bubble auto-panning (the map view centers on the info bubble) when marker is clicked">[Help?]</a>&nbsp;LABEL_BUBBLEAUTOPAN</td>
					<td valign="top" class="second-td">SELECT_BUBBLEAUTOPAN</td>
                    <td valign="top" class="first-td"><a id="tooltip-marker-distanceunits" class="google-map-tooltip-marker" href="javascript:void(0);" title="Default distance unit for 'Get Directions' dialog. 'Miles' is the default value">[Help?]</a>&nbsp;LABEL_DISTANCEUNITS</td>
                    <td valign="top" class="second-td">SELECT_DISTANCEUNITS</td>
				</tr>
			</tbody>
		</table>
	</fieldset>


	<fieldset class="fieldset" class="collapsible">
		<legend>Custom Overlays</legend>
		<table class="sgmp-widget-table" cellspacing="0" cellpadding="0" border="0">
			<tbody>
				<tr>
					<td valign="top" class="first-td"><a id="tooltip-marker-showbike" class="google-map-tooltip-marker" href="javascript:void(0);" title="A layer showing bike lanes and paths as overlays on a Google Map">[Help?]</a>&nbsp;LABEL_SHOWBIKE</td>
					<td valign="top" class="second-td">SELECT_SHOWBIKE</td>
					<td valign="top" class="third-td"><a id="tooltip-marker-showtraffic" class="google-map-tooltip-marker" href="javascript:void(0);" title="A layer showing vehicle traffic as overlay on a Google Map">[Help?]</a>&nbsp;LABEL_SHOWTRAFFIC</td>
					<td valign="top" class="fourth-td">SELECT_SHOWTRAFFIC</td>
				</tr>
			</tbody>
		</table>
	</fieldset>


	<fieldset class="fieldset" class="collapsible">
		<legend>Panoramio Library</legend>
		<table class="sgmp-widget-table" cellspacing="0" cellpadding="0" border="0">
			<tbody>
				<tr>
					<td valign="top" class="first-td"><a id="tooltip-marker-panoramio" class="google-map-tooltip-marker" href="javascript:void(0);" title="Panoramio (http://www.panoramio.com) is a geolocation-oriented photo sharing website. Accepted photos uploaded to the site can be accessed as a layer in Google Maps. In other words, each photo will be placed on the map like a marker.">[Help?]</a>&nbsp;LABEL_SHOWPANORAMIO</td>
					<td valign="top" class="second-td">SELECT_SHOWPANORAMIO</td>
					<td valign="top" class="third-td"><a id="tooltip-marker-panoramiouid" class="google-map-tooltip-marker" href="javascript:void(0);" title="If specified, the Panoramio photos displayed on the map, will be filtered based on the specified user ID. Please provide NUMERIC user ID only! NOT the Panoramio user web URL!">[Help?]</a>&nbsp;LABEL_PANORAMIOUID</td>
					<td valign="top" class="fourth-td">INPUT_PANORAMIOUID</td>
				</tr>
			</tbody>
		</table>
	</fieldset>

    SHORTCODEBUILDER_HTML_FORM

<div align="right"><span style="font-size: 10px;"><a href="admin.php?page=sgmp-documentation">Documentation</a></span></div>
