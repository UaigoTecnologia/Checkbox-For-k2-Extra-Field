<?php
/**
 * @version		
 * @package K2 Checkbox For Extra Field		
 * @author Rodrigo Emygdio da Silva		
 * @copyright	
 * @license		
 */

// no direct access
defined('_JEXEC') or die ;

JLoader::register('K2Plugin', JPATH_ADMINISTRATOR.'/components/com_k2/lib/k2plugin.php');
if(!class_exists('K2ModelCategory')) JLoader::register ('K2ModelCategory', JPATH_ADMINISTRATOR.'/components/com_k2/models/category.php');
class plgK2extra_field_checkbox extends K2Plugin
{

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->pluginName = 'extra_field_checkbox';
		$this->loadLanguage();
	}

	public function onRenderAdminForm(&$item, $type, $tab = '') {
            static $first_time = 1;
            
           
            if($first_time == 1){
                $doc                = JFactory::getDocument();
                $extra_field_obj    = new K2ModelExtraField();                
                $k2_category        = new K2ModelCategory();
                
                $k2_table_category  = $k2_category->getTable('K2Category', 'Table');
                $k2_table_category->load($item->catid);
                
                $multi_select_field_id      = $this->params->get('id_field_to_checkbox');
                $id_extra_field             = explode('_', $multi_select_field_id)[1];
                $k2_site_path               = JUri::root();
                $extra_fields_value_saved   = '[]';
                $extra_fields_values        = '[]';
                $extra_fields_configured    = $extra_field_obj->getExtraFieldsByGroup($k2_table_category->extraFieldsGroup);
                $extra_fields_item          = json_decode(htmlspecialchars_decode(addslashes($item->extra_fields)));
                
                foreach (is_null($extra_fields_item)?[]:$extra_fields_item as $value){                   
                    if($value->id == $id_extra_field){
                        $extra_fields_value_saved = json_encode($value);
                        break;
                    }
                }
                foreach($extra_fields_configured as $value){
                    if($value->id == $id_extra_field){
                        $extra_fields_values = $value->value;
                        break;
                    }
                }
               
                $checkbox_js = <<<"Eof"
                 var extra_fields_value_saved   = $extra_fields_value_saved;
                 var extra_fields_configured    = $extra_fields_values;
                 var id_select_multiple_field   = '$multi_select_field_id';
                 var K2SitePath                 = '$k2_site_path';
               function select_multilist_$id_extra_field(t,i) {
                    var myselect = document.getElementById(id_select_multiple_field);
                    var status = t.checked;
                    myselect[i-1].selected=status;
                }        
               function createCheckbox(field){
                   var checkbox         = '';
                   var label            = '';    
                   var has_value_saved  = 0;
                   for(var i=0;i< extra_fields_configured.length;++i){                        
                       checkbox = document.createElement('input');
                       checkbox.setAttribute('type','checkbox');
                       checkbox.setAttribute('onclick','select_multilist_$id_extra_field(this,'+extra_fields_configured[i].value+')'); 
                       checkbox.id =  "checkbox_" + extra_fields_configured[i].value;
                       
                       label            = document.createElement('label');
                       label.htmlFor    = checkbox.id;
                       label.appendChild(document.createTextNode(extra_fields_configured[i].name));
                        
                       if(!(extra_fields_value_saved instanceof Array) && extra_fields_value_saved.value.length > 0){ 
                           has_value_saved = extra_fields_value_saved.value[extra_fields_value_saved.value.indexOf(String(extra_fields_configured[i].value))]
                           if(has_value_saved >0 && has_value_saved != -1){
                                checkbox.checked = true;
                            } 
                       } 
                       field.appendChild(checkbox);
                       field.appendChild(label); 
                    }
                }                    
                    function initializeCheckbox(field){                        
                        var div = document.createElement('div');                        
                        var td_parent  = field.parentNode;                    
                        div.setAttribute('style','display: none;');
                        
                        div.appendChild(field);
                        td_parent.appendChild(div);
                        createCheckbox(td_parent);    
                        
                    }

              window.addEvent('domready', function() {
                
                var  multiple_select_field  = document.getElementById(id_select_multiple_field);                                    
               if(extra_fields_value_saved.length > 0 || extra_fields_configured.length > 0){
                       initializeCheckbox(multiple_select_field);
                }
            });
Eof;
           
            
                $doc->addScriptDeclaration($checkbox_js);
            }
            ++$first_time;
            parent::onRenderAdminForm($item, $type, $tab);
        }
}
