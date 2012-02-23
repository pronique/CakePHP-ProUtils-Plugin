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
 * ProUtils Event Dispatcher Behavior
 * 
 * Uses the CakePHP 2.1 Events System  to dispatch events 
 * for all of the common Model callbacks.
 * 
 * Fired Events                 Default
 * Model.{alias}.beforeFind     onPropagationStop = continue
 * Model.{alias}.afterFind      onPropagationStop = continue
 * Model.{alias}.beforeSave     onPropagationStop = abort
 * Model.{alias}.afterSave      onPropagationStop = continue
 * Model.{alias}.beforeDelete   onPropagationStop = abort
 * Model.{alias}.afterDelete    onPropagationStop = continue
 * Model.{alias}.beforeValidate onPropagationStop = abort
 * 
 * Config parameters can disable certain events alltogether, or
 * change the behavior of the Model when an Event is canceled
 * by a listener.
 *
 * Example Usage within your model
 *     public $actsAs = array( 'ProUtils.EventDispatcher' );
 * 
 * @package ProUtils
 * @subpackage PruUtils.Model.Behavior
 */
 
App::uses('CakeEvent', 'Event');

class EventDispatcherBehavior extends ModelBehavior {

/**
 * Event Dispatcher behavior settings
 *
 * @var array
 */
    public $settings = array();

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
                'beforeFind' => array(
                    'disable'=>false,
                    'eventName'=>'Model.{alias}.beforeFind',
                    'onStopPropagation'=>'continue'
                ),
                'afterFind' => array(
                    'disable'=>false,
                    'eventName'=>'Model.{alias}.afterFind',
                    'onStopPropagation'=>'continue'
                ),
                'beforeSave' => array(
                    'disable'=>false,
                    'eventName'=>'Model.{alias}.beforeSave',
                    'onStopPropagation'=>'abort'
                ),
                'afterSave' => array(
                    'disable'=>false,
                    'eventName'=>'Model.{alias}.afterSave',
                    'onStopPropagation'=>'continue'
                ),
                'beforeDelete' => array(
                    'disable'=>false,
                    'eventName'=>'Model.{alias}.beforeDelete',
                    'onStopPropagation'=>'abort'
                ),
                'afterDelete' => array(
                    'disable'=>false,
                    'eventName'=>'Model.{alias}.afterDelete',
                    'onStopPropagation'=>'continue'
                ),
                'beforeValidate' => array(
                    'disable'=>false,
                    'eventName'=>'Model.{alias}.beforeValidate',
                    'onStopPropagation'=>'abort'
                )
            );
        }
        $this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], $settings);
        
        foreach( $this->settings[$Model->alias] as $key=>$val ) {
            $this->settings[$Model->alias][$key]['eventName'] = str_replace( '{alias}', $Model->alias, $val['eventName'] );
        }   
    }
    
    
    /**
    * Dispatch the event Model.ModelName.beforeFind
    * 
    * @param Model $Model
    * @param mixed $query
    */
    public function beforeFind( Model $Model, $query ) {
        $cfg = $this->settings[$Model->alias][__FUNCTION__];
        if ( $cfg['disable'] !== true ) {
            $Event = new CakeEvent(
                $cfg['eventName'], 
                $Model, 
                array( 'query' => $query )
            );
            
            $Model->getEventManager()->dispatch( $Event );
            
            switch ( $cfg['onStopPropagation'] ) {
                case 'abort': if ( $Event->isStopped() ) { return false; } break;
                case 'die': die('Execution halted because the event ' . $cfg['eventName'] . ' was canceled.'); break;
                case 'exception': throw new Exception('The event ' . $cfg['eventName']  . ' was canceled.'); break;
                case 'continue':
                default:
            } //end switch
        } //end if
        return $query;
    }
    
    /**
    * Dispatch the event Model.{alias}.afterFind
    * 
    * @param Model $Model
    * @param mixed $results
    * @param mixed $primary
    */
    public function afterFind( Model $Model, $results, $primary ) {
        $cfg = $this->settings[$Model->alias][__FUNCTION__];
        if ( $cfg['disable'] !== true ) {
            $Event = new CakeEvent(
                $cfg['eventName'],
                $Model, 
                array( 'results' => $results, 'primary'=> $primary )
            );
            
            $Model->getEventManager()->dispatch( $Event );
            
            switch ( $cfg['onStopPropagation'] ) {
                case 'abort': if ( $Event->isStopped() ) { return false; } break;
                case 'die': die('Execution halted because the event ' . $cfg['eventName'] . ' was canceled.'); break;
                case 'exception': throw new Exception('The event ' . $cfg['eventName']  . ' was canceled.'); break;
                case 'continue':
                default:
            } //end switch
        } //end if    
        return $results;      
    }
    
    /**
    * Dispatch the event Model.ModelName.beforeSave
    * 
    * @param Model $Model
    * @param mixed $query
    */
    public function beforeSave( Model $Model ) {
        $cfg = $this->settings[$Model->alias][__FUNCTION__];
        if ( $cfg['disable'] !== true ) {
            $Event = new CakeEvent(
                $cfg['eventName'], 
                $Model, 
                array( )
            );
            
            $Model->getEventManager()->dispatch( $Event );
            
            switch ( $cfg['onStopPropagation'] ) {
                case 'abort': if ( $Event->isStopped() ) { return false; } break;
                case 'die': die('Execution halted because the event ' . $cfg['eventName'] . ' was canceled.'); break;
                case 'exception': throw new Exception('The event ' . $cfg['eventName']  . ' was canceled.'); break;
                case 'continue':
                default:
            } //end switch
        } //end if
        return true;
    }
    
     /**
    * Dispatch the event Model.{alias}.afterSave
    * 
    * @param Model $Model
    * @param mixed $results
    * @param mixed $primary
    */
    public function afterSave( Model $Model, $created ) {
        $cfg = $this->settings[$Model->alias][__FUNCTION__];
        if ( $cfg['disable'] !== true ) {
            $Event = new CakeEvent(
                $cfg['eventName'],
                $Model, 
                array( 
                    'created' => $created, 
                    'operation' => ( $created ? 'insert' : 'update')
                )
            );
            
            $Model->getEventManager()->dispatch( $Event );
            
            switch ( $cfg['onStopPropagation'] ) {
                case 'abort': if ( $Event->isStopped() ) { return false; } break;
                case 'die': die('Execution halted because the event ' . $cfg['eventName'] . ' was canceled.'); break;
                case 'exception': throw new Exception('The event ' . $cfg['eventName']  . ' was canceled.'); break;
                case 'continue':
                default:
            } //end switch
        } //end if    
        return true;      
    }
    
    /**
    * Dispatch the event Model.ModelName.beforeDelete
    * 
    * @param Model $Model
    * @param mixed $query
    */
    public function beforeDelete( Model $Model, $cascade ) {
        $cfg = $this->settings[$Model->alias][__FUNCTION__];
        if ( $cfg['disable'] !== true ) {
            $Event = new CakeEvent(
                $cfg['eventName'], 
                $Model, 
                array( 'cascade' => $cascade )
            );
            
            $Model->getEventManager()->dispatch( $Event );
            
            switch ( $cfg['onStopPropagation'] ) {
                case 'abort': if ( $Event->isStopped() ) { return false; } break;
                case 'die': die('Execution halted because the event ' . $cfg['eventName'] . ' was canceled.'); break;
                case 'exception': throw new Exception('The event ' . $cfg['eventName']  . ' was canceled.'); break;
                case 'continue':
                default:
            } //end switch
        } //end if
        return true;
    }
        
    /**
    * Dispatch the event Model.{alias}.afterDelete
    * 
    * @param Model $Model
    */
    public function afterDelete( Model $Model ) {
        $cfg = $this->settings[$Model->alias][__FUNCTION__];
        if ( $cfg['disable'] !== true ) {
            $Event = new CakeEvent(
                $cfg['eventName'],
                $Model, 
                array( )
            );
            
            $Model->getEventManager()->dispatch( $Event );
            
            switch ( $cfg['onStopPropagation'] ) {
                case 'abort': if ( $Event->isStopped() ) { return false; } break;
                case 'die': die('Execution halted because the event ' . $cfg['eventName'] . ' was canceled.'); break;
                case 'exception': throw new Exception('The event ' . $cfg['eventName']  . ' was canceled.'); break;
                case 'continue':
                default:
            } //end switch
        } //end if    
        return true;      
    }

    /**
    * Dispatch the event Model.ModelName.beforeValidate
    * 
    * @param Model $Model
    * @param mixed $query
    */
    public function beforeValidate( Model $Model ) {
        $cfg = $this->settings[$Model->alias][__FUNCTION__];
        if ( $cfg['disable'] !== true ) {
            $Event = new CakeEvent(
                $cfg['eventName'], 
                $Model, 
                array( )
            );
            
            $Model->getEventManager()->dispatch( $Event );
            
            switch ( $cfg['onStopPropagation'] ) {
                case 'abort': if ( $Event->isStopped() ) { return false; } break;
                case 'die': die('Execution halted because the event ' . $cfg['eventName'] . ' was canceled.'); break;
                case 'exception': throw new Exception('The event ' . $cfg['eventName']  . ' was canceled.'); break;
                case 'continue':
                default:
            } //end switch
        } //end if
        return true;
    }  
    
    /**
    * TODO add event for onError callback
    * 
    */
    /* public function onError( $Model ) {
        
    } */  
}