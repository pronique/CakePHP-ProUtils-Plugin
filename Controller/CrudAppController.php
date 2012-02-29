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
 
App::uses('AppController', 'Controller');

/**
 * ProUtils Plugin
 * 
 * Crud Controller
 *
 * Controller with abstract index,view,add,edit,delete methods
 *
 * @package ProUtils
 * @subpackage ProUtils.Controller   
 */
 
class CrudAppController extends AppController {

/**
 * abstracted index method
 *
 * @return void
 */
    public function index() {
        $this->{$this->modelClass}->recursive = 0;
        $this->set( Inflector::variable($this->name), $this->paginate() );
    }

/**
 * abstract view method
 *
 * @param string $id
 * @return void
 */
    public function view($id = null) {
        $this->{$this->modelClass}->id = $id;
        if (!$this->{$this->modelClass}->exists()) {
            throw new NotFoundException(__('Invalid ' . Inflector::humanize( $this->{$this->modelClass}->name ) ));
        }
        $this->set( Inflector::variable( $this->modelClass ), $this->{$this->modelClass}->read(null, $id));
    }
        
/**
 * abstracted, slug aware, view method
 *
 * @param string $id
 * @return void
 */
    public function slug($id = null) {
        $slug_field = 'slug';
        $redirect_code = 301;
        $human_model = Inflector::humanize( $this->{$this->modelClass}->name );
        
        //find by id or slug
        $record = $this->{$this->modelClass}->find('first', 
            array(
                'conditions'=>array( 
                    'OR'=>array(
                        $this->modelClass . '.id'=> $id,
                        $this->modelClass . '.' . $slug_field . ' LIKE'=> $id . '%'
                    )
                )
            ) 
        );
            
        if (empty($record) ) {
            throw new NotFoundException(__('Invalid %s', $human_model ) );
        }
        
        //301 redirect on numeric id or partial slug match
        if ( !empty( $record[$this->modelClass][$slug_field] ) ) { 
            if ( $record[$this->modelClass][$slug_field] != $id  ) {
                $this->redirect( array('action'=>'slug', $record[$this->modelClass][$slug_field]), $redirect_code );
            }
        }
        
        $this->set( Inflector::variable( $this->modelClass ), $record );
        
        $this->render('view');
    }
    
 /**
 * abstracted add method
 *
 * @return void
 */
    public function add() {
        $human_model = Inflector::humanize( $this->{$this->modelClass}->name );
        if ($this->request->is('post')) {
            $this->{$this->modelClass}->create();
            if ($this->{$this->modelClass}->save($this->request->data)) {
                $this->Session->setFlash(__('The %s has been saved', strtolower($human_model)));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The %s could not be saved. Please, try again.', strtolower($human_model)));
            }
        }

        foreach( $this->{$this->modelClass}->belongsTo as $assocName => $assocData ) {
            $varName = Inflector::variable(Inflector::pluralize(
                preg_replace('/(?:_id)$/', '', $assocData['foreignKey'])
            ));
            $this->set(  $varName, $this->{$this->modelClass}->$assocName->find('list') );             
        }
        
        foreach ( $this->{$this->modelClass}->hasAndBelongsToMany as $assocName => $assocData) {
            $varName = Inflector::variable(Inflector::pluralize($assocName));
            $this->set( $varName, $this->{$this->modelClass}->$assocName->find('list') );
        }
    }
    
/**
 * abstract edit method
 *
 * @param string $id
 * @return void
 */
    public function edit($id = null) {
        $human_model = Inflector::humanize( $this->{$this->modelClass}->name );
        $this->{$this->modelClass}->id = $id;
        if (!$this->{$this->modelClass}->exists()) {
            throw new NotFoundException(__('Invalid %s', strtolower($human_model) ));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->{$this->modelClass}->save($this->request->data)) {
                $this->Session->setFlash(__('The %s has been saved', strtolower($human_model)));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The %s could not be saved. Please, try again.', strtolower($human_model)));
            }
        } else {
            $this->request->data = $this->{$this->modelClass}->read(null, $id);
        }
        
        foreach( $this->{$this->modelClass}->belongsTo as $assocName => $assocData ) {
            $varName = Inflector::variable(Inflector::pluralize(
                preg_replace('/(?:_id)$/', '', $assocData['foreignKey'])
            ));
            $this->set(  $varName, $this->{$this->modelClass}->$assocName->find('list') );             
        }
        
        foreach ( $this->{$this->modelClass}->hasAndBelongsToMany as $assocName => $assocData) {
            $varName = Inflector::variable(Inflector::pluralize($assocName));
            $this->set( $varName, $this->{$this->modelClass}->$assocName->find('list') );
        }

    }  
    
/**
 * abstract delete method
 *
 * @param string $id
 * @return void
 */
    public function delete($id = null) {
        $human_model = Inflector::humanize( $this->{$this->modelClass}->name );
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $this->{$this->modelClass}->id = $id;
        if (!$this->{$this->modelClass}->exists()) {
            throw new NotFoundException(__('Invalid %s', strtolower($human_model) ));
        }
        if ($this->{$this->modelClass}->delete()) {
            $this->Session->setFlash(__('%s deleted', ucfirst( strtolower($human_model)) ));
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('%s was not deleted', ucfirst( strtolower($human_model)) ));
        $this->redirect(array('action' => 'index'));
    } 
}
