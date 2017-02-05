<?php
class BigPipe  {

    protected $pagelets = array();
    protected $pagelet_count = 0;
    protected $enabled = TRUE;

	
	protected $queued_scripts = array();
	protected $script_queue = array();
    protected $scripts = array();
    
	protected $queued_css = array();
    protected $css_queue = array();
	protected $css = array();

    private $_config = array();
    
    public function __construct($config = array() ){
        
        //Merge application config
        $this->_config = array_merge( 
            array(
                'registered_path' => realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'registered_resource' . DIRECTORY_SEPARATOR
            ),
            $config
        );

        $this->css = new DependencySort(new ResourceDataCache('css',$this->config('registered_path')));
        $this->scripts = new DependencySort(new ResourceDataCache('js',$this->config('registered_path')));
    }
    
    public function config( $name, $value = null )
    {
        if( func_num_args() === 1 )
        {
            return in_array( $name, array_keys( $this->_config ) ) ? $this->_config[ $name ] : null;
        }
        else
        {
            $this->_config[ $name ] = $value;
        }
    }
    
    /*  get registered resource  */
    private function getRegisteredResource($name,$suffix){
        $file = $this->config('registered_path') . $name . '-' . $suffix . '.json';
        if(file_exists($file)){
            return json_decode(file_get_contents($file),TRUE);
        }
        return FALSE;
    }
    /*  register a resource  */
    private function registerResource($name,$info,$suffix){
        $file = $this->config('registered_path') . $name . '-' . $suffix . '.json';
        file_put_contents($file, json_encode($info,FALSE));
        return $info;
    }

	/**
	 * Register a JS resource for enqueue later.
	 * 
	 * @param string $name Name of the script. Should be unique..
	 * @param string $source Full URL of the script, or path of the script relative to root directory.
	 * @param array $depends An array of registered script handles this script depends on..
	 * @param string|bool|null $version (Optional) String specifying script version number, 
     *        if it has one, which is added to the URL as a query string for cache busting purposes. 
     *        If version is set to false, a version number is automatically added equal 
     *        to current installed WordPress version. If set to null, no version is added.
	 */
	public function registerScript($name, $source, $depends = array(), $version = NULL){
        $info = array(
            'name'      => $name,
            'source'    => $source,
            'depends'    => array(),
            'version'    => filemtime(__FILE__),
        );
        if($registered = $this->scripts->getRegisteredResource($name)){
            $info = array_merge($info,$registered);
        }
        $info['source'] = $source; //make sure $source don't get override
        if(!empty($depends)){
            $info['depends'] = $depends;
        }
        if(!empty($version)){
            $info['version'] = $version;
        }
        return $this->registerResource($name,$info,'js');
	}
    
	/**
	 * Add a JS resource to the queue.
	 * 
	 * @param string $name Name of the script. Should be unique..
	 * @param string $source Full URL of the script, or path of the script relative to root directory.
	 * @param array $depends An array of registered script handles this script depends on..
	 * @param string|bool|null $version (Optional) String specifying script version number, 
     *        if it has one, which is added to the URL as a query string for cache busting purposes. 
     *        If version is set to false, a version number is automatically added equal 
     *        to current installed WordPress version. If set to null, no version is added.
	 */
	public function enqueueScript($name, $source = '', $depends = array(), $version = NULL){
        $info = array(
            'name'      => $name,
            'source'    => $source,
            'depends'    => array(),
            'version'    => filemtime(__FILE__),
        );

        if($registered = $this->scripts->getRegisteredResource($name)){
            $info = array_merge($info,$registered);
        }

        if(!empty($source)){
            $info['source'] = $source;
        }

        if(!empty($depends)){
            $info['depends'] = $depends;
        }

        if(!empty($version)){
            $info['version'] = $version;
        }

        if(!empty($info['source'])){
            $this->scripts->enqueue($info['name'],array(
                'id' => $info['name'] . '-js',
                'src' => $info['source'] . '?v=' . $info['version'],
                'depends' => $info['depends']
            ),$info['depends']);
        }

        return $info;
	}
    
    public function getJsResource(){
        return $this->scripts->sort();
    }

	/**
	 * Register a JS resource for enqueue later.
	 * 
	 * @param string $name Name of the script. Should be unique..
	 * @param string $source Full URL of the script, or path of the script relative to root directory.
	 * @param array $depends An array of registered script handles this script depends on..
	 * @param string|bool|null $version String specifying script version number, 
     *        if it has one, which is added to the URL as a query string for cache busting purposes. 
     *        If version is set to false, a version number is automatically added equal 
     *        to current installed WordPress version. If set to null, no version is added.
	 * @param string $media The media for which this cssheet has been defined. Accepts media types like 'all', 
     *       'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'
	 */
	public function registerStyle($name, $source, $depends = array(), $version = NULL,$media = 'all'){
        $info = array(
            'name'      => $name,
            'source'    => $source,
            'depends'    => array(),
            'version'    => filemtime(__FILE__),
            'media'     => $media,
        );

        if($registered = $this->css->getRegisteredResource($name)){
            $info = array_merge($info,$registered);
        }

        if(!empty($depends)){
            $info['depends'] = $depends;
        }
        if(!empty($version)){
            $info['version'] = $version;
        }
        $info = array_merge($info,array(
            'source' => $source,
            'media'     => $media
        ));

        return $this->registerResource($name,$info,'css');
	}
    
	/**
	 * Add a CSS resource to the queue.
	 * 
	 * @param string $name Name of the script. Should be unique..
	 * @param string $source Full URL of the script, or path of the script relative to root directory.
	 * @param array $depends An array of registered script handles this script depends on..
	 * @param string|bool|null String specifying script version number, 
     *        if it has one, which is added to the URL as a query string for cache busting purposes. 
     *        If version is set to false, a version number is automatically added equal 
     *        to current installed WordPress version. If set to null, no version is added.
	 * @param string $media The media for which this cssheet has been defined. Accepts media types like 'all', 
     *       'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'
	 */

	public function enqueueStyle($name, $source = '', $depends = array(), $version = null, $media = 'all'){
        $info = array(
            'name'      => $name,
            'source'    => $source,
            'depends'    => array(),
            'version'    => filemtime(__FILE__),
            'media'    => $media,
        );

        if($registered = $this->css->getRegisteredResource($name)){
            $info = array_merge($info,$registered);
        }

        if(!empty($source)){
            $info['source'] = $source;
        }

        if(!empty($depends)){
            $info['depends'] = $depends;
        }

        if(!empty($version)){
            $info['version'] = $version;
        }

        if(!empty($media)){
            $info['media'] = $media;
        }
        
        if(!empty($info['source'])){
            $this->css->enqueue($info['name'],array(
                'id' => $info['name'] . '-css',
                'src' => $info['source'] . '?v=' . $info['version'],
                'depends' => $info['depends']
            ),$info['depends']);
        }
        

        return $info;
	}

    public function getCssResource(){
        return $this->css->sort();
    }

    protected function allElementsExists($must_exist,$all){
        return count(array_intersect($must_exist, $all)) == count($must_exist);
    }
    

    /**
    * Add pagelets
    * @param string $id is the div / span tag content will be placed inside
    * @param string $pagelet is the pagelet user will see. Here: object of the Paglets class
    */

    public function addPagelet( $id, $content, $css = array(), $js = array(),$config = array()) {
        if(!empty($id)){
            $pagelet = new Pagelet($id,$config);
            $pagelet->setContent($content);
            if(!$pagelet->isEnabled()){
                $pagelet->disable();
            }
            if(is_array($css)){
                foreach($css as $style){
                    $pagelet->addCss($style);
                }
            } elseif(is_string($css)){
                $pagelet->addCss($css);
            }
            if(is_array($js)){
                foreach($js as $script){
                    $pagelet->addJavascript($script);
                }
            } elseif(is_string($js)){
                $pagelet->addJavascript($js);
            }

            $this->pagelets[$pagelet->getName()] = $pagelet;
            $this->pagelet_count++;
        }

    }
    
    public function getPagelet($name){
        if(isset($this->pagelets[$name])){
            $pagelet =  $this->pagelets[$name];
            $content = '';
            if(!$pagelet->isEnabled()){
                $content = $this->getPageletContent($name);
            }
            if ($pagelet->isSpan()) {
                return '<span id="' . $pagelet->getName() . '">' . $content . '</span>';
            } else {			
                return '<div id="' . $pagelet->getName() . '">' . $content . '</div>';
            }
        }
    }
    
    public function getPageletContent($name){
        if(isset($this->pagelets[$name])){
            $pagelet =  $this->pagelets[$name];
            return $pagelet->getContent();
        }
    }
    
    /**
    * renders the content and make the data items ready for output
    */
    
    public function render() {
        if($this->enabled){
            // These two are used to count when we are rendering the last pagelet
            $i = 0;	
            if (!$this->pagelet_count) return;

            // Sort all pagelets according to their priority (highest priority => rendered first)	
            
            usort($this->pagelets,array($this,'cmp'));
            
            foreach ($this->pagelets as $id => $pagelet) {
                foreach($pagelet->getJsFiles() as $js){
                    $this->enqueueScript($js);
                }
                foreach($pagelet->getCssFiles() as $css){
                    $this->enqueueStyle($css);
                }
            }
            
            echo '<script id="bigpipe_init">var pipe = new BigPipe();pipe.jsResource('. json_encode($this->getJsResource()) .'); pipe.cssResource('. json_encode($this->getCssResource()) .');</script>';
            // General flush
            $this->fullFlush();

            foreach ($this->pagelets as $id => $pagelet) {
                $data = $pagelet->getData();
                $data['is_last'] = FALSE;
                if (++$i >= $this->pagelet_count){
                    $data['is_last'] = true;
                }
                // Output the pagelet on screen...:
                $this->showPageletScript($data, $pagelet);
                
              
                if($data['is_last'] == true){
                    sleep(2);
                    echo '<script id="bigpipe_done">pipe.done();</script>';
                    $this->fullFlush();
                }
            }
            $this->enabled = false;
        }
    }

    private function cmp($a, $b){
        if ($a->getPriority() == $b->getPriority()) {
            return 0;
        }
        return ($a->getPriority() < $b->getPriority()) ? 1 : -1;
    }
    private function fullFlush() {
        ob_flush();
        flush();
    }
    /**
    * Prints a single pagelet out and flushes it.
    * @param string $data contains the data that will be rendered on screen
    * @param string $pagelet is the div / span tag the content will be put inside
    */
    
    private function showPageletScript($data, $pagelet) {
        sleep(2);
        echo '<script id="pagelet-'.$data['id'].'">pipe.onPageletArrive(' . json_encode($data) . ');</script>';
        $this->fullFlush();
    }
} // EOF ?>