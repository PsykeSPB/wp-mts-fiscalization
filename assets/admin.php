<div class="wrap">
	<h2>MTS Fiscalization Настройки</h2>
	<form method="post" action="options.php">
		<? settings_fields('mts_fiscalization'); ?>
		<? do_settings_sections('mts_fiscalization'); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row" colspan="100%">
					<h3>Данные организации</h3>
					<p>Следует заполнять в соответствии с данными личного кабинета МТС</p>
					<hr />
				</th>
			</tr>

			<tr valign="top">
				<th scope="row">Email:</th>
				<td>
					<input type="email" 
						name="mts_fiscalization_email" 
						value="<? echo esc_attr( get_option('mts_fiscalization_email') ); ?>" />
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">ИНН:</th>
				<td>
					<input type="text" 
						name="mts_fiscalization_inn" 
						value="<? echo esc_attr( get_option('mts_fiscalization_inn') ); ?>" 
						pattern="[0-9]{12}"/>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">Система Налогооблажения:</th>
				<td>
					<select name="mts_fiscalization_tax_system">
						<option value="osn"	<? echo get_option('mts_fiscalization_tax_system') === 'osn' ? 'selected' : '' ?>>Общая СН</option>
						<option value="usn_income" <? echo get_option('mts_fiscalization_tax_system') === 'usn_income' ? 'selected' : '' ?>>Упрощенная СН (доходы)</option>
						<option value="usn_income_outcome" <? echo get_option('mts_fiscalization_tax_system') === 'usn_income_outcome' ? 'selected' : '' ?>>Упрощенная СН (доходы минус расходы)</option>
						<option value="envd" <? echo get_option('mts_fiscalization_tax_system') === 'envd' ? 'selected' : '' ?>>Единый налог на вмененный доход</option>
						<option value="esn" <? echo get_option('mts_fiscalization_tax_system') === 'esn' ? 'selected' : '' ?>>Единый сельскохозяйственный налог</option>
						<option value="patent" <? echo get_option('mts_fiscalization_tax_system') === 'patent' ? 'selected' : '' ?>>Патентная СН</option>
					</select>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" colspan="100%">
					<hr />
				</th>
			</tr>

			<tr valign="top">
				<th scope="row">Идентификатор Магазина</th>
				<td>
					<input type="text" 
						name="mts_fiscalization_shop_id"
						value="<? echo get_option('mts_fiscalization_shop_id') ?>" />
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">API token</th>
				<td>
					<input type="text" 
						name="mts_fiscalization_api_token"
						value="<? echo get_option('mts_fiscalization_api_token') ?>" />
				</td>
			</tr>
		</table>
		<? submit_button('Сохранить'); ?>
	</form>
</div>