jQuery(document).ready(function(){
    jQuery('.acf_url_field_block table').livequery(function(){
      _this = jQuery(this);
      jQuery("input", _this).each(function(){
          acf_url_field_toggle(jQuery(this));
      });
      jQuery("select,input", _this).change(function(){
          acf_url_field_toggle(jQuery(this));
      });
    });
});
function acf_url_field_toggle(_elt){
	_parent = _elt.parents(".acf_url_field_block");
	_choice = _parent.find(".acf_url_field_choice input:checked").val();
	_val = "";
	_target = "";
	if(_choice == "1"){
		_parent.find(".acf_url_field_internal").show();
		_parent.find(".acf_url_field_external").hide();
		_val = _parent.find(".acf_url_field_internal").find('select').val();
	}else{
		_parent.find(".acf_url_field_internal").hide();
		_parent.find(".acf_url_field_external").show();
		_val = _parent.find(".acf_url_field_external").find('input').val();
		_target = "_blank";
	}
	_label = _parent.find(".acf_url_label").val();
	_final_value = {'target':_target, 'link':_val,'label':_label};
	_parent.find(".acf_url_true_value").val(JSON.stringify(_final_value));
	_parent.find(".acf_url_field_internal").find('select').removeAttr('name');
}