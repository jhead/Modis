<?php
namespace jhead\Redis;

class RedisDataProvider {
    
    /**
     * Takes parts of a Redis hash, combines them according to formatHash(),
     * searches Redis for a matching hash with key-value pairs, then dumps
     * these pairs into an array.
     *
     * @param array $hash_parts
     * @return array|bool attributes
     */
    public static function attributesByHash(array $hash_parts) {
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
    final public static function formatHash(array $parts, $hash_format = 'model:id') {
        $requires = explode(":", $hash_format);
        
        foreach ($requires as $r) {
            if ( !array_key_exists($r, $parts) ) {
                throw new RedisHashFormatException('Missing required attributes in hash format');
            }
        }
        
        $hash = '';
        
        $hash .= $parts['model'];
        $hash .= ':';
        $hash .= $parts['id'];
        
        return $hash;
    }
    
}

