<?php

/*~ Pagelet.php
.---------------------------------------------------------------------------.
|   File: Pagelet.php class file                                        		|
|   Version: 1.0                                                            |
| ------------------------------------------------------------------------- |
|   Author : Kenny Flashlight (KFlash)					                    |
|   Copyright (c) 2013, Kenny Flashlight. All Rights Reserved.         		|
'---------------------------------------------------------------------------'
*/

class Pagelet {

    private $name;
    private $enable = TRUE;
    private $priority;
    private $content;
    private $config = array();


    /**
        * List of css files which this pagelet needs
        * @var array
        */
    private $css_files = array();
    
    /**
        * List of javascript files which this pagelet needs
        * @var array
        */
    private $js_files = array();
    
    
    /**
        * Tells if the placeholder is done with a <div /> or a <span /> tag. True if span
        * @var boolean
        */
    public $use_span = false;

    
    public function __construct($name,$config = array()) {
        $this->name = $name;
        $this->config = array_merge( 
            array(
                'priority' 		    => 0,
                'enabled' 		    => TRUE,
                'dom_inserted' 		=> TRUE,
                'css_inserted' 		=> TRUE,
                'js_inserted' 		=> TRUE,
                'element' 		    => 'div',
                'attribute' 		=> '',
            ),
            $config
        );
    }
    
    public function config( $name, $value = null )
    {
        if( func_num_args() === 1 )
        {

            if( is_array( $name ) )
            {
                $this->config = array_merge( $this->config, $name );
            }
            else
            {
                return in_array( $name, array_keys( $this->config ) ) ? $this->config[ $name ] : null;
            }
        }
        else
        {
            $this->config[ $name ] = $value;
        }
    }
    public function getName(){
        return $this->name;
    }
    public function getPriority(){
        return $this->config('priority');
    }
    /*
        Add CSS files to the pagelet
    
    */

    public function addCss($file) {

        $this->css_files[] = $file;
    }

    /*
        Add content to the pagelet
    
    */
    
    public function setContent($content) {
        $this->content = $content;
    }
    
    public function getContent() {
        return $this->content;
    }

    /*
        Add Javascript files to the pagelet
    
    */

    public function addJavascript($file) {
        $this->js_files[] = $file;
    }
    
    /*
        Render the paglet data and prepare it for output
    
    */
    
    public function getData() {
        $data['id'] =  $this->name;
        $data['content'] = $this->content; 
        $data['enable'] = $this->config('enabled'); 
        $data['css'] = $this->css_files;
        $data['js'] = $this->js_files;
        return $data;	
    }
    public function getJsFiles(){
        return $this->js_files;
    }
    public function getCssFiles(){
        return $this->css_files;
    }
    public function isSpan(){
        return $this->use_span;
    }
    public function enable(){
        $this->config('enabled',TRUE);
    }
    public function isEnabled(){
        return $this->config('enabled');
    }
    public function disable(){
        $this->config('enabled',FALSE);
    }
} //EOF ?>