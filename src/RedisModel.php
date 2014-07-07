<?php
namespace jhead\Redis;

abstract class RedisModel {

    protected $attributes = [];
    protected $hidden = [ 'hidden', 'attributes' ];

    private function __construct(array $attributes = []) {
        $this->attributes = $attributes;
        $this->fill( $this->attributes );
    }
    
    /**
     * Assign contents of $attributes to class as key-value pairs
     *
     * @param array $attributes
     * @return void
     */
    protected function fill(array $attributes = []) {
        foreach ($attributes as $attr => $value) {
            $this->$attr = $value;
        }
    }
    
    /**
     * See __toString()
     *
     * @return string
     */
    public function asJson() {
        return $this->__toString();
    }
    
    /**
     * Provides the current class in JSON format
     *
     * @return string
     */
    public function __toString() {
        $vars = get_object_vars($this);
        
        foreach ($this->hidden as $attr) {
            if ( array_key_exists($attr, $vars) ) {
                unset($vars[$attr]);
            }
        }
        
        $json_output = json_encode($vars);
        return $json_output;
    }
    
    /**
     * Searches for a model and ID combination in the Redis database
     * and returns a new instance of the model with key-value pairs
     * from the database associated with it.
     *
     * @param int $id
     * @return jhead\Redis\RedisModel
     */
    public static function find($id) {
        $model_name = self::getModelName();
        
        $attributes = RedisDataProvider::attributesByHash([
            'model' => self::sanitizeModelName($model_name),
            'id' => $id
        ]);
        
        return new $model_name($attributes);
    }
    
    /**
     * Provides the name of the current class.
     *
     * @return string
     */
    final public static function getModelName() {
        return get_called_class();
    }
    
    /**
     * Sanitizes a model name for use in Redis.
     */
    final public static function sanitizeModelName($model_name) {
        $sanitized = $model_name;
        
        $sanitized = strtolower($sanitized);
        // TODO
        
        return $sanitized;
    }

}
