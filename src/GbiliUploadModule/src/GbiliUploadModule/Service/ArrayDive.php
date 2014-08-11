<?php
namespace GbiliUploadModule\Service;

class ArrayDive
{
    /**
     * Did the last call to get() succeed
     * @var bool
     */
    protected $hasPath;

    /**
     * Contents of last call to get()
     * @var mixed
     */
    protected $result;
    
    /**
     * Get a deep value from keys in array as if:
     * $keys = array('1', '2', '3')
     * @param array $target, the array from which we want a deep value
     */
    public function get(array $target, $keys)
    {
        if (is_string($keys) || is_int($keys)) {
            $keys = array($keys);
        }
        $this->has = true;
        foreach ($keys as $key) {
            if (!isset($target[$key])) {
                $this->hasPath = false;
                break;
            }
            $target = $target[$key];
        }
        $this->result = $target;
        return $target;
    }

    public function had()
    {
        return $this->hasPath;
    }

    public function has(array $target, array $keys))
    {
        $this->dive($target, $keys);
        return $this->hasPath;
    }

    public function got()
    {
        return $this->result;
    }
}
