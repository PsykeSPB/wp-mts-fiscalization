<div class="wrap">
	<h2>MTS Fiscalization Настройки</h2>
	<form method="post" action="options.php">
		<?php 
			settings_fields('mts_fiscalization'); 
			do_settings_sections('mts_fiscalization');
			submit_button('Сохранить');
		?>
	</form>
</div>