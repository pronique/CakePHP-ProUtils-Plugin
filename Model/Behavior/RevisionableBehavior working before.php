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
 * ProUtils Revisionable Behavior
 * 
 * Maintains a revisions table that contains a versioned history of table data.
 *
 * Example Usage within your model
 * 
 *     public $actsAs = array( 'ProUtils.Revisionable' );
 * 
 * @package ProUtils
 * @subpackage PruUtils.Model.Behavior
 */

class RevisionableBehavior extends ModelBehavior {

/**
 * Initializes this behavior for the model $Model
 *
 * @param Model $Model
 * @param array $settigs list of settings to be used for this model
 * @return void
 */
    public function setup(Model &$Model, $settings = array()) {
        if (!isset($this->settings[$Model->alias])) {
            $this->settings[$Model->alias] = array(
                'className' => '{alias}Rev',
                'tableName' => '{useTable}_revs',
                'foreignKey' => 'record_id',
                'autoCreate'=> false
            );
        }
        $this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], $settings);
                
        $this->settings[$Model->alias]['tableName'] = str_replace( '{useTable}', Inflector::singularize( $Model->useTable ), $this->settings[$Model->alias]['tableName'] ); 
        $this->settings[$Model->alias]['className'] = ucfirst( str_replace( '{alias}', Inflector::singularize( $Model->useTable) , $this->settings[$Model->alias]['className'] ) ); 
        pr( $this->settings );
        
        
        $this->bindRevisionModel( &$Model );
    }
    
    public function beforeSave( Model $Model ) {
        
        if ( !empty( $Model->id ) ) {
            //This is an update
            $this->bindRevisionModel( &$Model );
            extract( $this->settings[$Model->alias] );
            unset( $this->revisionRecord );
            $this->revisionRecord[$className] = $Model->data[$Model->alias];
            $this->revisionRecord[$className][$foreignKey] = $Model->data[$Model->alias][$Model->primaryKey];
            unset( $this->revisionRecord[$className]['id'] );

        } else {
            //This is an insert
        }
        return true;
    }

    public function afterSave( Model $Model, $created ) {
        if ( $created ) {
            //Record was created
        } else {
            //Record was updated
            if ( !empty( $this->revisionRecord ) ) {
                $this->bindRevisionModel( &$Model );
                $Model->Revision->save( $this->revisionRecord );
            }
        }

        return true;
        
    }
    
    protected function bindRevisionModel( $Model ) {
        extract( $this->settings[$Model->alias] );
        $Model->bindModel(array(
            'hasMany' => array(
                'Revision' => array(
                    'className' => $className,
                    'foreignKey' => $foreignKey
                )
            )
        ));
    }    
    protected function _createTable() {
        pr('Try to create the table');
    }
    
    
}