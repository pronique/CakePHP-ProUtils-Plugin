# ProUtils Plugin for CakePHP 2.1 #

The ProUtils plugin is a growing collection of useful components, behaviors, helpers, and libraries. Here we will list and detail each component.

## Behaviors

* CsvExport        - adds the ability to export csv data from the model.
* EventDispatcher        - adds CakeEvent dispatchers for all common Model callbacks.

### CsvExportBehavior ###

Adds the exportCSV method to the model, exports all records.

You can configure the CsvExport behavior using these options:

* delimiter - The delimiter for the values, default is ;
* enclosure - The enclosure, default is "
* encoding - utf8 or default encoding, default is utf8
* max_execution_time - Extend the execution time for larger datasets, default is 360

The main method of this behavior is
    
    $csvdata = $this->Model->exportCSV();

Enable within your model
    
    public $actsAs = array( 'ProUtils.CsvExport' );

Export data and present as a file download from your controller

    function export() {
        $this->autoRender = false;
        $modelClass = $this->modelClass;
        $this->response->type('Content-Type: text/csv');
        $this->response->download( strtolower( Inflector::pluralize( $modelClass ) ) . '.csv' );
        $this->response->body( $this->$modelClass->exportCSV() );
    }
    
### EventDispatcherBehavior ###

Uses the CakePHP 2.1 Events System to dispatch events for all of the common Model callbacks.
 
Fired Events          

* Model.{alias}.beforeFind     onPropagationStop = continue
* Model.{alias}.afterFind      onPropagationStop = continue
* Model.{alias}.beforeSave     onPropagationStop = abort
* Model.{alias}.afterSave      onPropagationStop = continue
* Model.{alias}.beforeDelete   onPropagationStop = abort
* Model.{alias}.afterDelete    onPropagationStop = continue
* Model.{alias}.beforeValidate onPropagationStop = abort

Config parameters can disable certain events alltogether, or
change the behavior of the Model when an Event is canceled
by a listener.

Example Usage within your model
    public $actsAs = array( 'ProUtils.EventDispatcher' );
    
## Libraries ##

* ProTemplateCompiler       - Compile a document from a template and data array.

## Installation ##

    git clone https://github.com/pronique/CakePHP-ProUtils app/Plugin/ProUtils
    
    //Enable plugin in app/Config/bootstrap.php
    CakePlugin::load('ProUtils');
    
## Requirements ##

* PHP version: PHP 5.3+
* CakePHP version: 2.1

## License ##

Copyright 2011-2012, [Cake Development Corporation](http://pronique.com)

Licensed under [The MIT License](http://www.opensource.org/licenses/mit-license.php)<br/>
Redistributions of files must retain the above copyright notice.

--------------------------------------------------------------------------
ProUtils is Open Source Software created and managed by PRONIQUE Software.

http://www.pronique.com
