<?php
/**
 *   ____             ____        ___                  _
 *  / ___|___  _ __  / _\ \      / (_)______ _ _ __ __| |
 * | |   / _ \| '_ \| |_ \ \ /\ / /| |_  / _` | '__/ _` |
 * | |__| (_) | | | |  _| \ V  V / | |/ / (_| | | | (_| |
 *  \____\___/|_| |_|_|    \_/\_/  |_/___\__,_|_|  \__,_|
 *
 * This file is part of the Confwizard project.
 * Copyright (c) 2013 Patrick Wieschollek
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://integralstudio.net/license.txt
 *
 * @package    ConfWizard
 * @copyright  2013 (c) Patrick Wieschollek
 * @author     Patrick Wieschollek <wieschoo@gmail.com>
 * @link       http://www.integralstudio.net/
 * @license    GPLv3 http://integralstudio.net/license.txt
 *
 */

// ~~~~~~~~~~~~~~~~~~~~~~~~~ VALIDATORS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
abstract class Validator {
	protected $ErrorMessage = "Fehler";
	public function ErrorMessage()
	{
		return $this->ErrorMessage;
	}
	public function Modify($Attributes,$Label,$Events )
	{
		return array($Attributes,$Label,$Events);
	}
	abstract public function Check($Element );
}

class EmptyValidator extends Validator
{
	protected $ErrorMessage = 'cannot be empty';
	protected $clientside = true;

	public function Check($Element )
	{
		return ($Element->Input() !== '' );
	}
	public function Modify($Attributes,$Label,$Events )
	{
		if($this->clientside)
			$Attributes['required'] = 'required';
		if(($Label !== null) AND ($Label[strlen($Label)-1] != '*'))
			$Label = $Label.'*';
		return array($Attributes,$Label,$Events);
	}
	public function __construct($cs=true){

		$this->clientside = $cs;
	}
}
class EmailValidator extends Validator {
	protected $ErrorMessage = 'thats not an email adress';
	public function Check($Element )
	{
		if (filter_var($Element->Input(), FILTER_VALIDATE_EMAIL ) === false ) {
			return false;
		}
		return true;
	}
	public function Modify($Attributes,$Label,$Events )
	{
		$Attributes['type'] = 'email';
		return array($Attributes,$Label,$Events);
	}
}
class MinLengthValidator extends Validator {
	protected $length;
	public function __construct($length=-1)
	{
		$this->length = $length;
		$this->ErrorMessage = 'zu kurz, muss mindestens ' . $this->length . ' Zeichen enthalten.';
	}
	public function Check($Element )
	{
		if (strlen($Element->Input() ) < $this->length ) {
			return false;
		}
		return true;
	}
	public function JQueryRule(){
		return 'minlength: '.$this->length;
	}
}
class MaxLengthValidator extends Validator {
	protected $length;
	public function __construct($length=-1)
	{
		$this->length = $length;
		$this->ErrorMessage = 'zu lang, darf maximal ' . $this->length . ' Zeichen enthalten.';
	}
	public function Check($Element )
	{
		if (strlen($Element->Input() ) > $this->length ) {
			return false;
		}
		return true;
	}
	public function JQueryRule(){
		return 'maxlength: '.$this->length;
	}
}
class InputCompareValidator extends Validator {
	protected $names;
	protected $ErrorMessage = 'Zwei Eingaben müssen übereinstimmen. Das ist nicht der Fall.';
	public function __construct($fieldnames )
	{
		global $GlobalStack;

		$js = "var x = ['";
		$js .= implode("','",$fieldnames);
		$js .= "'];";
		$GlobalStack->javascript .= $js;
		$this->names = $fieldnames;
	}
	public function Modify($Attributes,$Label,$Events )
	{

		$Events['oninput'][] = 'InputCompareValidator(x);';
		return array($Attributes,$Label,$Events);
	}
	public function Check($Element )
	{
		$cmp = $Element->Input();
		foreach($this->names as $name ) {
			if (Request::get($name ) !== $cmp )
				return false;
		}
		return true;
	}
}
class WhiteListValidator extends Validator {
	protected $ErrorMessage = 'Keine erlaubte Eingabe.';
	protected $AllowedValues =array();

	public function __construct($allowed=array()){
		$this->AllowedValues = $allowed;
	}
	public function Check($Element)
	{
		return in_array($Element->Input(),$this->AllowedValues);
	}

}
class BlackListValidator extends Validator {
	protected $ErrorMessage = 'Keine erlaubte Eingabe.';
	protected $DisallowedValues =array();

	public function __construct($disallowed=array()){
		$this->DisallowedValues = $disallowed;
	}
	public function Check($Element )
	{
		return (!in_array($Element->Input(),$this->DisallowedValues));
	}

}
class IpValidator extends Validator{
	public function Check($Element )
	{
		$IP = $Element->Input();
		if (preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/',$IP)) {
			return true;
		}
		return false;
	}
	public function Modify($Attributes,$Label,$Events )
	{
		$Attributes['pattern'] = "^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$";
		return array($Attributes,$Label,$Events);
	}

}
class CharsetValidator extends Validator {
	protected $charset;
	public function __construct($charset )
	{
		$this->charset = $charset;
	}
	public function Check($Element )
	{
		$test = trim($Element->Input());
		for($i = 0;$i < strlen($this->charset );$i++ )
			$test = str_replace($this->charset[$i], '', $test );
		return (strlen($test ) === 0 );
	}
}
class UrlValidator extends Validator {
	protected $ErrorMessage = 'Keine gültige URL';
	public function Check($Element )
	{

		$url = $Element->Input();
		if (preg_match('/^(http|https):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $url)) {
			return true;
		}
		return false;
	}
	public function Modify($Attributes,$Label,$Events )
	{
		$Attributes['pattern'] = "https?://.+";
		return array($Attributes,$Label,$Events);
	}

}
class PathValidator extends Validator {
	protected $ErrorMessage = 'Dieses Verzeichnis existiert nicht, oder Sie haben keinen Zugriff auf dieses Verzeichnis.';
	protected $base = '';
	public function __construct($base){
		$this->base = $base;
	}


	public function Validate($relpath){

		$userpath = $this->base . $relpath;
		$realUserPath = realpath($userpath);
		#var_dump($realUserPath);
		#echo '###'.$userpath.' vs. '.(string)$realUserPath;
		if ($realUserPath === false || strpos($realUserPath, $this->base) !== 0) {
			//Directory Traversal!
			return false;
		} else {
			return true;
		}
	}

	public function Check($Element )
	{
		return $this->Validate($Element->Input());
	}
}
class DirectoryValidator extends Validator {
	protected $ErrorMessage = 'Dieses Verzeichnis existiert nicht.';
	protected $base = '';
	public function __construct($base){
		$this->base = $base;
	}

	public function Validate($relpath){
		$userpath = $this->base . $relpath;
		return (is_dir($userpath));
	}


	public function Check($Element )
	{
		return $this->Validate($Element->Input());
	}


}
class DigitsValidator extends Validator {
	protected $ErrorMessage = 'Eingabe darf nur aus Ziffern bestehen.';
	public function Check($Element )
	{
		if($Element->Input() != ''){
			if(preg_match('/^\d+$/',$Element->Input()) == true){
				return true;
			}else{
				return false;
			}
		}
		return true;
	}
	public function Modify($Attributes,$Label,$Events )
	{
		$Attributes['pattern'] = "^\d+$";
		return array($Attributes,$Label,$Events);
	}
}
// ~~~~~~~~~~~~~~~~~~~~~~~~~ FORMELEMENTS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
abstract class FormElement {

	public $label = null;
	public $quicktip = null;
	public $TagName = 'input';
	public $Attributes = array();
	public $Events=array();
	public $InputHint = null;


	private $HashCode = null;


	protected $ErrorMessages = array();
	protected $ValidatorChain = array();
	protected $AllowedAttributes = array('name','id','type','value','style','class','require');


	public function HashCode(){
		return md5($this->HashCode);
	}

	public function __get($key )
	{
		switch($key){
			default:
				if (isset($this->Attributes[$key] ) )
					return $this->Attributes[$key];
				else
					throw new CodeException('Attribute '.$key.' not found');
			case 'readonly':
				return ($this->Attributes[$key] != 'readonly');

		}


	}
	public function __set($key, $val )
	{

		switch($key){
			default:
				$this->Attributes[$key] = $val;
				break;
			case 'readonly':
			case 'checked':
				if($val)
					$this->Attributes[$key] = $key;
				else
					unset($this->Attributes[$key]);
				break;
			case 'display':
				if($val)
					unset($this->Attributes['style']['display']);
				else
					$this->Attributes['style']['display'] = 'none';
				break;


		}

	}

	public function __construct($name )
	{
		$this->Attributes = array(
		    'name' => $name,
		    'id'   => $name,
		    'label' => null,
		    'value' => '',
		    'placeholder' => '',
		    'class' => array(),
		    'style' => array(),
		    'type' => 'text',
		    );

		$this->HashCode = 'FormElement'.$name;
	}

	public function AddErrorMessage($msg )
	{
		$this->ErrorMessages[] = $msg;
	}
	public function GetErrorMessage()
	{
		if(count($this->ErrorMessages)>0)
			return '<span class="errormsg">' . implode(';', $this->ErrorMessages ) . '</span>';
		else
			return '';
	}
	public function ConstructHtml(){
		$html = '<' . $this->TagName;
		$html .= $this->BuildAttributes();
		$html .= ' />';
		return $html;
	}

	protected function FilterAttributes(){
		$a = array();
		foreach($this->AllowedAttributes as $al){
			if(isset($this->Attributes[$al]))
				$a[$al] =$this->Attributes[$al];
		}
		$this->Attributes = $a;
	}

	public function GetHtml()
	{
		// delete false attributes
		$this->FilterAttributes();
		$this->ApplyValidationModification();
		$html = '';
		$html .= $this->BuildLabel().PHP_EOL;

		$html .= $this->ConstructHtml();
		if($this->InputHint !== null)
			$html .= '<span class="form_hint">'.$this->InputHint.'</span>';

		$html .= $this->GetErrorMessage();
		$html .= $this->QuicktipReference();
		return $html;
	}
	protected function QuicktipReference(){
		return ($this->quicktip !== null) ? '<qt:'.$this->quicktip.'>' : '';
	}
	protected function ApplyValidationModification(){
		foreach($this->ValidatorChain as $V ) {
			// modify element by initialization
			$ans = $V->Modify($this->Attributes,$this->label,$this->Events);
			$this->Attributes = $ans[0];
			$this->label = $ans[1];
			$this->Events = $ans[2];
		}

	}
	protected function BuildLabel(){

		if($this->label === null){
			return '';
		}

		if($this->label === '')
			return '<label class="FormLabel" for="'.$this->id.'"></label>';
		return '<label class="FormLabel" for="'.$this->id.'">'.$this->label.':</label>';
	}
	protected function BuildAttributes(){
		$html = '';
		foreach($this->Attributes as $k => $v ){
			switch($k){
				default:
					$html .= ' ' . $k . '="' . $v . '"';
					break;
				case 'class':
					$html .= ' ' . $k . '="' . implode(' ',$v) . '"';
					break;
				case 'style':
					$styles = array();
					foreach($v as $sk => $sv)
						$styles[] = $sk.':'.$sv.';';
					$html .= ' ' . $k . '="' . implode(' ',$styles) . '"';
					break;

			}
		}

		#var_dump($this->Events);
		// add events
		foreach($this->Events as $eventname => $eventcode){
			$html .= ' '.$eventname.'="'.implode(' ',$eventcode).'" ';
		}

		return $html.' ';
	}

	public function Input()
	{
		return (Request::Exists($this->Attributes['name']) ) ? Request::Get($this->Attributes['name']) : '';
	}
	public function Populate($Data=array())
	{
		if(!in_array($this->type,array('hidden','submit','button','reset'))){
			$val = null;
			if(in_array($this->Attributes['name'],array_keys($Data))  ){
				$val = $Data[$this->Attributes['name']];
				$this->Attributes['value'] = $val;
			}
			else{
				if(Request::Exists($this->Attributes['name'] ) ){
					$val = $this->Input();
					$this->Attributes['value'] = $val;
				}

			}



		}

	}

	public function AddValidator($V=null )
	{
		if ($V instanceof Validator ){
			// add to chain
			$this->ValidatorChain[] = $V;
		}

	}
	protected function AddDefaultValidator( )
	{


	}

	public function Validate()
	{
		$this->AddDefaultValidator();
		$result = true;

		foreach($this->ValidatorChain as $Validator ) {
			$tmp = $Validator->Check($this);
			if ($tmp !== true ) {
				$this->AddErrorMessage($Validator->ErrorMessage() );
				$result = false;
			}
		}
		if(!$result)
			$this->Attributes['class'][] = 'input_error';
		return $result;
	}
}
class FormInput extends FormElement{
	protected $AllowedAttributes = array('name','id','type','value','style','class','size','placeholder','readonly');
}
class FormButton extends FormElement{
	protected $AllowedAttributes = array('name','id','type','value','style','class','size');
}
class FormRadio extends FormElement{
	protected $AllowedAttributes = array('name','id','type','value','style','class','items','readonly');
	public function ConstructHtml()
	{

		$val = isset($this->Attributes['value']) ? $this->Attributes['value'] : '';
		unset($this->Attributes['value']);

		$items = isset($this->Attributes['items']) ?  $this->Attributes['items'] : array();
		unset($this->Attributes['items']);
		$html = '';
		foreach ($items as $k => $v ) {
			$c = '';
			if ($k == $val )
				$c = ' checked="checked"';
			$ev = '';
			foreach($this->Events as $eventname => $eventcode){
				$ev .= ' '.$eventname.'="'.implode(' ',$eventcode).'" ';
			}


			$html .= '<label><input ' . $c . ' type="radio"  name="' . $this->name .
				'" id="' . $this->name . '_'.$val.'" value="' . $k . '"  '.$ev.' >';
			$html .= $v . '</label>';
		}


		return $html;
	}
	protected function AddDefaultValidator( )
	{
		$this->AddValidator(new WhiteListValidator(array_keys($this->items)));

	}
}
class FormSelect extends FormElement{
	protected $AllowedAttributes = array('name','id','type','value','style','class','items','readonly');
	public function ConstructHtml()
	{

		$val = isset($this->Attributes['value']) ? $this->Attributes['value'] : '';
		unset($this->Attributes['value']);

		$items = isset($this->Attributes['items']) ?  $this->Attributes['items'] : array();
		unset($this->Attributes['items']);


		$html = '';
		$html .= '<select ';
		$html .= $this->BuildAttributes();
		$html .= '>';
		foreach ($items as $k => $v ) {
			$c = '';
			if ($k == $val )
				$c = ' selected="selected"';
			$html .= ' <option ' . $c . ' value=\'' . $k . '\'>' . $v . '</option>';
		}
		$html .= '</select>';
		return $html;
	}
	protected function AddDefaultValidator( )
	{
		$this->AddValidator(new WhiteListValidator(array_keys($this->items)));

	}
}
class FormCheckbox extends FormElement{
	protected $AllowedAttributes = array('name','id','type','value','style','class','items','readonly');
	public function ConstructHtml()
	{

		$val = isset($this->Attributes['value']) ? $this->Attributes['value'] : '';
		unset($this->Attributes['value']);

		$items = isset($this->Attributes['items']) ?  $this->Attributes['items'] : array();
		unset($this->Attributes['items']);

		$html = '';
		foreach ($items as $k => $v ) {
			$c = '';
			if ($k == $val )
				$c = ' checked="checked"';
			$html .= ' <label><input '.$this->BuildAttributes().' value="' . $k . '" ' . $c . ' />' . $v.'</label>' ;
		}
		return $html;
	}
}
class FormCustom extends FormElement{
	public function __construct($name )
	{
		$this->label = '';
		$this->Attributes = array(
		    'name' => $name,
		    'id' => $name
		    );
	}
	public function Input(){return '';}
	public function Populate($Data=array()){}
	public function GetHtml()
	{
		$html = '';
		$html .= $this->BuildLabel().PHP_EOL;
		$html .= (isset($this->Attributes['text'])  ? $this->Attributes['text'] : ''   );
		return $html;
	}
}
class FormLink extends FormElement{
	public $Link;
	public function __construct($name )
	{
		$this->label = null;
		$this->Attributes = array(
		    'name' => $name,
		    'id' => $name
		    );
	}
	public function Input(){return '';}
	public function Populate($Data=array()){}
	public function GetHtml()
	{
		$this->Link->IsButton = true;
		$html = '';
		$html .= $this->BuildLabel().PHP_EOL;
		$html .= $this->Link;
		return $html;
	}
}
class FormTextarea extends FormElement{
	protected $AllowedAttributes = array('name','id','type','value','style','class','size','placeholder','readonly','cols','rows','Quicktip');
	public function GetHtml()
	{

		$val = $this->Attributes['value'];
		unset($this->Attributes['value']);

		$this->ApplyValidationModification();
		$html = '';
		$html .= $this->BuildLabel().PHP_EOL;
		$html .= '<textarea ';
		$html .= $this->BuildAttributes();
		$html .= '>'.$val.'</textarea >';
		$html .= $this->GetErrorMessage();
		$html .= $this->QuicktipReference();
		return $html;
	}
}

class FormFactory{
	public static function Create($name,$type='text'){
		switch($type){
			default:
				throw new CodeException('FormElement '.$type.'does not exists');
			case 'text':
				return new FormInput($name);
			case 'textarea':
				return new FormTextarea($name);
			case 'custom':
				return new FormCustom($name);
			case 'select':
				return new FormSelect($name);
			case 'radio':
				return new FormRadio($name);
			case 'link':
				return new FormLink($name);
			case 'checkbox':
				$el = new FormCheckbox($name);
				$el->type = $type;
				return $el;
			case 'hidden':
			case 'password':
				$el = new FormInput($name);
				$el->type = $type;
				$el->name = $name;
				return $el;

			case 'submit':
			case 'reset':
			case 'button':

				$el = new FormButton($name);
				$el->type = $type;
				#$el->label = null;
				$el->value = $name;
				return $el;


		}
	}
	public static function FromArray($arr){

		$type = isset($arr['type']) ? $arr['type'] : 'text';
		switch($type){
			default:
				$el = self::Create($arr['name'],$type);
				$test = array('value','id','items','label','size','class','style','checked','float','quicktip','placeholder','text','InputHint','readonly','cols','rows');
				foreach($test as $t){
					if(isset($arr[$t]))
						$el->$t = $arr[$t];
				}


				if(isset($arr['validators'])){
					foreach($arr['validators'] as $v){
						$el->AddValidator($v);
					}
				}
				break;
			case 'link':
				$el = self::Create($arr['name'],$type);
				$el->float = true;
				$el->Link = $arr['link'];
				break;

		}
		return $el;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~ SECTION CLASS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
class FormSection{
	public $Elements = array();
	public $label = null;
	public $active = true;
	public $id = null;



	public function __construct(){
		$this->id = md5('FormSection'.time().rand());
	}

	public function ElementExists($name){

		return in_array($name,array_keys($this->Elements));
	}
	public function DeleteElement($name){
		if(isset($this->Elements[$name]))
			unset($this->Elements[$name]);
	}
	public function AddElement($el )
	{
		$this->Elements[$el->name] = $el;
	}
	public function AddElementFromArray($arr)
	{
		$this->Elements[$arr['name']] = FormFactory::FromArray($arr);
	}
	public function AddElementsFromArray($arrs)
	{
		foreach($arrs as $arr)
			$this->AddElementFromArray($arr);
	}
	public function GetHtml()
	{
		$first = true;
		$html = '<div class="FormRow">';
		foreach($this->Elements as $el ) {
			if((!$first) && (@!$el->Attributes['float'])){
				$html .= '</div>';
				$html .= '<div class="FormRow">';
			}
			$first = false;
			$html .= $el->GetHtml() .PHP_EOL;
		}
		$html .= '</div>';

		return $html;
	}
	public function Validate()
	{
		$result = true;
		if($this->active){
			foreach($this->Elements as $el ) {
				if ($el->Validate() === false )
					$result = false;
			}
		}
		return $result;
	}
	public function Populate($Data=array())
	{
		foreach($this->Elements as $key => $el ) {
			$this->Elements[$key]->Populate($Data);
		}
	}
}
// ~~~~~~~~~~~~~~~~~~~~~~~~~ FORM CLASS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
class Form {

	public $Action;
	public $Sections;
	public $id;

	public function __construct(){
		$this->Action = $_SERVER['SCRIPT_NAME'];
		$this->Sections['default'] = new FormSection();
		$this->id = md5('Form'.time().rand().'form');
	}



	public function GetHtml()
	{
		$html = '<form id="'.$this->id.'" method="post">'.PHP_EOL;
		foreach($this->Sections as $name=>$sec ) {
			if(count($sec->Elements)>0){
				$display = ' style="display: none;" ';
				if($sec->active)
					$display = '';
				$html .= '<div id="'.$sec->id.'" '.$display.' >';
				$html .= '<h3>'.$sec->label.'</h3>';
				$html .= $sec->GetHtml();
				$html .= '</div>';
			}

		}
		$html .= '<input type="hidden" value="formsend" name="formsend" id="formsend"/>';
		$html .= '</form>';
		return $html;
	}

	public function AddElementsFromArray($arr,$sectionname,$label = null){
		$new = false;
		if(!isset($this->Sections[$sectionname])){
			$this->Sections[$sectionname] = new FormSection();
			if($label !== null)
				$this->Sections[$sectionname]->label = $label;
			else
				$this->Sections[$sectionname]->label = $sectionname;
			$new = true;
		}
		$this->Sections[$sectionname]->AddElementsFromArray($arr);

	}
	public function AddElement($el,$sectionname='default'){
		if(!isset($this->Sections[$sectionname]))
			$this->Sections[$sectionname] = new FormSection();
		$this->Sections[$sectionname]->AddElement($el);
	}

	public function Input($name )
	{
		foreach($this->Sections as $section){
			if($section->ElementExists($name))
				return $section->Elements[$name]->Input();
		}
	}



	public function WasSent()
	{
		return (isset($_POST['formsend'] ) AND ($_POST['formsend'] === 'formsend' ) );
	}
	public function Validate()
	{
		$result = true;
		foreach($this->Sections as $sec ) {
			if ($sec->Validate() === false )
				$result = false;
		}
		if (!$result )
			throw new AppException('Eingaben überprüfen' );
	}
	public function Populate($Data=array())
	{
		foreach($this->Sections as $key => $el ) {
			$this->Sections[$key]->Populate($Data);
		}
	}
	public function AddDefaultActions($page='index',$action='index'){
		global $GlobalLocale;
		$this->AddElementsFromArray(
			array(
				array(
					'type' => 'submit',
					'name' => 'submit',
					'value' => $GlobalLocale->_('save'),
					'label'=>null
				),
				array(
					'type'	=> 'reset',
					'name' 	=> 'reset',
					'value' => $GlobalLocale->_('reset'),
					'float'	=> true,
					'label' => null
				),
				array(
					'type' => 'link',
					'link' => new Link($page, $action, array(), $GlobalLocale->_('back')),
					'name' => 'back',
					'float'	=>true
				)
			),
			'actions',
			'Aktionen'
		);
	}
}


#####################################################################################################


