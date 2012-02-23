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
                'foreignKey' => '{alias}_id',
                'autoCreate'=> false
            );
        }
        $this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], $settings);
                
        $this->settings[$Model->alias]['tableName'] = str_replace( '{useTable}', Inflector::singularize( $Model->useTable ), $this->settings[$Model->alias]['tableName'] ); 
        $this->settings[$Model->alias]['className'] = ucfirst( str_replace( '{alias}', Inflector::singularize( $Model->alias ) , $this->settings[$Model->alias]['className'] ) ); 
        $this->settings[$Model->alias]['foreignKey'] = strtolower( str_replace( '{alias}', Inflector::singularize( $Model->alias ) , $this->settings[$Model->alias]['foreignKey'] ) ); 

    }
    
    public function cleanup( Model $Model ) {
        //Nothing to do    
    }
    
    /**
    * Hook the beforeSave so that we can grab a copy of the record in
    * from the database into $this->revisionRecord before the update 
    * is perform on the table.
    * 
    * It's important to do this in two steps because another behavior 
    * could cancel the save operation.
    * 
    * @param Model $Model
    */
    public function beforeSave( Model $Model ) {
        
        if ( !empty( $Model->id ) ) {
            //This is an update
            extract( $this->settings[$Model->alias] );
            
            //backup the updated data that will be saved
            $tmp['id'] = $Model->id;
            $tmp['data'] = $Model->data;
            $tmp['recursive'] = $Model->recursive;
            
            //read the last state of the record from the table
            $Model->recursive = -1;
            unset( $this->revisionRecord );
            $prev_record = $Model->read( null, $Model->id );
            $this->revisionRecord[$className] = $prev_record[$Model->alias];
            
            //Restore orignal recursive depth and the updated data that will be saved
            $Model->recursive = $tmp['recursive'];
            $Model->data = $tmp['data'];

            //Copy the primary key over to the foreignkey field and unset the primary key
            $this->revisionRecord[$className][$foreignKey] = $Model->data[$Model->alias][$Model->primaryKey];
            unset( $this->revisionRecord[$className]['id'] );

        } else {
            //This is an insert
        }
        return true;
    }

    /**
    * Hook the afterSave callback so we can save the backup record ($this->revisionRecord)
    * to the revisions model/table.
    * 
    * It's important to do this in two steps because another behavior 
    * could cancel the save operation.
    * 
    * @param Model $Model
    * @param mixed $created
    */
    public function afterSave( Model $Model, $created ) {
        if ( $created ) {
            //Record was created
            //Do nothing on inserts
        } else {
            //Record was updated
            if ( !empty( $this->revisionRecord ) ) {
                $this->bindRevisionModel( &$Model );
                $Model->RevisionableRevision->save( $this->revisionRecord );
                $this->unBindRevisionModel( &$Model );
            }
        }

        return true;
        
    }
    
    public function beforeDelete( Model $Model, $cascade ) {
        if ( !empty( $Model->id ) ) {
            //This is a delete
            extract( $this->settings[$Model->alias] );
            
            //backup the current model state
            $tmp['id'] = $Model->id;
            $tmp['recursive'] = $Model->recursive;
            
            //read the last state of the record from the table
            $Model->recursive = -1;
            unset( $this->revisionRecord );
            $prev_record = $Model->read( null, $Model->id );
            $this->revisionRecord[$className] = $prev_record[$Model->alias];
            
            //Restore current model state
            $Model->recursive = $tmp['recursive'];

            //Copy the primary key over to the foreignkey field and unset the primary key
            $this->revisionRecord[$className][$foreignKey] = $Model->data[$Model->alias][$Model->primaryKey];
            unset( $this->revisionRecord[$className]['id'] );
        }
        return true;    
    }
    
    public function afterDelete( Model $Model ) {
        //Record was deleted
        if ( !empty( $this->revisionRecord ) ) {
            $this->bindRevisionModel( &$Model );
            $Model->RevisionableRevision->save( $this->revisionRecord );
            unset( $this->revisionRecord );
            $this->unBindRevisionModel( &$Model );
        }
        return true;    
    }

    protected function bindRevisionModel( $Model ) {
        extract( $this->settings[$Model->alias] );
        $Model->bindModel(array(
            'hasMany' => array(
                'RevisionableRevision' => array(
                    'className' => $className,
                    'foreignKey' => $foreignKey
                )
            )
        ));
    } 
    
    protected function unBindRevisionModel( $Model ) {
        $Model->unbindModel(array(
            'hasMany' => array(
                'RevisionableRevision'
            )
        ));
    }  
    
    
}