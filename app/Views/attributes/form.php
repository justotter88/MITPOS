<?php
/**
 * @var string $definition_id
 * @var object $definition_info
 * @var array $definition_group
 * @var array $definition_flags
 * @var array $selected_definition_flags
 * @var string $controller_name
 * @var array $definition_values
 */
?>
<div id="required_fields_message"><?php echo lang('Common.fields_required_message') ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('attributes/save_definition/' . esc($definition_id, 'attr'), ['id' => 'attribute_form', 'class' => 'form-horizontal']) //TODO: String Interpolation?>
<fieldset id="attribute_basic_info">

	<div class="form-group form-group-sm">
		<?php echo form_label(lang('Attributes.definition_name'), 'definition_name', ['class' => 'required control-label col-xs-3']) ?>
		<div class='col-xs-8'>
			<?php echo form_input ([
					'name' => 'definition_name',
					'id' => 'definition_name',
					'class' => 'form-control input-sm',
					'value'=>esc($definition_info->definition_name, 'attr')
				]
			) ?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label(lang('Attributes.definition_type'), 'definition_type', ['class' => 'required control-label col-xs-3']) ?>
		<div class='col-xs-8'>
			<?php echo form_dropdown('definition_type', DEFINITION_TYPES, esc(array_search($definition_info->definition_type, DEFINITION_TYPES)), 'id="definition_type" class="form-control"') ?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label(lang('Attributes.definition_group'), 'definition_group', ['class' => 'control-label col-xs-3']) ?>
		<div class='col-xs-8'>
			<?php echo form_dropdown(
				'definition_group',
				esc($definition_group, 'attr'),
				esc($definition_info->definition_fk, 'attr'),
				'id="definition_group" class="form-control" ' . (empty($definition_group) ? 'disabled="disabled"' : '')
			) ?>
		</div>
	</div>

	<div class="form-group form-group-sm hidden">
		<?php echo form_label(lang('Attributes.definition_flags'), 'definition_flags', ['class' => 'control-label col-xs-3']) ?>
		<div class='col-xs-8'>
			<div class="input-group">
				<?php echo form_multiselect(
					'definition_flags[]',
					esc($definition_flags, 'attr'),
					esc(array_keys($selected_definition_flags), 'attr'),
					[
						'id' => 'definition_flags',
						'class' => 'selectpicker show-menu-arrow',
						'data-none-selected-text'=>lang('Common.none_selected_text'),
						'data-selected-text-format' => 'count > 1',
						'data-style' => 'btn-default btn-sm',
						'data-width' => 'fit'
					]
				) ?>
			</div>
		</div>
	</div>

	<div class="form-group form-group-sm hidden">
		<?php echo form_label(lang('Attributes.definition_unit'), 'definition_units', ['class' => 'control-label col-xs-3']) ?>
		<div class='col-xs-8'>
			<div class="input-group">
				<?php echo form_input ([
					'name' => 'definition_unit',
					'value' => esc($definition_info->definition_unit, 'attr'),
					'class' => 'form-control input-sm',
					'id' => 'definition_unit'
				]) ?>
			</div>
		</div>
	</div>

	<div class="form-group form-group-sm hidden">
		<?php echo form_label(lang('Attributes.definition_values'), 'definition_value', ['class' => 'control-label col-xs-3']) ?>
		<div class='col-xs-8'>
			<div class="input-group">
				<?php echo form_input (['name' => 'definition_value', 'class' => 'form-control input-sm', 'id' => 'definition_value']) ?>
				<span id="add_attribute_value" class="input-group-addon input-sm btn btn-default">
					<span class="glyphicon glyphicon-plus-sign"></span>
				</span>
			</div>
		</div>
	</div>

	<div class="form-group form-group-sm hidden">
		<?php echo form_label('&nbsp', 'definition_list_group', ['class' => 'control-label col-xs-3']) ?>
		<div class='col-xs-8'>
			<ul id="definition_list_group" class="list-group"></ul>
		</div>
	</div>

</fieldset>
<?php echo form_close() ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	var values = [];
	var definition_id = <?php echo esc($definition_id, 'js') ?>;
	var is_new = definition_id == 0;

	var disable_definition_types = function()
	{
		var definition_type = $("#definition_type option:selected").text();

		if(definition_type == "DATE" || (definition_type == "GROUP" && !is_new) || definition_type == "DECIMAL")
		{
			$('#definition_type').prop("disabled",true);
		}
		else if(definition_type == "DROPDOWN" || definition_type == "CHECKBOX")
		{
			$("#definition_type option:contains('GROUP')").hide();
			$("#definition_type option:contains('DATE')").hide();
			$("#definition_type option:contains('DECIMAL')").hide();
		}
		else
		{
			$("#definition_type option:contains('GROUP')").hide();
		}
	}
	disable_definition_types();

	var disable_category_dropdown = function()
	{
		if(definition_id == -1)
		{
			$('#definition_name').prop("disabled",true);
			$('#definition_type').prop("disabled",true);
			$('#definition_group').parents('.form-group').toggleClass("hidden", true);
			$('#definition_flags').parents('.form-group').toggleClass('hidden', true);
		}
	}
	disable_category_dropdown();

	var show_hide_fields = function(event)
	{
	    var is_dropdown = $('#definition_type').val() !== '1';
	    var is_decimal = $('#definition_type').val() !== '2';
	    var is_no_group = $('#definition_type').val() !== '0';
	    var is_category_dropdown = definition_id == -1;

		$('#definition_value, #definition_list_group').parents('.form-group').toggleClass('hidden', is_dropdown);
		$('#definition_unit').parents('.form-group').toggleClass('hidden', is_decimal);

	//Appropriately show definition flags if not category_dropdown
		if(definition_id != -1)
		{
			$('#definition_flags').parents('.form-group').toggleClass('hidden', !is_no_group);
		}
	};

	$('#definition_type').change(show_hide_fields);
	show_hide_fields();

	$('.selectpicker').each(function () {
		var $selectpicker = $(this);
		$.fn.selectpicker.call($selectpicker, $selectpicker.data());
	});

	var remove_attribute_value = function()
	{
		var value = $(this).parents("li").text();

		if (is_new)
		{
			values.splice($.inArray(value, values), 1);
		}
		else
		{
			$.post('<?php echo esc(site_url("$controller_name/delete_attribute_value/"), 'url') ?>', {definition_id: definition_id, attribute_value: value});
		}
		$(this).parents("li").remove();
	};

	var add_attribute_value = function(value)
	{
		var is_event = typeof(value) !== 'string';

        if ($("#definition_value").val().match(/(\||_)/g) != null)
        {
            return;
        }

		if (is_event)
		{
			value = $('#definition_value').val();

			if (!value)
			{
				return;
			}

			if (is_new)
			{
				values.push(value);
			}
			else
			{
				$.post('<?php echo site_url("attributes/save_attribute_value/") ?>', {definition_id: definition_id, attribute_value: value});
			}
		}

		$('#definition_list_group').append("<li class='list-group-item'>" + value + "<a href='javascript:void(0);'><span class='glyphicon glyphicon-trash pull-right'></span></a></li>")
			.find(':last-child a').click(remove_attribute_value);
		$('#definition_value').val('');
	};

	$('#add_attribute_value').click(add_attribute_value);

	$('#definition_value').keypress(function (e) {
		if (e.which == 13) {
			add_attribute_value();
			return false;
		}
	});

	var definition_values = <?php echo json_encode(array_values(esc($definition_values))) ?>;
	$.each(definition_values, function(index, element) {
		add_attribute_value(element);
	});

	$.validator.addMethod('valid_chars', function(value, element) {
        return value.match(/(\||_)/g) == null;
	}, "<?php echo lang('Attributes.attribute_value_invalid_chars') ?>");

	$('form').bind('submit', function () {
		$(this).find(':input').prop('disabled', false);
	});

	$('#attribute_form').validate($.extend({
		submitHandler: function(form)
		{
			$(form).ajaxSubmit({
				beforeSerialize: function($form, options) {
					is_new && $('<input>').attr({
						id: 'definition_values',
						type: 'hidden',
						name: 'definition_values',
						value: JSON.stringify(values)
					}).appendTo($form);
				},
				success: function(response)
				{
					dialog_support.hide();
					table_support.handle_submit('<?php echo esc(site_url($controller_name), 'url') ?>', response);
				},
				dataType: 'json'
			});
		},
		rules:
		{
			definition_name: 'required',
			definition_value: 'valid_chars',
			definition_type: 'required'
		},
        messages:
        {
            definition_name: "<?php echo lang('Attributes.definition_name_required') ?>",
            definition_type: "<?php echo lang('Attributes.definition_type_required') ?>"
        }
	}, form_support.error));
});
</script>