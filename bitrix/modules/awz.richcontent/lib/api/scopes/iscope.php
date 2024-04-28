<?php
namespace Awz\RichContent\Api\Scopes;

interface IScope
{
    public function enableScope();
    public function disableScope();
    public function checkRequire();
}