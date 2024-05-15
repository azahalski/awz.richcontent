<?php

namespace Awz\RichContent\Right;

class bMask {

    //const ALLOW_IMAGE = 4;

    /*
    const ALLOW_1 = 1;
    const ALLOW_2 = 2;
    const ALLOW_3 = 4;
    const ALLOW_4 = 8;
    const ALLOW_5 = 16;
    */

    const ALLOW_ALL = 31;

    protected int $user_perm = 0;

    public function __construct($mask = self::ALLOW_ALL)
    {
        $this->set($mask);
    }

    /**
     * Установка маски
     *
     * @param $mask
     * @return bMask
     */
    public function set($mask = self::ALLOW_ALL): bMask
    {
        $this->user_perm = $mask & self::ALLOW_ALL;
        return $this;
    }

    /**
     * Добавление права доступа
     *
     * @param $mask
     * @return bMask
     */
    public function add($mask): bMask
    {
        $mask = $mask & self::ALLOW_ALL;
        $this->user_perm = $this->user_perm | ($this->user_perm ^ $mask);
        return $this;
    }

    /**
     * Удаление права доступа
     *
     * @param $mask
     * @return bMask
     */
    public function delete($mask): bMask
    {
        $mask = $mask & self::ALLOW_ALL;
        $this->user_perm &= ~ ($this->user_perm & $mask);
        return $this;
    }

    /**
     * Текущая маска cookies
     *
     * @return int
     */
    public function get(): int
    {
        return $this->user_perm & self::ALLOW_ALL;
    }

    /**
     * Проверка прав
     *
     * @param $mask
     * @return bool
     */
    public function check($mask = self::ALLOW_ALL):bool
    {
        return ($this->get() & $mask);
    }

}