<div class="wrap">
	<h2>MTS Fiscalization Настройки</h2>
	<form method="post" action="options.php">
		<?php settings_fields('mts_fiscalization_options_organization'); ?>
		<h3>Данные организации:</h3>
		<p>Заполняется в соответствии с данными из личном кабинета МТС.</p>
		<table>
			<tr valign="top">
				<th scope="row">
					<label for="mts_fiscalization_organization_email">Email:</label>
				</th>
				<td>
					<input type="email"
						name="mts_fiscalization_organization_email"
						value="<?php echo get_option('mts_fiscalization_organization_email'); ?>"
					/>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
</div>