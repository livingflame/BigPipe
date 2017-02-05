<?php 
class ResourceDataCache {
	
    public $identifier;
    public $cache_path;

    public function __construct($identifier,$cache_path = "") {
        $this->identifier = $identifier;
        if($cache_path){
            $this->cache_path = $cache_path;
        } else {
            $this->cache_path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'registered_resource' . DIRECTORY_SEPARATOR ;
        }
    }
	
    /*  get registered resource  */
    public function getRegisteredResource($name){
        $file = $this->cache_path . $name . ((!empty($this->identifier)) ? '-' . $this->identifier : '') . '.json';
        if(file_exists($file)){
            return json_decode(file_get_contents($file),TRUE);
        }
        return FALSE;
    }

    /*  register a resource  */
    private function registerResource($name,$data){
        $file = $this->cache_path . $name . '-' . $this->identifier . '.json';
        file_put_contents($file, json_encode($data,FALSE));
        return $data;
    }
}