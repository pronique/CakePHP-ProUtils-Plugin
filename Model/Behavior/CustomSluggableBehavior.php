<?php
/**
 * Copyright 2011-2012, PRONIQUE Softeare (http://pronique.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2011-2012, PRONIQUE Software (http://pronique.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */


App::import('Core', 'Multibyte');
App::uses('ModelBehavior', 'Model');
App::uses('ProTemplateCompiler', 'ProUtils.Lib');

/**
 * ProUtils Plugin
 *
 * ProUtils Custom Sluggable Behavior
 *
 * Allows for dynamic slugs by combining data from multiple fields
 * 'label'=>'{$part_number} {$name}'
 * 
 * @package utils
 * @subpackage ProUtils.Model.Behavior
 */
class CustomSluggableBehavior extends ModelBehavior {

/**
 * Settings to configure the behavior
 *
 * @var array
 */
	public $settings = array();

/**
 * Default settings
 *
 * label 		- The field or tempate used to generate the slug from
 * slug 		- The field to store the slug in
 * scope 		- conditions for the find query to check if the slug already exists
 * separator 	- the character used to separate the words in the slug
 * length		- the maximum length of the slug
 * unique		- check if the slug is unique
 * update		- update the slug or not
 * trigger		- defines a property in the model that has to be true to generate the slug
 *
 * Note that trigger will temporary bypass update and act like update is set to true.
 *
 * @var array
 */
	protected $_defaults = array(
		'label' => 'title',
		'slug' => 'slug',
		'scope' => array(),
		'separator' => '_',
		'length' => 255,
		'unique' => true,
		'update' => false,
		'trigger' => false);

/**
 * Initiate behaviour
 *
 * @param object $Model
 * @param array $settings
 */
	public function setup(Model $Model, $settings = array()) {
		$this->settings[$Model->alias] = array_merge($this->_defaults, $settings);
	}

/**
 * beforeSave callback
 *
 * @param object $Model
 */
	public function beforeSave(Model $Model) {
		$settings = $this->settings[$Model->alias];
		if (is_string($this->settings[$Model->alias]['trigger'])) {
			if ($Model->{$this->settings[$Model->alias]['trigger']} != true) {
				return true;
			}
		}

		if (empty($Model->data[$Model->alias])) {
			return true;
		//} else if (empty($Model->data[$Model->alias][$this->settings[$Model->alias]['label']])) {
			//return true;
		} else if (!$this->settings[$Model->alias]['update'] && !empty($Model->id) && !is_string($this->settings[$Model->alias]['trigger'])) {
			return true;
		}

        $slug = $this->_buildSlug( $Model );
        
		if (method_Exists($Model, 'beforeSlugGeneration')) {
			$slug = $Model->beforeSlugGeneration($slug, $settings['separator']);
		}

		$settings = $this->settings[$Model->alias];
		$slug = $this->multibyteSlug($Model, $slug, $settings['separator']);

		if ($settings['unique'] === true || is_array($settings['unique'])) {
			$slug = $this->makeUniqueSlug($Model, $slug);
		}

		if (!empty($Model->whitelist) && !in_array($settings['slug'], $Model->whitelist)) {
			$Model->whitelist[] = $settings['slug'];
		}
		$Model->data[$Model->alias][$settings['slug']] = $slug;
		
		return true;
	}

/**
 * Searche if the slug already exists and if yes increments it
 *
 * @param object $Model
 * @param string the raw slug
 * @return string The incremented unique slug
 * 
 */
	public function makeUniqueSlug(Model $Model, $slug = '') {
		$settings = $this->settings[$Model->alias];
		$conditions = array();
		if ($settings['unique'] === true) {
			$conditions[$Model->alias . '.' . $settings['slug'] . ' LIKE'] = $slug . '%';
		} else if (is_array($settings['unique'])) {
			foreach ($settings['unique'] as $field) {
				$conditions[$Model->alias . '.' . $field] = $Model->data[$Model->alias][$field];
			}
			$conditions[$Model->alias . '.' . $settings['slug'] . ' LIKE'] = $slug . '%';
		}

		if (!empty($Model->id)) {
			$conditions[$Model->alias . '.' . $Model->primaryKey . ' !='] = $Model->id;
		}

		$conditions = array_merge($conditions, $settings['scope']);

		$duplicates = $Model->find('all', array(
			'recursive' => -1,
			'conditions' => $conditions,
			'fields' => array($settings['slug'])));

		if (!empty($duplicates)) {
			$duplicates = Set::extract($duplicates, '{n}.' . $Model->alias . '.' . $settings['slug']);
			$startSlug = $slug;
			$index = 1;

			while ($index > 0) {
				if (!in_array($startSlug . $settings['separator'] . $index, $duplicates)) {
					$slug = $startSlug . $settings['separator'] . $index;
					$index = -1;
				}
				$index++;
			}
		}
		return $slug;
	}
	
/**
 * Generates a slug from a (multibyte) string
 *
 * @param object $Model
 * @param string $string
 * @return string
 */
	public function multibyteSlug(Model $Model, $string = null) {
		$str = mb_strtolower($string);
		$str = preg_replace('/\xE3\x80\x80/', ' ', $str);
		$str = preg_replace('[\'s ]', 's ', $str);
		$str = str_replace($this->settings[$Model->alias]['separator'], ' ', $str);
		$str = preg_replace( '#[:\#\*"()~$^{}`@+=;,<>!&%\.\]\/\'\\\\|\[]#', "\x20", $str );
		$str = str_replace('?', '', $str);
		$str = trim($str);
		$str = preg_replace('#\x20+#', $this->settings[$Model->alias]['separator'], $str);
		return $str;
	}
    
    /**
    * Compile the behavior's 'label' configuration
    * 
    * @param Model $Model
    * @return string
    */
    protected function _buildSlug( Model $Model ) {
        $settings = $this->settings[$Model->alias];
        
        //New instance of compiler, template is the label
        $TC = new ProTemplateCompiler( $settings['label'] );
        
        //allow some model attributes to be used as variables in slug label
        $attribs = array( 
            'hasOne','hasMany', 'belongsTo', 'hasAndBelongsToMany', 'actsAs',
            'alias', 'displayField', 'primaryKey', 'name', 'table', 'tablePrefix'
        );
        foreach ( $attribs as $attrib ) {
            $class_vars['Model::' . $attrib ] = $Model->$attrib;    
        }

        $model_data = $Model->data;
        //some extra work to make sure {$id} is removed from slug on insert
        if ( !empty($Model->id ) && empty($model_data[$Model->alias]['id'] ) ) {
            $model_data[$Model->alias]['id'] = $Model->id;
        } elseif ( empty($Model->id ) && empty($model_data[$Model->alias]['id'] ) ) {
            $model_data[$Model->alias]['id'] = '';
        }
        
        /**
        * Restructure array so template can be {$name} or {$Model.name}, and even
        * better ${AssociatedModel.field}
        */
        $this_model_data = $model_data[$Model->alias];
        $vars = array_merge( $class_vars, $model_data, $this_model_data );
        $slug = $TC->compile( $vars );
        
        //if something goes wrong with the template then fallback to 
        //grabbing value from Model's displayField
        if ( empty($slug) ) {
            $slug = $Model->data[$Model->alias][$Model->displayField];
        }
        
        return $slug;
    }
}
