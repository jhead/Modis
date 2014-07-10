<?php
namespace jhead\Modis;

abstract class RedisModel {

    const HASH_FORMAT = "model:token";

    protected $token;
    protected $hash;
    protected $attributes = [];
    
    public $fillable = [ 'token' ];
    protected $hidden = [ 'hidden', 'attributes', 'fillable' ];

    protected function __construct(array $attributes = []) {
        $this->attributes = $attributes;
        $this->fill($this->attributes);

        $this->token = $this->getToken();
        $this->hash = $this->getHash();        
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
    
    public function getVisibleAttributes() {
        $vars = get_object_vars($this);
        
        foreach ($this->hidden as $attr) {
            if ( array_key_exists($attr, $vars) ) {
                unset($vars[$attr]);
            }
        }
        
        return $vars;
    }
    
    public function save() {
        RedisDataProvider::saveModel($this);
    }
    
    public function getToken() {
        if ( !isset($this->token) ) {
            $this->token = $this->generateToken();
        }
        
        return $this->token;
    }
    
    abstract protected function generateToken();
    
    public function getHash() {
        if ( !isset($this->hash) ) {
            $this->hash = RedisDataProvider::formatHash([
                'model' => self::sanitizeModelName( self::getModelName() ),
                'token' => $this->token
            ]);
        }
        
        return $this->hash;
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
        $vars = $this->getVisibleAttributes();
        
        $json_output = json_encode($vars);
        return $json_output;
    }
    
    /**
     * Searches for a model and token combination in the Redis database
     * and returns a new instance of the model with key-value pairs
     * from the database associated with it.
     *
     * @param int $token
     * @return jhead\Redis\RedisModel
     */
    public static function find($token) {
        $model_name = self::getModelName();
        
        $attributes = RedisDataProvider::modelAttributesByHash([
            'model' => self::sanitizeModelName($model_name),
            'token' => $token
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
