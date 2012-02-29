# ProUtils Plugin for CakePHP 2.1 #

The ProUtils plugin is a growing collection of useful components, behaviors, helpers, and libraries.

> Note: This CakePHP plugin is designed to work with CakePHP 2.1, this requirement is dictated by the implementation
> of CakePHP 2.1's Events System.  Most components should work with CakePHP 2.0 with little or no modifications. 
>The plugin is not backwards compatiable with CakePHP 1.3 or 1.2 and never will be.

## Behaviors

* CsvExport           - adds the ability to export csv data from the model.
* CustomSluggable     - dynamic slugs generated from multiple fieldsx
* EventDispatcher     - adds CakeEvent dispatchers for all common Model callbacks.
* Revisionable        - maintains a versioned copy of each record change in another table.

## Controllers 

* CrudAppController   - extends AppController, and provides abstract index,view,add,edit,delete methods similar to a scaffold enable controller
* CrudController      - same as CrudAppController, but is meant to be extended by AppController. 

## Libraries ##

* ProTemplateCompiler - Compile a document from a template and data array.

## Documentation ##

[ProUtils Wiki](https://github.com/pronique/CakePHP-ProUtils-Plugin/wiki)

## Installation ##

    sudo git clone https://github.com/pronique/CakePHP-ProUtils app/Plugin/ProUtils
    
    //Enable plugin in app/Config/bootstrap.php
    CakePlugin::load('ProUtils');
    
## Requirements ##

* PHP version: PHP 5.3+
* CakePHP version: 2.1

## License ##

Copyright 2011-2012, [PRONIQUE Software](http://pronique.com)

Licensed under [The MIT License](http://www.opensource.org/licenses/mit-license.php)<br/>
Redistributions of files must retain the above copyright notice.

--------------------------------------------------------------------------
ProUtils is Open Source Software created and managed by PRONIQUE Software.

http://www.pronique.com
