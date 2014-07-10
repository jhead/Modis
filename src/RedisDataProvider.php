<?php
namespace jhead\Modis;

class RedisDataProvider {
    
    /**
     * Takes parts of a Redis hash, combines them according to formatHash(),
     * searches Redis for a matching hash with key-value pairs, then dumps
     * these pairs into an array.
     *
     * @param array $hash_parts
     * @return array|bool attributes
     */
    public static function modelAttributesByHash(array $hash_parts) {
        $hash = self::formatHash($hash_parts);
        
        if (strlen($hash) === 0) {
            return false;
        } else if ( !\Redis::exists($hash) ) {
            return false;
        }
        
        $attributes = [];
        
        $keys = \Redis::hkeys($hash);
        foreach ($keys as $key) {
            $attributes[$key] = \Redis::hget($hash, $key);
        }
        
        return $attributes;
    }
    
    public static function saveModel(RedisModel $model) {
        $vars = $model->fillable;
        $hash = $model->getHash();
        
        foreach ($vars as $var) {
            if ( isset($model->$var) ) {
                \Redis::hset($hash, $var, $model->$var);
            }
        }
    }
    
    /**
     * Takes parts of a hash and combines them as a hash string, used for
     * interacting with a Redis database. The hash format defaults to
     * 'model:id' where 'model' is the sanitized model name and 'id' is the
     * supplied alphanumeric ID of the model instance.
     *
     * @param array $parts
     * @param string $hash_format
     * @return string $hash
     */
    final public static function formatHash(array $parts, $hash_format = RedisModel::HASH_FORMAT) {
        $requires = explode(":", $hash_format);
        
        foreach ($requires as $r) {
            if ( !array_key_exists($r, $parts) ) {
                throw new RedisHashFormatException('Missing required parameters in hash format');
            }
        }
        
        $hash = '';
        
        foreach ($parts as $key => $value) {
            $hash .= $value . ':';
        }
        
        $hash = substr($hash, 0, strlen($hash) - 1);
        return $hash;
    }
    
}

