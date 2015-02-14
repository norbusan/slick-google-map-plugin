<h2>Slick Google Map</h2>
<div class="tools-tabs">
	<ul class="tools-tabs-nav hide-if-no-js">
		<li class="">
			<a title="Shortcode Builder" href="#settings">Settings</a>
		</li>
        <li class="">
            <a title="Support" href="#support">Debug</a>
        </li>
	</ul>

	<div class="tools-tab-body" id="settings" style="">
		<div class="tools-tab-content settings">
				<form action='' name='' id='' method='post'>
				<div id='google-map-container-settings' style='margin-top: 20px'>
				<table cellspacing='0' cellpadding='0' border='0'>
					<tbody>
						<tr>
							<td><b>Shortcode builder under default post/page HTML WYSIWYG editor?</b></td>
						</tr>
						<tr>
							<td>
								<label id='yes-display-label' for='yes-display'>Visible</label>
								<input type='radio' id='yes-display' name='builder-under-post' value='true' YES_DISPLAY_SHORTCODE_BUILDER_INPOST_TOKEN />&nbsp;
								<label id='no-display-label' for='no-display'>Hidden</label>
								<input type='radio' id='no-display' name='builder-under-post' value='false' NO_DISPLAY_SHORTCODE_BUILDER_INPOST_TOKEN /></td>
						</tr>
					</tbody>
				</table>
				<table cellspacing='0' cellpadding='0' border='0'>
					<tbody>
						<tr>
                            <td><br />&nbsp;<br /></td>
						</tr>
					</tbody>
				</table>
				<table cellspacing='0' cellpadding='0' border='0'>
					<tbody>
						<tr>
							<td><b>The following custom post/page types:</b><br />
                                <span style="color: green; font-weight: bold;">[a]</span>&nbsp;Will be included in Geo Mashup maps<br />
                                <span style="color: green; font-weight: bold;">[b]</span>&nbsp;Will have shortcode builder visible under HTML WYSIWYG editor</td>
						</tr>
						<tr>
							<td>
								<label id='custom-post-types' for='custom-post-types'>Enter <span style="color: green; font-weight: bold;">comma</span>-separated values:</label>
								<input type='text' id='custom-post-types' name='custom-post-types' maxlength="40" size="50" value='CUSTOM_POST_TYPES_TOKEN' />
							</td>
						</tr>
					</tbody>
				</table>
                <table cellspacing='0' cellpadding='0' border='0'>
                    <tbody>
                    <tr>
                        <td><br />&nbsp;<br /></td>
                    </tr>
                    </tbody>
                </table>
                <table cellspacing='0' cellpadding='0' border='0'>
                    <tbody>
                    <tr>
                        <td><b>HTML WYSIWYG TinyMCE button to load saved shortcodes (only compatible with WordPress < 3.9)</b></td>
                    </tr>
                    <tr>
                        <td>
                            <label id='yes-enabled-label' for='yes-enabled'>Enabled</label>
                            <input type='radio' id='yes-enabled' name='tinymce-button-in-editor' value='true' YES_ENABLED_TINYMCE_BUTTON_TOKEN />&nbsp;
                            <label id='no-enabled-label' for='no-enabled'>Disabled</label>
                            <input type='radio' id='no-enabled' name='tinymce-button-in-editor' value='false' NO_ENABLED_TINYMCE_BUTTON_TOKEN /></td>
                    </tr>
                    </tbody>
                </table>
                <table cellspacing='0' cellpadding='0' border='0'>
                    <tbody>
                    <tr>
                        <td><br />&nbsp;<br /></td>
                    </tr>
                    </tbody>
                </table>
                <table cellspacing='0' cellpadding='0' border='0'>
                    <tbody>
                    <tr>
                        <td><b>Plugin admin bar menu</b></td>
                    </tr>
                    <tr>
                        <td>
                            <label id='yes-menu-enabled-label' for='yes-menu-enabled'>Enabled</label>
                            <input type='radio' id='yes-menu-enabled' name='plugin-admin-bar-menu' value='true' YES_ENABLED_PLUGIN_ADMIN_BAR_MENU_TOKEN />&nbsp;
                            <label id='no-menu-enabled-label' for='no-menu-enabled'>Disabled</label>
                            <input type='radio' id='no-menu-enabled' name='plugin-admin-bar-menu' value='false' NO_ENABLED_PLUGIN_ADMIN_BAR_MENU_TOKEN /></td>
                    </tr>
                    </tbody>
                </table>
                <table cellspacing='0' cellpadding='0' border='0'>
                    <tbody>
                    <tr>
                        <td><br />&nbsp;<br /></td>
                    </tr>
                    </tbody>
                </table>
                <table cellspacing='0' cellpadding='0' border='0'>
                    <tbody>
                    <tr>
                        <td><b>When viewing map on mobile devices, map should ignore user-set width & height,<br />instead the map should expand to the device's screen width & height</b></td>
                    </tr>
                    <tr>
                        <td>
                            <label id='yes-map-fill-viewport-enabled-label' for='map-fill-viewport-enabled'>Enabled</label>
                            <input type='radio' id='map-fill-viewport-enabled' name='map-fill-viewport' value='true' YES_ENABLED_MAP_FILL_VIEWPORT_TOKEN />&nbsp;
                            <label id='no-map-fill-viewport-enabled-label' for='no-map-fill-viewport-enabled'>Disabled</label>
                            <input type='radio' id='no-map-fill-viewport-enabled' name='map-fill-viewport' value='false' NO_ENABLED_MAP_FILL_VIEWPORT_TOKEN /></td>
                    </tr>
                    </tbody>
                </table>
			</div><br /><br />
			<input type='submit' onclick='' class='button-primary' tabindex='4' value=' Save Settings ' id='sgmp-save-settings' name='sgmp-save-settings' />
		</form>
		</div>
	</div>

    <div class="tools-tab-body" id="support" style="">
        <div class="tools-tab-content">
            <h3 class="hide-if-js">Debug</h3>
            SUPPORT_DATA
        </div>
    </div>
</div>
