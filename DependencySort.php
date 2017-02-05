<?php 
class DependencySort {
    
    public $cache;
    public $data = array();
    public $dependency = array();
    public $order = array();
    public $pre_processing = array();
    

    public function __construct(ResourceDataCache $cache) {
        $this->cache = $cache;
    }

    public function register($name,$data){
        $this->cache->registerResource($name,$data);
    }

    public function enqueue($name,$url,$dependencies = array()) {
        $this->data[$name] = $url;
        if(!empty($dependencies)){
            $this->dependency[$name] = $dependencies;
        }
    }

    /*    
    * sorts array based on dependencies
    * 
    * @return array or false on Circular reference
    */
    public function sort() {
        $this->order = array_diff_key($this->data, $this->dependency);
        $data = array_diff_key($this->data, $this->order);
        foreach($data as $i=>$v){
            if(!$this->processSort($i)) return false;
        }
        return $this->order;
    }

    protected function setData($pointer){
        
        if(isset($this->data[$pointer])){
            $this->order[$pointer] = $this->data[$pointer];
        } elseif($data = $this->getRegisteredResource($pointer)){
            $this->order[$pointer] = $data; 
        }
    }

    public function getRegisteredResource($name){
        return $this->cache->getRegisteredResource($name);
    }
    
    protected function processSort($pointer){
        if(isset($this->pre_processing[$pointer])) {
            return false;
        } else {
            $this->pre_processing[$pointer] = $pointer;
        }
        foreach($this->dependency[$pointer] as $i=>$v){
            if(isset($this->dependency[$v])){
                if(!$this->processSort($v)) {
                    return false;
                }
            }
            $this->setData($v);
            unset($this->pre_processing[$v]);
        }
        $this->setData($pointer);
        unset($this->pre_processing[$pointer]);
        return true;
    }
}
/*  
 * $sorter = new DependencySort(new ResourceDataCache('js',''));
 * $sorter->enqueue('a','a.js',array('c','d','e'));
 */