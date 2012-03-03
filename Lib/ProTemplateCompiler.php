<?php
/**
 * Copyright 2010-2011, PRONIQUE Software (http://pronique.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2011-2012, PRONIQUE Software (http://pronique.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * ProUtils Plugin
 * 
 * ProTemplateCompiler Class
 *
 * This class uses regex to prepare and compile text templates
 * 
 * @package ProUtils
 * @subpackage ProUtils.Lib
 */
class ProTemplateCompiler {
    protected $template;
    protected $rules = array();
    protected $tag = '{$tag}';
    
    public function __construct( $template='', $rules=array(), $tag='' ) {
        $this->template = $template;
        if (!empty( $tag ) ) {
            $this->tag = $tag;    
        }
        if( !empty( $rules ) && is_array( $rules ) ) {
            $this->rules = $rules;
            $this->prepare();
        }
    }
  
    protected function prepare( ) {
        $tmpl = $this->template;
        foreach( $this->rules as $key=>$rule ) {
            $tmpl = preg_replace_callback(
                $rule['match'],
                'ProTemplateCompiler::replaceWithVariableCallback', 
                $tmpl
            );               
        }     
        $this->template = $tmpl;
    } 
    
    /**
    * Takes associative array in, $data 
    * replaces template tags with match keys in data
    * and returns the compiled template as text
    * 
    * @param mixed $vars
    * @return string
    */
    public function compile( $dataArr ) {
        $dataArrFlat = Set::flatten($dataArr);

        $compiled_template = $this->template;
        foreach( $dataArrFlat as $key=>$val ) {
            $compiled_template = preg_replace(
                "/{\\$" . preg_quote($key) . "}/",
                $val, 
                $compiled_template
            );             
        }     
        return $compiled_template;
    }
    
    protected function replaceWithVariableCallback( $matches ) {
        //TODO Add support for custom Tags
        return $matches[1] . '{$' . $matches[2] . '}' . $matches[4];
    }    
}