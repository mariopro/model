<?php namespace Esensi\Model\Traits;

use \Esensi\Model\Observers\ValidatingModelObserver;
use \Watson\Validating\ValidatingTrait;

/**
 * Trait that implements the Validating Model Interface
 *
 * @package Esensi\Model
 * @author Daniel LaBarge <dalabarge@emersonmedia.com>
 * @copyright 2014 Emerson Media LP
 * @license https://github.com/esensi/model/blob/master/LICENSE.txt MIT License
 * @link http://www.emersonmedia.com
 *
 * @see \Esensi\Model\Contracts\ValidatingModelInterface
 */
trait ValidatingModelTrait {

    /**
     * Use Watson's trait as a base.
     *
     * @see \Watson\Validating\ValidatingTrait
     */
    use ValidatingTrait;

    /**
     * We want to boot our own observer so we stub out this
     * boot method. This renders this function void.
     *
     * @return void
     */
    public static function bootValidatingTrait(){ }

    /**
     * Boot the trait's observers.
     *
     * @return void
     */
    public static function bootValidatingModelTrait()
    {
        static::observe(new ValidatingModelObserver);
    }

}
