<?php

	/* generate specializations list */

	$arr = array(
		'general' => "General Practitioner",
		'ent' => "Eyes, Nose, Throat (ENT)",
		'dental' => "Dental",
		'obgyn' => "Maternity, Ob-Gyn",
		);

	echo "<select id='specs_list'>
		<option></option>";
	foreach ($arr as $val => $text)
	{
		echo "<option val='$val'>$text</option>";
	}
	echo "</select>";	
	
?>
	<script type="text/javascript">
		$(document).ready(function(){
			$('#specs_list').select2({
				placeholder: "Select speciality",
				allowClear: true,
				width: "200px"
			});
		});
	</script>